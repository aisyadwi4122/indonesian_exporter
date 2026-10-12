<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryProductModel extends Model
{
    protected $table = 'tb_kategori_produk';
    protected $primaryKey = 'id_kategori_produk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id_kategori_produk',      // ✔ DITAMBAHKAN (PENTING!)
        'nama_kategori_id',
        'nama_kategori_en',
        'slug_kategori_id',
        'slug_kategori_en',
        'title_kategori_id',
        'title_kategori_en',
        'meta_desc_id',
        'meta_desc_en',
        'created_at',
        'updated_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getCategoryBySlug($slugCategory, $lang = 'id')
    {
        return $this->where('slug_kategori_id', $slugCategory)
            ->orWhere('slug_kategori_en', $slugCategory)
            ->first();
    }

    public function getAllCategories(string $lang = 'id')
    {
        $fieldName = $lang === 'id' ? 'nama_kategori_id' : 'nama_kategori_en';
        $fieldSlug = $lang === 'id' ? 'slug_kategori_id' : 'slug_kategori_en';

        return $this->select("id_kategori_produk, $fieldName AS nama_kategori, $fieldSlug AS slug_kategori")
            ->orderBy('id_kategori_produk', 'ASC')
            ->findAll();
    }

    public function getKategoriTerbanyak()
    {
        return $this->select('tb_kategori_produk.nama_kategori_id, tb_kategori_produk.slug_kategori_id')
            ->selectCount('tb_produk.id_produk', 'total_produk')
            ->join('tb_produk', 'tb_produk.id_kategori_produk = tb_kategori_produk.id_kategori_produk', 'left')
            ->groupBy('tb_kategori_produk.id_kategori_produk')
            ->orderBy('total_produk', 'DESC')
            ->limit(5)
            ->findAll() ?: [];
    }
}
