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
