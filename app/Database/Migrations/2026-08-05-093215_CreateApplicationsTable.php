<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApplicationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'client_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'redirect_uri' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('client_id');
        $this->forge->createTable('applications');
    }

    public function down()
    {
        $this->forge->dropTable('applications');
    }
}