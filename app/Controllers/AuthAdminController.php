<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthAdminController extends BaseController
{
    public function index()
    {
        if (session()->get('sso_user_id')) {
            $db = \Config\Database::connect();
            $admin = $db->table('sso_admins')->where('user_id', session()->get('sso_user_id'))->get()->getRow();
            
            if ($admin) {
                return redirect()->to('/admin/users');
            } else {
                // Sesi saat ini bukan admin, hapus sesi agar tidak terjadi infinite loop
                session()->remove('sso_user_id');
                return redirect()->to('/authorize-admin')->with('error', 'Sesi Anda saat ini bukan admin. Silakan login dengan akun admin.');
            }
        }
        return view('auth/admin_login');
    }

    public function login()
    {
        $loginId  = $this->request->getPost('login_id');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->groupStart()
                          ->where('email', $loginId)
                          ->orWhere('username', $loginId)
                          ->groupEnd()
                          ->first();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return redirect()->back()
                ->with('error', 'Email/Username atau password salah.')
                ->withInput();
        }

        $db = \Config\Database::connect();
        $admin = $db->table('sso_admins')->where('user_id', $user['id'])->get()->getRow();

        if (!$admin) {
            return redirect()->back()
                ->with('error', 'Akun Anda tidak memiliki hak akses Admin SSO.')
                ->withInput();
        }

        session()->set('sso_user_id', $user['id']);
        return redirect()->to('/admin/users');
    }

    public function logout()
    {
        session()->remove('sso_user_id');
        return redirect()->to('/authorize-admin');
    }
}
