<?php

namespace App\Models;

use CodeIgniter\Model;

class PdfHistoryModel extends Model
{
    protected $table            = 'pdf_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'pdf_type',
        'product_id',
        'product_name',
        'view_mode',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Catat unduh / cetak PDF
     */
    public function recordPdf(int $userId, string $pdfType, ?int $productId = null, ?string $productName = null, string $viewMode = 'internal'): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $viewMode = (strtolower(trim($viewMode)) === 'eksternal') ? 'eksternal' : 'internal';

        return (bool) $this->insert([
            'user_id'      => $userId,
            'pdf_type'     => substr(trim($pdfType), 0, 100),
            'product_id'   => $productId !== null && $productId > 0 ? $productId : null,
            'product_name' => $productName !== null ? substr(trim($productName), 0, 255) : null,
            'view_mode'    => $viewMode,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}

