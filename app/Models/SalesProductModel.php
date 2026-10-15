<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesProductModel extends Model
{
    protected $table            = 'tb_sales_produk';
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

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** Ambil produk sales berdasarkan slug */
    public function getProductBySlug(string $slug)
    {
        return $this->where('slug_id', $slug)
            ->orWhere('slug_en', $slug)
            ->first();
    }
}
