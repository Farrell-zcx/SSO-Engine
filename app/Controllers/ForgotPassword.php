<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Email\Email;

class ForgotPassword extends BaseController
{
    public function index()
    {
        $context = session()->get('oauth_context');
        $loginUrl = $context ? site_url('authorize?' . http_build_query($context)) : site_url('authorize');

        return view('auth/forgot_password', ['loginUrl' => $loginUrl]);
    }

    public function processEmail()
    {
        $rules = [
            'email' => 'required|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->with('success', 'Jika email terdaftar, link reset telah dikirim.');
        }

        // Generate Token
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);

        // Save to password_resets table
        $db = \Config\Database::connect();
        
        // Hapus token lama jika ada
        $db->table('password_resets')->where('email', $email)->delete();

        $db->table('password_resets')->insert([
            'email'      => $email,
            'token'      => $hashedToken,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $resetLink = site_url("reset-password?token={$token}&email=" . urlencode($email));
        
        $emailMessage = view('email/reset_password', [
            'username'  => $user['username'],
            'resetLink' => $resetLink
        ]);

        $emailService = \Config\Services::email();
        $emailService->setFrom('noreply@sso-engine.test', 'SSO Engine');
        $emailService->setTo($email);
        $emailService->setSubject('Reset Password SSO Engine');
        $emailService->setMessage($emailMessage);

        if ($emailService->send()) {
            return redirect()->back()->with('success', 'Link reset password telah dikirim ke email Anda.');
        } else {
            log_message('error', $emailService->printDebugger(['headers']));
            return redirect()->back()->with('error', 'Gagal mengirim email. Hubungi Administrator IT.');
        }
    }

    public function reset()
    {
        $token = (string) $this->request->getGet('token');
        $email = (string) $this->request->getGet('email');

        if (empty($token) || empty($email)) {
            return view('auth/reset_error', ['message' => 'Link reset password tidak valid.']);
        }

        $db = \Config\Database::connect();
        $resetRecord = $db->table('password_resets')->where('email', $email)->get()->getRowArray();

        if (!$resetRecord) {
            return view('auth/reset_error', ['message' => 'Token reset password tidak ditemukan atau sudah kadaluarsa.']);
        }

        // Validasi token hash
        if (!hash_equals($resetRecord['token'], hash('sha256', $token))) {
            return view('auth/reset_error', ['message' => 'Token reset password salah.']);
        }

        // Cek kadaluarsa (10 menit)
        if (strtotime($resetRecord['created_at']) < strtotime('-10 minutes')) {
            $db->table('password_resets')->where('email', $email)->delete();
            return view('auth/reset_error', ['message' => 'Token reset password sudah kadaluarsa.']);
        }

        return view('auth/reset_password', ['email' => $email, 'token' => $token]);
    }

    public function processReset()
    {
        $rules = [
            'email'            => 'required|valid_email',
            'token'            => 'required',
            'password'         => 'required|min_length[6]',
            'password_confirm' => 'matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = (string) $this->request->getPost('email');
        $token = (string) $this->request->getPost('token');
        $password = (string) $this->request->getPost('password');

        $db = \Config\Database::connect();
        $resetRecord = $db->table('password_resets')->where('email', $email)->get()->getRowArray();

        if (!$resetRecord || !hash_equals($resetRecord['token'], hash('sha256', $token))) {
            return view('auth/reset_error', ['message' => 'Proses reset gagal. Link tidak valid.']);
        }

        if (strtotime($resetRecord['created_at']) < strtotime('-10 minutes')) {
            $db->table('password_resets')->where('email', $email)->delete();
            return view('auth/reset_error', ['message' => 'Token reset password sudah kadaluarsa.']);
        }

        // Update password
        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();
        
        if ($user) {
            $userModel->update($user['id'], [
                'password_hash' => password_hash($password, PASSWORD_BCRYPT)
            ]);
            
            // Revoke all refresh tokens
            $refreshTokenModel = new \App\Models\RefreshTokenModel();
            $activeTokens = $refreshTokenModel
                ->where('user_id', $user['id'])
                ->where('revoked', 0)
                ->findAll();

            $jtis = array_column($activeTokens, 'jti');
            $activeIds = array_column($activeTokens, 'id');
            
            if (!empty($activeIds)) {
                $refreshTokenModel
                    ->whereIn('id', $activeIds)
                    ->set(['revoked' => 1])
                    ->update();
            }

            if (!empty($jtis)) {
                $blacklist = new \App\Libraries\RedisBlacklist();
                $blacklist->addMany($jtis);
            }
        }

        // Hapus token
        $db->table('password_resets')->where('email', $email)->delete();

        return view('auth/reset_success');
    }
}
