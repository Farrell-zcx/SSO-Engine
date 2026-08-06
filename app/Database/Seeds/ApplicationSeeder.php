<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Ramsey\Uuid\Uuid;

class ApplicationSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'           => Uuid::uuid4()->toString(),
                'name'         => 'MyMember',
                'client_id'    => 'mymember-client',
                'redirect_uri' => 'http://mymember.test/auth/callback',
            ],
            [
                'id'           => Uuid::uuid4()->toString(),
                'name'         => 'Inventory',
                'client_id'    => 'inventory-client',
                'redirect_uri' => 'http://inventory.test/auth/callback',
            ],
        ];

        $this->db->table('applications')->ignore(true)->insertBatch($data);
    }
}