<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;

class CreateUser extends BaseCommand
{
    protected $group       = 'SSO';
    protected $name        = 'sso:create-user';
    protected $description = 'Provisioning akun SSO baru untuk karyawan';

    public function run(array $params)
    {
        CLI::write('=== SSO Engine User Provisioning ===', 'yellow');

        $email = CLI::prompt('Email karyawan (wajib @desnet.co.id)', null, 'required|valid_email');
        if (!str_ends_with($email, '@desnet.co.id')) {
            CLI::error('Domain email harus @desnet.co.id');
            return;
        }

        $userModel = new UserModel();
        if ($userModel->where('email', $email)->first()) {
            CLI::error("Email {$email} sudah terdaftar di sistem!");
            return;
        }

        $username = CLI::prompt('Username', null, 'required');

        // Generate temporary random password (will not be given to user)
        $tempPassword = bin2hex(random_bytes(16));

        // Insert User
        try {
            $userModel->insert([
                'id'            => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'email'         => $email,
                'username'      => $username,
                'password_hash' => password_hash($tempPassword, PASSWORD_BCRYPT),
            ]);
            CLI::write("-> Akun berhasil dibuat di tabel users", 'green');
        } catch (\Exception $e) {
            CLI::error("Gagal membuat user: " . $e->getMessage());
            return;
        }

        // Generate activation link (reset password flow)
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);

        $db = \Config\Database::connect();
        $db->table('password_resets')->where('email', $email)->delete();
        $db->table('password_resets')->insert([
            'email'      => $email,
            'token'      => $hashedToken,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $resetLink = site_url("reset-password?token={$token}&email=" . urlencode($email));

        // Send Email
        $emailMessage = view('email/reset_password', [
            'username'  => $username,
            'resetLink' => $resetLink
        ]);

        $emailService = \Config\Services::email();
        $emailService->setFrom('noreply@sso-engine.test', 'SSO Engine');
        $emailService->setTo($email);
        $emailService->setSubject('Aktivasi Akun SSO Engine Anda');
        $emailService->setMessage($emailMessage);

        CLI::write("-> Mengirim email aktivasi ke {$email}...", 'yellow');
        if ($emailService->send()) {
            CLI::write("[OK] Email aktivasi berhasil terkirim.", 'green');
            CLI::write("[OK] Akun SSO berhasil di-provision untuk {$email}.", 'green');
        } else {
            CLI::error("Gagal mengirim email aktivasi. Silakan ulangi via web Lupa Password.");
            CLI::write($emailService->printDebugger(['headers']), 'red');
        }
    }
}
