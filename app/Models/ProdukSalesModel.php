<?php

namespace App\Models;

/**
 * Model ProdukSales untuk pengelolaan data produk sales di area Admin.
 * Menggunakan tabel utama `tb_produk_supplier` yang terhubung dengan `tb_supplier`.
 */
class ProdukSalesModel extends SupplierProductModel
{
    /**
     * Mengambil daftar produk untuk panel admin dengan join supplier dan pencarian
     *
     * @param array $filter
     * @return $this
     */
    public function getAdminList(array $filter = [])
    {
        $this->select('tb_produk_supplier.*, tb_supplier.nama_perusahaan, tb_supplier.domisili_usaha')
             ->join('tb_supplier', 'tb_supplier.id_supplier = tb_produk_supplier.id_supplier', 'left');

        if (!empty($filter['q'])) {
            $q = trim((string)$filter['q']);
            $this->groupStart()
                 ->like('tb_produk_supplier.nama_produk', $q)
                 ->orLike('tb_produk_supplier.lokasi_barang', $q)
                 ->orLike('tb_produk_supplier.spesifikasi', $q)
                 ->orLike('tb_supplier.nama_perusahaan', $q);

            // Jika pencarian berupa kode IE-xxxx atau angka ID
            if (preg_match('/^IE-?0*(\d+)$/i', $q, $matches)) {
                $idSearch = (int)$matches[1];
                $this->orWhere('tb_produk_supplier.id_produk_supplier', $idSearch);
            } elseif (ctype_digit($q)) {
                $this->orWhere('tb_produk_supplier.id_produk_supplier', (int)$q);
            }

            $this->groupEnd();
        }

        $this->orderBy('tb_produk_supplier.id_produk_supplier', 'DESC');

        return $this;
    }

    /**
     * Mengambil daftar produk terpadu 127 produk (10 Supplier IE di awal + 117 ECP)
     * Sama persis dengan data dan urutan yang tampil di halaman Sales.
     * Mendukung filter kata kunci ($q), nama perusahaan ($perusahaan), dan lokasi ($lokasi).
     *
     * @param string $q Kata kunci pencarian
     * @param string $perusahaan Kunci perusahaan ternormalisasi (nama_kunci)
     * @param string $lokasi Teks lokasi pencocokan persis (case-insensitive & trimmed)
     * @return array
     */
    public function getUnifiedProducts(string $q = '', string $perusahaan = '', string $lokasi = ''): array
    {
        $items = [];
        $db = \Config\Database::connect();
        $q = trim($q);
        $perusahaan = trim($perusahaan);
        $lokasi = trim($lokasi);

        // 1. Ambil data Supplier Produk (dari tb_produk_supplier join tb_supplier)
        $spBuilder = $db->table('tb_produk_supplier')
            ->select('tb_produk_supplier.*, tb_supplier.nama_perusahaan, tb_supplier.nama_kunci, tb_supplier.domisili_usaha')
            ->join('tb_supplier', 'tb_supplier.id_supplier = tb_produk_supplier.id_supplier', 'left')
            ->orderBy('tb_produk_supplier.id_produk_supplier', 'ASC');

        if ($perusahaan !== '') {
            $spBuilder->where('tb_supplier.nama_kunci', $perusahaan);
        }

        if ($lokasi !== '') {
            $locNorm = strtolower($lokasi);
            $spBuilder->groupStart()
                ->where('LOWER(TRIM(tb_produk_supplier.lokasi_barang))', $locNorm)
                ->orGroupStart()
                    ->groupStart()
                        ->where('tb_produk_supplier.lokasi_barang IS NULL', null, false)
                        ->orWhere('TRIM(tb_produk_supplier.lokasi_barang)', '')
                    ->groupEnd()
                    ->where('LOWER(TRIM(tb_supplier.domisili_usaha))', $locNorm)
                ->groupEnd()
            ->groupEnd();
        }

        $spRows = $spBuilder->get()->getResultArray();
        foreach ($spRows as $r) {
            $idProd = (int)$r['id_produk_supplier'];
            $kode = self::getKodeProduk($idProd);
            $photoUrl = self::photoUrl($r, 1);
            $namaPerusahaan = !empty($r['nama_perusahaan']) ? $r['nama_perusahaan'] : '-';
            $namaKunci = !empty($r['nama_kunci']) ? $r['nama_kunci'] : SupplierModel::makeKey($namaPerusahaan);
            $lokasiItem = !empty($r['lokasi_barang']) ? trim($r['lokasi_barang']) : (!empty($r['domisili_usaha']) ? trim($r['domisili_usaha']) : '-');

            if ($lokasi !== '' && strtolower(trim($lokasiItem)) !== strtolower($lokasi)) {
                continue;
            }

            if ($q !== '') {
                $haystack = strtolower($kode . ' ' . $r['nama_produk'] . ' ' . $namaPerusahaan . ' ' . $lokasiItem . ' ' . ($r['spesifikasi'] ?? ''));
                if (strpos($haystack, strtolower($q)) === false) {
                    continue;
                }
            }

            $items[] = [
                'id_produk_supplier' => $idProd,
                'raw_id'             => $idProd,
                'sumber'             => 'supplier',
                'kode_produk'        => $kode,
                'nama_produk'        => $r['nama_produk'] ?: 'Produk Supplier',
                'nama_perusahaan'    => $namaPerusahaan,
                'nama_kunci'         => $namaKunci,
                'domisili_usaha'     => $r['domisili_usaha'] ?? '',
                'lokasi_barang'      => $lokasiItem,
                'deskripsi'          => $r['deskripsi'] ?? '',
                'spesifikasi'        => $r['spesifikasi'] ?? '',
                'kapasitas_produksi' => $r['kapasitas_produksi'] ?? '',
                'moq'                => $r['moq'] ?? '',
                'harga_exw'          => $r['harga_exw'] ?? '',
                'komisi'             => $r['komisi'] ?? '',
                'sudah_ekspor'       => $r['sudah_ekspor'] ?: 'Belum',
                'foto_1'             => $r['foto_1'] ?? null,
                'foto_url'           => $photoUrl,
                'id_supplier'        => $r['id_supplier'] ?? null,
            ];
        }

        // 2. Ambil data ECP (117 produk dari tb_ecp_ekspor)
        $ecpBuilder = $db->table('tb_ecp_ekspor')->orderBy('id_ecp', 'ASC');

        if ($lokasi !== '') {
            $locNorm = strtolower($lokasi);
            $ecpBuilder->groupStart()
                ->where('LOWER(TRIM(domisili))', $locNorm)
                ->orGroupStart()
                    ->groupStart()
                        ->where('domisili IS NULL', null, false)
                        ->orWhere('TRIM(domisili)', '')
                    ->groupEnd()
                    ->where('LOWER(TRIM(wilayah_ecp))', $locNorm)
                ->groupEnd()
            ->groupEnd();
        }

        $ecpRows = $ecpBuilder->get()->getResultArray();
        foreach ($ecpRows as $r) {
            $idEcp = (int)$r['id_ecp'];
            $virtualId = 10000 + $idEcp;
            $kode = 'ECP-' . str_pad((string)$idEcp, 4, '0', STR_PAD_LEFT);
            $namaProduk = !empty($r['produk_ekspor']) ? $r['produk_ekspor'] : (!empty($r['produk_dipasarkan']) ? $r['produk_dipasarkan'] : 'Produk Ekspor');
            $namaPerusahaan = !empty($r['nama_perusahaan']) ? $r['nama_perusahaan'] : '-';
            $namaKunci = SupplierModel::makeKey($namaPerusahaan);
            $lokasiItem = !empty($r['domisili']) ? trim($r['domisili']) : (!empty($r['wilayah_ecp']) ? trim($r['wilayah_ecp']) : '-');
            $spesifikasi = !empty($r['kategori_produk']) ? ('Kategori: ' . $r['kategori_produk'] . (!empty($r['merk_dagang']) ? ' | Merk: ' . $r['merk_dagang'] : '')) : ($r['produk_dipasarkan'] ?: '');

            if ($perusahaan !== '' && $namaKunci !== $perusahaan) {
                continue;
            }

            if ($lokasi !== '' && strtolower(trim($lokasiItem)) !== strtolower($lokasi)) {
                continue;
            }

            if ($q !== '') {
                $haystack = strtolower($kode . ' ' . $namaProduk . ' ' . $namaPerusahaan . ' ' . $lokasiItem . ' ' . $spesifikasi);
                if (strpos($haystack, strtolower($q)) === false) {
                    continue;
                }
            }

            $items[] = [
                'id_produk_supplier' => $virtualId,
                'raw_id'             => $idEcp,
                'sumber'             => 'ecp',
                'kode_produk'        => $kode,
                'nama_produk'        => $namaProduk,
                'nama_perusahaan'    => $namaPerusahaan,
                'nama_kunci'         => $namaKunci,
                'domisili_usaha'     => $r['domisili'] ?: ($r['wilayah_ecp'] ?: ''),
                'lokasi_barang'      => $lokasiItem,
                'deskripsi'          => $r['produk_dipasarkan'] ?? '',
                'spesifikasi'        => $spesifikasi,
                'kapasitas_produksi' => !empty($r['kapasitas_produksi']) ? $r['kapasitas_produksi'] : (!empty($r['kuantitas_ekspor']) ? 'Ekspor: ' . $r['kuantitas_ekspor'] : '-'),
                'moq'                => $r['kuantitas_ekspor'] ?: '-',
                'harga_exw'          => isset($r['nilai_ekspor_usd']) ? ('Ekspor: $' . number_format((float)$r['nilai_ekspor_usd'], 0, ',', '.') . ' USD') : '-',
                'komisi'             => null,
                'sudah_ekspor'       => 'Sudah',
                'foto_1'             => null,
                'foto_url'           => null,
                'id_supplier'        => null,
            ];
        }

        return $items;
    }

    /**
     * Mengambil semua item katalog produk sales terpadu (130 item)
     * tanpa filter dan tanpa paginasi, identik dengan katalog sales.
     *
     * @return array
     */
    public function getAllCatalogItems(): array
    {
        return $this->getUnifiedProducts('', '', '');
    }

    /**
     * Mengambil semua produk lain milik perusahaan yang sama dari tb_produk_supplier
     * DAN tb_ecp_ekspor dengan format seragam, mengecualikan produk yang sedang dibuka.
     * Pencocokan nama perusahaan dilakukan dengan normalisasi di kedua sisi via SupplierModel::makeKey().
     *
     * @param string $namaPerusahaan Nama perusahaan produk yang sedang dibuka
     * @param int    $excludeId      ID produk yang sedang dibuka (ID virtual 10000+id_ecp jika ECP)
     * @param int    $idSupplier     Opsional: id_supplier jika produk aktif berasal dari tb_produk_supplier
     * @return array
     */
    public function getOtherUnifiedProductsByCompany(string $namaPerusahaan, int $excludeId, int $idSupplier = 0): array
    {
        $targetKey = SupplierModel::makeKey($namaPerusahaan);
        if ($targetKey === '' && $idSupplier <= 0) {
            return [];
        }

        $db = \Config\Database::connect();
        $results = [];
        $seenIds = [];

        // 1. Produk dari tb_produk_supplier (join tb_supplier untuk mendapatkan nama_perusahaan)
        $spRows = $db->table('tb_produk_supplier')
            ->select('tb_produk_supplier.id_produk_supplier, tb_produk_supplier.id_supplier, tb_produk_supplier.nama_produk, tb_produk_supplier.foto_1, tb_supplier.nama_perusahaan')
            ->join('tb_supplier', 'tb_supplier.id_supplier = tb_produk_supplier.id_supplier', 'left')
            ->orderBy('tb_produk_supplier.nama_produk', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($spRows as $r) {
            $idProd = (int)$r['id_produk_supplier'];
            if ($idProd === $excludeId || isset($seenIds[$idProd])) {
                continue;
            }

            $rowSupplierId = (int)($r['id_supplier'] ?? 0);
            $rowKey = SupplierModel::makeKey((string)($r['nama_perusahaan'] ?? ''));

            $isMatch = ($idSupplier > 0 && $rowSupplierId === $idSupplier)
                || ($targetKey !== '' && $rowKey === $targetKey);

            if (!$isMatch) {
                continue;
            }

            $seenIds[$idProd] = true;
            $results[] = [
                'id_produk_supplier' => $idProd,
                'id_supplier'        => $rowSupplierId > 0 ? $rowSupplierId : null,
                'nama_produk'        => !empty($r['nama_produk']) ? $r['nama_produk'] : 'Produk Supplier',
                'foto_1'             => $r['foto_1'] ?? null,
                'foto_url'           => self::photoUrl($r, 1),
                'sumber'             => 'supplier',
            ];
        }

        // 2. Produk dari tb_ecp_ekspor (virtual ID = 10000 + id_ecp)
        if ($targetKey !== '') {
            $ecpRows = $db->table('tb_ecp_ekspor')
                ->select('id_ecp, nama_perusahaan, produk_ekspor, produk_dipasarkan')
                ->orderBy('id_ecp', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($ecpRows as $r) {
                $idEcp = (int)$r['id_ecp'];
                $virtualId = 10000 + $idEcp;
                if ($virtualId === $excludeId || isset($seenIds[$virtualId])) {
                    continue;
                }

                $rowKey = SupplierModel::makeKey((string)($r['nama_perusahaan'] ?? ''));
                if ($rowKey !== $targetKey) {
                    continue;
                }

                $namaProduk = !empty($r['produk_ekspor'])
                    ? $r['produk_ekspor']
                    : (!empty($r['produk_dipasarkan']) ? $r['produk_dipasarkan'] : 'Produk Ekspor');

                $seenIds[$virtualId] = true;
                $results[] = [
                    'id_produk_supplier' => $virtualId,
                    'id_supplier'        => null,
                    'nama_produk'        => $namaProduk,
                    'foto_1'             => null,
                    'foto_url'           => null,
                    'sumber'             => 'ecp',
                ];
            }
        }

        return $results;
    }

    /**
     * Menghitung total seluruh produk terpadu (Supplier IE + ECP)
     * Sama persis dengan sumber data yang tampil di halaman Produk Sales.
     *
     * @return int
     */
    public function countUnifiedProducts(): int
    {
        $db = \Config\Database::connect();
        $supplierCount = $db->table('tb_produk_supplier')->countAllResults();
        $ecpCount      = $db->table('tb_ecp_ekspor')->countAllResults();

        return (int)($supplierCount + $ecpCount);
    }

    /**
     * Mendapatkan saran kode produk berikutnya berdasarkan auto-increment ID
     *
     * @return string
     */
    public function getNextSuggestedCode(): string
    {
        $row = $this->selectMax('id_produk_supplier')->first();
        $nextId = ((int)($row['id_produk_supplier'] ?? 0)) + 1;
        return self::getKodeProduk($nextId);
    }
}
