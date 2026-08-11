<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeUsernameUnique extends Migration
{
    public function up()
    {
        $this->forge->addUniqueKey('username');
        $this->forge->processIndexes('users');
    }

    public function down()
    {
        $this->forge->dropKey('users', 'username');
    }
}
