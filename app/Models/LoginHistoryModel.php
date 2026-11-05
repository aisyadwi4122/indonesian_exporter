<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginHistoryModel extends Model
{
    protected $table            = 'login_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Catat riwayat login
     */
    public function recordLogin(int $userId, string $ipAddress, ?string $userAgent = null): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return (bool) $this->insert([
            'user_id'    => $userId,
            'ip_address' => substr($ipAddress, 0, 45),
            'user_agent' => $userAgent !== null ? substr($userAgent, 0, 255) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
