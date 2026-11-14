<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierProdukModel extends Model
{
    protected $table            = 'tb_supplier_produk';
    protected $primaryKey       = 'id_supplier_produk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'timestamp_form',
        'email_pengirim',
        'nama_pic',
        'nama_perusahaan',
        'no_hp',
        'email_perusahaan',
        'website',
        'instagram',
        'lokasi',
        'jenis_usaha',
        'nama_produk',
        'spesifikasi_kapasitas',
        'legalitas',
        'sertifikasi',
        'foto_produk',
        'harga_moq',
        'komisi',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public static function extractPhotoUrl(?string $val): ?string
    {
        if (empty($val)) {
            return null;
        }
        $val = trim($val);
        // Google Drive check
        if (preg_match('/(?:drive\.google\.com|docs\.google\.com)/i', $val)) {
            if (preg_match('#/(?:file/)?d/([A-Za-z0-9_-]{10,})#i', $val, $matches)) {
                return 'https://drive.google.com/thumbnail?id=' . $matches[1] . '&sz=w800';
            }
            if (preg_match('/[?&]id=([A-Za-z0-9_-]{10,})/i', $val, $matches)) {
                return 'https://drive.google.com/thumbnail?id=' . $matches[1] . '&sz=w800';
            }
        }
        if (filter_var($val, FILTER_VALIDATE_URL)) {
            return $val;
        }
        return null;
    }
}
