<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierExportHistoryModel extends Model
{
    protected $table            = 'tb_history_export_supplier';
    protected $primaryKey       = 'id_history';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_supplier',
        'tahun',
        'produk',
        'negara_tujuan',
        'jenis_ekspor',
        'kuantitas',
        'nilai_ekspor_usd',
        'nilai_ekspor_asli',
        'catatan',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts        = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
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
     * Urut tahun DESC lalu nilai_ekspor_usd DESC; filter tahun jika diisi.
     */
    public function getBySupplier(int $idSupplier, ?int $tahun = null): array
    {
        $builder = $this->where('id_supplier', $idSupplier);

        if ($tahun !== null) {
            $builder->where('tahun', $tahun);
        }

        return $builder->orderBy('tahun', 'DESC')
                       ->orderBy('nilai_ekspor_usd', 'DESC')
                       ->findAll();
    }

    /**
     * Daftar tahun unik (DESC)
     */
    public function getYearsBySupplier(int $idSupplier): array
    {
        $results = $this->select('tahun')
            ->distinct()
            ->where('id_supplier', $idSupplier)
            ->orderBy('tahun', 'DESC')
            ->findAll();

        return array_map('intval', array_column($results, 'tahun'));
    }

    /**
     * SUM nilai_ekspor_usd (0 jika kosong)
     */
    public function getTotalBySupplier(int $idSupplier, ?int $tahun = null): float
    {
        $builder = $this->selectSum('nilai_ekspor_usd', 'total')
            ->where('id_supplier', $idSupplier);

        if ($tahun !== null) {
            $builder->where('tahun', $tahun);
        }

        $row = $builder->first();

        return isset($row['total']) && $row['total'] !== null ? (float) $row['total'] : 0.0;
    }

    /**
     * Mengambil ringkasan data turunan ekspor berdasarkan id_supplier dari tb_history_export_supplier.
     * Mengembalikan array dengan key: pernah_ekspor, kategori_ekspor, frekuensi_ekspor, total_transaksi.
     */
    public function getSummaryBySupplier(?int $idSupplier): array
    {
        static $cache = [];

        if (empty($idSupplier)) {
            return [
                'pernah_ekspor'    => 'Belum',
                'kategori_ekspor'  => '-',
                'frekuensi_ekspor' => '-',
                'total_transaksi'  => 0,
            ];
        }

        if (isset($cache[$idSupplier])) {
            return $cache[$idSupplier];
        }

        $rows = $this->select('tahun, jenis_ekspor')
            ->where('id_supplier', $idSupplier)
            ->findAll();

        if (empty($rows)) {
            return $cache[$idSupplier] = [
                'pernah_ekspor'    => 'Belum',
                'kategori_ekspor'  => '-',
                'frekuensi_ekspor' => '-',
                'total_transaksi'  => 0,
            ];
        }

        $totalTransaksi = count($rows);
        $kategoriList   = [];
        $years          = [];

        foreach ($rows as $r) {
            $cat = trim((string)($r['jenis_ekspor'] ?? ''));
            if ($cat !== '' && $cat !== '-' && !in_array($cat, $kategoriList, true)) {
                $kategoriList[] = $cat;
            }
            if (!empty($r['tahun'])) {
                $y = (int)$r['tahun'];
                if ($y > 0 && !in_array($y, $years, true)) {
                    $years[] = $y;
                }
            }
        }

        $kategoriEkspor = !empty($kategoriList) ? implode(', ', $kategoriList) : '-';

        if (!empty($years)) {
            $minY = min($years);
            $maxY = max($years);
            $yearSpan = ($minY === $maxY) ? "tahun {$minY}" : "tahun {$minY}-{$maxY}";
            $frekuensiEkspor = "{$totalTransaksi} transaksi tercatat ({$yearSpan})";
        } else {
            $frekuensiEkspor = "{$totalTransaksi} transaksi tercatat";
        }

        return $cache[$idSupplier] = [
            'pernah_ekspor'    => 'Ya',
            'kategori_ekspor'  => $kategoriEkspor,
            'frekuensi_ekspor' => $frekuensiEkspor,
            'total_transaksi'  => $totalTransaksi,
        ];
    }
}
