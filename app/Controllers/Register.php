<?php

namespace App\Controllers;

use App\Models\UserModel;
use Ramsey\Uuid\Uuid;

class Register extends BaseController
{
    public function index()
    {
        $context = session()->get('oauth_context');
        $loginUrl = $context ? site_url('authorize?' . http_build_query($context)) : site_url('authorize');
        
        return view('auth/register', ['loginUrl' => $loginUrl]);
    }

    public function process()
    {
        $rules = [
            'username' => [
                'rules'  => 'required|min_length[3]|is_unique[users.username]',
                'errors' => [
                    'is_unique' => 'Username ini sudah terdaftar. Jika Anda lupa password, gunakan fitur Lupa Password.',
                ],
            ],
            'email' => [
                'rules'  => 'required|valid_email|is_unique[users.email]',
                'errors' => [
                    'is_unique' => 'Email ini sudah terdaftar. Jika Anda lupa password, gunakan fitur Lupa Password.',
                ],
            ],
            'password' => 'required|min_length[6]',
            'password_confirm' => 'matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();

        $userModel->insert([
            'id'            => Uuid::uuid4()->toString(),
            'username'      => $this->request->getPost('username'),
            'email'         => $this->request->getPost('email'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/authorize')->with('success', 'Pendaftaran berhasil. Silakan login.');
    }
}
