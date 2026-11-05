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
     *
     * @param string $q Kata kunci pencarian
     * @return array
     */
    public function getUnifiedProducts(string $q = ''): array
    {
        $items = [];
        $db = \Config\Database::connect();
        $q = trim($q);

        // 1. Ambil data Supplier Produk (10 produk dari tb_produk_supplier join tb_supplier)
        $spBuilder = $db->table('tb_produk_supplier')
            ->select('tb_produk_supplier.*, tb_supplier.nama_perusahaan, tb_supplier.domisili_usaha')
            ->join('tb_supplier', 'tb_supplier.id_supplier = tb_produk_supplier.id_supplier', 'left')
            ->orderBy('tb_produk_supplier.id_produk_supplier', 'ASC');

        $spRows = $spBuilder->get()->getResultArray();
        foreach ($spRows as $r) {
            $idProd = (int)$r['id_produk_supplier'];
            $kode = self::getKodeProduk($idProd);
            $photoUrl = self::photoUrl($r, 1);
            $namaPerusahaan = !empty($r['nama_perusahaan']) ? $r['nama_perusahaan'] : '-';
            $lokasi = !empty($r['lokasi_barang']) ? $r['lokasi_barang'] : (!empty($r['domisili_usaha']) ? $r['domisili_usaha'] : '-');

            if ($q !== '') {
                $haystack = strtolower($kode . ' ' . $r['nama_produk'] . ' ' . $namaPerusahaan . ' ' . $lokasi . ' ' . ($r['spesifikasi'] ?? ''));
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
                'domisili_usaha'     => $r['domisili_usaha'] ?? '',
                'lokasi_barang'      => $lokasi,
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
        $ecpRows = $ecpBuilder->get()->getResultArray();
        foreach ($ecpRows as $r) {
            $idEcp = (int)$r['id_ecp'];
            $virtualId = 10000 + $idEcp;
            $kode = 'ECP-' . str_pad((string)$idEcp, 4, '0', STR_PAD_LEFT);
            $namaProduk = !empty($r['produk_ekspor']) ? $r['produk_ekspor'] : (!empty($r['produk_dipasarkan']) ? $r['produk_dipasarkan'] : 'Produk Ekspor');
            $namaPerusahaan = !empty($r['nama_perusahaan']) ? $r['nama_perusahaan'] : '-';
            $lokasi = !empty($r['domisili']) ? $r['domisili'] : (!empty($r['wilayah_ecp']) ? $r['wilayah_ecp'] : '-');
            $spesifikasi = !empty($r['kategori_produk']) ? ('Kategori: ' . $r['kategori_produk'] . (!empty($r['merk_dagang']) ? ' | Merk: ' . $r['merk_dagang'] : '')) : ($r['produk_dipasarkan'] ?: '');

            if ($q !== '') {
                $haystack = strtolower($kode . ' ' . $namaProduk . ' ' . $namaPerusahaan . ' ' . $lokasi . ' ' . $spesifikasi);
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
                'domisili_usaha'     => $r['domisili'] ?: ($r['wilayah_ecp'] ?: ''),
                'lokasi_barang'      => $lokasi,
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
