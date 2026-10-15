<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'tb_produk';
    protected $primaryKey       = 'id_produk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_kategori_produk',
        'nama_produk_id',
        'nama_produk_en',
        'deskripsi_produk_id',
        'deskripsi_produk_en',
        'foto_produk',
        'alt_produk_id',
        'alt_produk_en',
        'title_id',
        'title_en',
        'meta_desc_id',
        'meta_desc_en',
        'slug_id',
        'slug_en'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Ambil produk berdasarkan slug bahasa Indonesia atau Inggris
     */
    public function getProductBySlug(string $slug)
    {
        return $this->where('slug_id', $slug)
            ->orWhere('slug_en', $slug)
            ->first();
    }

    /**
     * Ambil produk secara acak
     */
    public function getRandomProducts(int $limit = 10, ?int $excludeId = null)
    {
        $builder = $this->orderBy('RAND()');
        if ($excludeId !== null) {
            $builder->where('id_produk !=', $excludeId);
        }
        return $builder->limit($limit)->find();
    }

    /**
     * Ambil semua produk untuk modul sales / public dengan fallback alias
     */
    public function getPublishedProducts()
    {
        $products = $this->orderBy('id_produk', 'DESC')->findAll();
        foreach ($products as &$p) {
            if (!isset($p['nama_produk'])) {
                $p['nama_produk'] = $p['nama_produk_id'] ?? ($p['nama_produk_en'] ?? '');
            }
            if (!isset($p['id'])) {
                $p['id'] = $p['id_produk'] ?? 0;
            }
            if (!isset($p['deskripsi'])) {
                $p['deskripsi'] = $p['deskripsi_produk_id'] ?? ($p['deskripsi_produk_en'] ?? '');
            }
            if (!isset($p['gambar_utama'])) {
                $p['gambar_utama'] = $p['foto_produk'] ?? '';
            }
        }
        return $products;
    }

    /**
     * Ambil 1 detail produk dengan fallback alias
     */
    public function getDetail($id)
    {
        $product = $this->find($id);
        if (!$product) {
            return null;
        }
        if (!isset($product['nama_produk'])) {
            $product['nama_produk'] = $product['nama_produk_id'] ?? ($product['nama_produk_en'] ?? '');
        }
        if (!isset($product['id'])) {
            $product['id'] = $product['id_produk'] ?? 0;
        }
        if (!isset($product['deskripsi'])) {
            $product['deskripsi'] = $product['deskripsi_produk_id'] ?? ($product['deskripsi_produk_en'] ?? '');
        }
        if (!isset($product['gambar_utama'])) {
            $product['gambar_utama'] = $product['foto_produk'] ?? '';
        }
        return $product;
    }
}
