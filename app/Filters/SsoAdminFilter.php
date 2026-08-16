<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SsoAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('admin_user_id')) {
            return redirect()->to('/authorize-admin')->with('error', 'Silakan login sebagai admin terlebih dahulu.');
        }

        $userId = $session->get('admin_user_id');
        $db = \Config\Database::connect();

        $admin = $db->table('sso_admins')->where('user_id', $userId)->get()->getRow();

        if (!$admin) {
            return redirect()->to('/authorize-admin')->with('error', 'Anda tidak memiliki akses ke halaman admin ini.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
