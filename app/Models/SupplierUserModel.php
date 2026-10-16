<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierUserModel extends Model
{
    protected $table            = 'tb_supplier_user';
    protected $primaryKey       = 'id_supplier_user';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_supplier',
        'username',
        'password',
        'name',
        'is_active',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
