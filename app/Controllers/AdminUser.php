<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserApplicationAccessModel;
use App\Models\ApplicationModel;
use Ramsey\Uuid\Uuid;

class AdminUser extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $users = $userModel->orderBy('created_at', 'DESC')->findAll();
        
        return view('admin/users/index', ['users' => $users]);
    }

    public function store()
    {
        $email = (string) $this->request->getPost('email');
        $username = (string) $this->request->getPost('username');
        
        // Validasi dihapus agar bebas menggunakan email domain apapun
        
        $userModel = new UserModel();
        if ($userModel->where('email', $email)->first()) {
            return redirect()->back()->with('error', 'Email sudah terdaftar')->withInput();
        }
        if ($userModel->where('username', $username)->first()) {
            return redirect()->back()->with('error', 'Username sudah terdaftar')->withInput();
        }

        // Generate password reset token via ForgotPassword logic
        $userId = Uuid::uuid4()->toString();
        $userModel->insert([
            'id' => $userId,
            'email' => $email,
            'username' => $username,
            'password_hash' => '', // belum diset
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Generate reset token untuk aktivasi
        $resetTokenPlain = bin2hex(random_bytes(32));
        $resetTokenHash  = hash('sha256', $resetTokenPlain);

        $db = \Config\Database::connect();
        $db->table('password_resets')->insert([
            'email'      => $email,
            'token'      => $resetTokenHash,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Send activation email
        $resetLink = site_url("reset-password?token={$resetTokenPlain}&email=" . urlencode($email));
        
        $emailMessage = view('email/activation', [
            'username'       => $username,
            'activationLink' => $resetLink
        ]);

        $emailService = \Config\Services::email();
        $emailService->setFrom('noreply@sso-engine.test', 'SSO Engine');
        $emailService->setTo($email);
        $emailService->setSubject('Aktivasi Akun SSO Engine');
        $emailService->setMessage($emailMessage);

        if ($emailService->send()) {
            return redirect()->to('/admin/users')->with('message', 'User berhasil ditambahkan dan email aktivasi telah dikirim ke ' . esc($email));
        } else {
            log_message('error', $emailService->printDebugger(['headers']));
            return redirect()->to('/admin/users')->with('error', 'User ditambahkan, tetapi gagal mengirim email aktivasi ke ' . esc($email) . '. Pastikan konfigurasi SMTP benar.');
        }
    }

    public function access(string $userId)
    {
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan');
        }

        $appModel = new ApplicationModel();
        $apps = $appModel->findAll();

        $accessModel = new UserApplicationAccessModel();
        // Ambil list ID aplikasi yang diizinkan (yang belum direvoke)
        $accesses = $accessModel->where('user_id', $userId)
                                ->where('revoked_at', null)
                                ->findAll();
        
        $grantedAppIds = array_column($accesses, 'application_id');

        return view('admin/users/access', [
            'user' => $user,
            'apps' => $apps,
            'grantedAppIds' => $grantedAppIds
        ]);
    }

    public function grantAccess(string $userId)
    {
        $appId = (string) $this->request->getPost('application_id');
        $adminId = session()->get('admin_user_id'); // get admin ID who is currently logged in

        $accessModel = new UserApplicationAccessModel();
        
        // Cek apakah sudah pernah ada (mungkin di-revoke sebelumnya)
        $existing = $accessModel->where('user_id', $userId)
                                ->where('application_id', $appId)
                                ->first();

        if ($existing) {
            // Restore akses
            $accessModel->update($existing['id'], [
                'granted_by' => $adminId,
                'granted_at' => date('Y-m-d H:i:s'),
                'revoked_at' => null
            ]);
        } else {
            // Insert baru
            $accessModel->insert([
                'user_id' => $userId,
                'application_id' => $appId,
                'granted_by' => $adminId,
                'granted_at' => date('Y-m-d H:i:s'),
                'revoked_at' => null
            ]);
        }

        return redirect()->to("/admin/users/{$userId}/access")->with('message', 'Akses aplikasi berhasil diberikan.');
    }

    public function delete(string $userId)
    {
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan');
        }

        $userModel->delete($userId);

        // Hapus otomatis di database MyMember
        try {
            $dbMymember = \Config\Database::connect([
                'hostname' => '127.0.0.1',
                'username' => 'root',
                'password' => '',
                'database' => 'mymember_db',
                'DBDriver' => 'MySQLi'
            ], false); // set false to not use default connection config
            $dbMymember->table('admin')->where('sso_user_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'Gagal menghapus user di MyMember: ' . $e->getMessage());
        }

        // Hapus otomatis di database Inventory
        try {
            $dbInventory = \Config\Database::connect([
                'hostname' => '127.0.0.1',
                'username' => 'root',
                'password' => '',
                'database' => 'inventory_sso',
                'DBDriver' => 'MySQLi'
            ], false);
            $dbInventory->table('users')->where('sso_user_id', $userId)->delete();
        } catch (\Exception $e) {
            log_message('error', 'Gagal menghapus user di Inventory: ' . $e->getMessage());
        }

        return redirect()->to('/admin/users')->with('message', 'User berhasil dihapus dari SSO Engine, MyMember, dan Inventory.');
    }

    public function revokeAccess(string $userId, string $appId)
    {
        $accessModel = new UserApplicationAccessModel();
        $existing = $accessModel->where('user_id', $userId)
                                ->where('application_id', $appId)
                                ->where('revoked_at', null)
                                ->first();

        if ($existing) {
            $accessModel->update($existing['id'], [
                'revoked_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Revoke seluruh refresh token aktif untuk user & aplikasi ini
        $refreshTokenModel = new \App\Models\RefreshTokenModel();
        $activeTokens = $refreshTokenModel
            ->where('user_id', $userId)
            ->where('application_id', $appId)
            ->where('revoked', 0)
            ->findAll();

        if (!empty($activeTokens)) {
            $jtis = array_column($activeTokens, 'jti');
            $activeIds = array_column($activeTokens, 'id');

            $refreshTokenModel
                ->whereIn('id', $activeIds)
                ->set(['revoked' => 1])
                ->update();

            // Masukkan JTI ke Redis Blacklist agar sesi langsung invalid seketika
            $blacklist = new \App\Libraries\RedisBlacklist();
            $blacklist->addMany($jtis);
        }

        return redirect()->to("/admin/users/{$userId}/access")->with('message', 'Akses aplikasi berhasil dicabut.');
    }
}
