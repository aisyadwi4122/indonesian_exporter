<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierProductModel extends Model
{
    protected $table            = 'tb_produk_supplier';
    protected $primaryKey       = 'id_produk_supplier';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_supplier',
        'nama_produk',
        'spesifikasi',
        'kapasitas_produksi',
        'legalitas',
        'sertifikasi',
        'lokasi_barang',
        'foto_1',
        'foto_2',
        'foto_3',
        'foto_4',
        'foto_5',
        'moq',
        'harga_exw',
        'komisi',
        'sudah_ekspor',
        'views',
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
     * static model chain, return $this agar controller bisa memanggil ->paginate()
     */
    public function getListWithSupplier(array $filter = [])
    {
        $this->select('tb_produk_supplier.id_produk_supplier, tb_produk_supplier.nama_produk, tb_produk_supplier.foto_1, tb_produk_supplier.views, tb_produk_supplier.lokasi_barang, tb_supplier.id_supplier, tb_supplier.nama_perusahaan, tb_supplier.jenis_perusahaan')
            ->join('tb_supplier', 'tb_supplier.id_supplier = tb_produk_supplier.id_supplier', 'inner');

        if (! empty($filter['keyword'])) {
            $keyword = trim((string) $filter['keyword']);
            $this->groupStart()
                ->like('tb_produk_supplier.nama_produk', $keyword)
                ->orLike('tb_supplier.nama_perusahaan', $keyword)
                ->orLike('tb_produk_supplier.lokasi_barang', $keyword)
                ->orLike('tb_supplier.produk_dipasarkan', $keyword)
            ->groupEnd();
        }

        $sort = $filter['sort'] ?? 'az';
        switch ($sort) {
            case 'za':
                $this->orderBy('tb_produk_supplier.nama_produk', 'DESC');
                break;
            case 'views':
                $this->orderBy('tb_produk_supplier.views', 'DESC')
                     ->orderBy('tb_produk_supplier.nama_produk', 'ASC');
                break;
            case 'terbaru':
                $this->orderBy('tb_produk_supplier.id_produk_supplier', 'DESC');
                break;
            case 'az':
            default:
                $this->orderBy('tb_produk_supplier.nama_produk', 'ASC');
                break;
        }

        return $this;
    }

    /**
     * Ambil produk (find) lalu supplier-nya (SupplierModel find by id_supplier)
     * Kembalikan null jika produk tidak ada.
     */
    public function getDetail(int $id): ?array
    {
        $produk = $this->find($id);

        if (! $produk) {
            return null;
        }

        $supplierModel = new SupplierModel();
        $supplier      = ! empty($produk['id_supplier']) ? $supplierModel->find($produk['id_supplier']) : null;

        return [
            'produk'   => $produk,
            'supplier' => $supplier,
        ];
    }

    /**
     * Jalankan UPDATE views = views + 1 tanpa mengubah updated_at
     */
    public function incrementViews(int $id): bool
    {
        return (bool) $this->builder()
            ->set('views', 'views + 1', false)
            ->where($this->primaryKey, $id)
            ->update();
    }
}
