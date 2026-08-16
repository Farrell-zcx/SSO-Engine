<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Ramsey\Uuid\Uuid;

class SsoAdminSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        $email = 'labibfarrel960@gmail.com';
        
        // Cek apakah user sudah ada
        $user = $db->table('users')->where('email', $email)->get()->getRow();
        
        if (!$user) {
            $userId = Uuid::uuid4()->toString();
            $db->table('users')->insert([
                'id'            => $userId,
                'email'         => $email,
                'username'      => 'farrel labib',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        } else {
            $userId = $user->id;
        }
        
        // Cek apakah sudah jadi admin
        $admin = $db->table('sso_admins')->where('user_id', $userId)->get()->getRow();
        
        if (!$admin) {
            $db->table('sso_admins')->insert([
                'user_id'    => $userId,
                'granted_by' => null, 
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
