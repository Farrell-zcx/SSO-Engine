<?php

namespace App\Models;

use CodeIgniter\Model;

class UserApplicationAccessModel extends Model
{
    protected $table            = 'user_application_access';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 
        'application_id', 
        'granted_by', 
        'granted_at', 
        'revoked_at'
    ];

    // Dates
    protected $useTimestamps = false;
}
