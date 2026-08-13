<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserApplicationAccessTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '36',
            ],
            'application_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '36',
            ],
            'granted_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'granted_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'revoked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'application_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        // Because sso_admins use AUTO_INCREMENT id, we might want to reference sso_admins(id), but granted_by could also be users(id). 
        // In the PRD: granted_by INT NOT NULL -- FK ke sso_admins. We'll add the FK after creating sso_admins or keep it without constraint here.
        $this->forge->createTable('user_application_access');
    }

    public function down()
    {
        $this->forge->dropTable('user_application_access');
    }
}
