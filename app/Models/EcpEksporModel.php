<?php

namespace App\Models;

use CodeIgniter\Model;

class EcpEksporModel extends Model
{
    protected $table            = 'tb_ecp_ekspor';
    protected $primaryKey       = 'id_ecp';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tahun',
        'nama_peserta',
        'nama_perusahaan',
        'email',
        'no_hp',
        'domisili',
        'wilayah_ecp',
        'kategori_produk',
        'produk_dipasarkan',
        'merk_dagang',
        'jenis_perusahaan',
        'kapasitas_produksi',
        'omset',
        'website',
        'instagram',
        'produk_ekspor',
        'negara_tujuan',
        'jenis_ekspor',
        'kuantitas_ekspor',
        'nilai_ekspor_usd',
        'nilai_ekspor_raw',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
