<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRefreshTokensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'user_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'application_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'jti' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'token_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'revoked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('jti');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('refresh_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('refresh_tokens');
    }
}