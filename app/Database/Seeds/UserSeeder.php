<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Ramsey\Uuid\Uuid;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'id'            => Uuid::uuid4()->toString(),
            'email'         => 'labibfarrel960@gmail.com',
            'username'      => 'farrel labib',
            'password_hash' => password_hash('password123', PASSWORD_BCRYPT),
            'created_at'    => date('Y-m-d H:i:s'),
        ];

        $this->db->table('users')->ignore(true)->insert($data);
    }
}