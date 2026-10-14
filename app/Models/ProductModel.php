<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_produk',
        'kategori',
        'deskripsi',
        'gambar_utama',
        'domisili_usaha',
        'jenis_perusahaan',
        'jumlah_pegawai',
        'kapasitas_produksi',
        'produk_dipasarkan',
        'produk_siap_ekspor',
        'frekuensi_ekspor',
        'status',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Ambil semua produk yang berstatus published
     */
    public function getPublishedProducts()
    {
        return $this->where('status', 'published')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Ambil 1 detail produk jika berstatus published, return null jika tidak ada / bukan published
     */
    public function getDetail($id)
    {
        $product = $this->find($id);

        if (!$product || ($product['status'] ?? '') !== 'published') {
            return null;
        }

        return $product;
    }
}
