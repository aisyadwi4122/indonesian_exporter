<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table            = 'tb_supplier';
    protected $primaryKey       = 'id_supplier';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama_perusahaan',
        'nama_kunci',
        'nama_kontak',
        'email',
        'no_hp',
        'website',
        'instagram',
        'domisili_usaha',
        'lokasi_pabrik_gudang',
        'produk_dipasarkan',
        'jenis_perusahaan',
        'kapasitas_produksi',
        'jumlah_pegawai',
        'pameran_internasional',
        'keunggulan_produk',
        'link_foto_usaha',
        'legalitas',
        'link_dokumen_legalitas',
        'sertifikasi',
        'link_dokumen_sertifikat',
        'sumber_tahun',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts        = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
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
     * Membuat nama_kunci ternormalisasi untuk deteksi duplikat:
     * huruf kecil, hapus kata berdiri sendiri "pt", "cv", "ud",
     * lalu buang semua karakter selain a-z dan 0-9.
     */
    public static function makeKey(string $name): string
    {
        $name = mb_strtolower($name, 'UTF-8');
        $name = preg_replace('/\b(pt|cv|ud|p\.t\.|c\.v\.|u\.d\.|p\.t|c\.v|u\.d)\b/u', '', $name);
        return preg_replace('/[^a-z0-9]/', '', $name);
    }
}
