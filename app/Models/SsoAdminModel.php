<?php

namespace App\Models;

use CodeIgniter\Model;

class SsoAdminModel extends Model
{
    protected $table            = 'sso_admins';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'granted_by', 'created_at'];

    // Dates
    protected $useTimestamps = false;
}
