<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionClickHistoryModel extends Model
{
    protected $table            = 'commission_click_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'product_id',
        'product_name',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Catat klik info komisi produk
     */
    public function recordClick(int $userId, int $productId, string $productName): bool
    {
        if ($userId <= 0 || $productId <= 0) {
            return false;
        }

        return (bool) $this->insert([
            'user_id'      => $userId,
            'product_id'   => $productId,
            'product_name' => substr(trim($productName), 0, 255),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}
