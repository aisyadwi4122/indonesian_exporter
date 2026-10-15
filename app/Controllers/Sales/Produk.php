<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\SupplierProductModel;
use App\Models\SupplierModel;
use App\Models\SupplierExportHistoryModel;
use App\Models\ProductModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Produk extends BaseController
{
    public function index()
    {
        $supplierProductModel = new SupplierProductModel();
        $q = trim((string)($this->request->getGet('q') ?? ''));

        $builder = $supplierProductModel->select('
            tb_produk_supplier.*,
            tb_produk_supplier.id_produk_supplier as id_produk,
            tb_produk_supplier.nama_produk as nama_produk_id,
            tb_supplier.nama_perusahaan,
            tb_supplier.jenis_perusahaan,
            tb_supplier.domisili_usaha
        ')->join('tb_supplier', 'tb_supplier.id_supplier = tb_produk_supplier.id_supplier', 'left');

        if ($q !== '') {
            $builder->groupStart()
                ->like('tb_produk_supplier.nama_produk', $q)
                ->orLike('tb_supplier.nama_perusahaan', $q)
                ->orLike('tb_produk_supplier.spesifikasi', $q)
                ->orLike('tb_produk_supplier.lokasi_barang', $q)
                ->orLike('tb_supplier.domisili_usaha', $q)
            ->groupEnd();
        }

        $products = $builder->orderBy('tb_produk_supplier.id_produk_supplier', 'DESC')->findAll();

        $data = [
            'title'      => 'Produk Sales - Indonesian Exporter',
            'page_title' => 'Produk',
            'activeMenu' => 'produk',
            'products'   => $products,
            'q'          => $q,
        ];

        return view('sales/produk/index', $data);
    }

    public function detail($id = null)
    {
        if ($id === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $supplierProductModel = new SupplierProductModel();
        $produkSupplier = $supplierProductModel->find($id);

        $supplierModel = new SupplierModel();
        $historyModel = new SupplierExportHistoryModel();

        if ($produkSupplier) {
            $supplier = $supplierModel->find($produkSupplier['id_supplier']);
            $histories = $historyModel->where('id_supplier', $produkSupplier['id_supplier'])
                ->orderBy('tahun', 'DESC')
                ->orderBy('nilai_ekspor_usd', 'DESC')
                ->findAll();

            $years = [];
            $totalNilai = 0.0;
            foreach ($histories as $h) {
                if (!empty($h['tahun']) && !in_array((int)$h['tahun'], $years, true)) {
                    $years[] = (int)$h['tahun'];
                }
                if (isset($h['nilai_ekspor_usd']) && is_numeric($h['nilai_ekspor_usd'])) {
                    $totalNilai += (float)$h['nilai_ekspor_usd'];
                }
            }
            rsort($years);

            $defaultImg = 'assets/img/logo.png';
            $foto = !empty($produkSupplier['foto_1']) ? $produkSupplier['foto_1'] : $defaultImg;

            $product = [
                'id_produk'           => $produkSupplier['id_produk_supplier'],
                'nama_produk_id'      => $produkSupplier['nama_produk'],
                'nama_produk_en'      => $supplier['nama_perusahaan'] ?? '',
                'nama_kategori_id'    => 'Produk Supplier',
                'nama_kategori_en'    => 'Supplier Product',
                'foto_produk'         => $foto,
                'deskripsi_produk_id' => $produkSupplier['spesifikasi'] ?? '-',
                'deskripsi_produk_en' => $produkSupplier['spesifikasi'] ?? '-',
            ];
        } else {
            // Fallback jika id berasal dari tb_produk
            $productModel = new ProductModel();
            $product = $productModel->select('tb_produk.*, tb_kategori_produk.nama_kategori_id, tb_kategori_produk.nama_kategori_en')
                ->join('tb_kategori_produk', 'tb_kategori_produk.id_kategori_produk = tb_produk.id_kategori_produk', 'left')
                ->where('tb_produk.id_produk', $id)
                ->first();

            if (!$product) {
                throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan');
            }

            // TODO: isi pada tahap data supplier
            $produkSupplier = null;   // array|null: data produk dari tb_produk_supplier
            $supplier       = null;   // array|null: data perusahaan dari tb_supplier
            $histories      = [];     // array: baris tb_history_export_supplier
            $years          = [];     // array tahun yang tersedia
            $totalNilai     = 0.0;
        }

        $data = [
            'title'          => ($product['nama_produk_id'] ?? 'Detail Produk') . ' - Area Sales',
            'page_title'     => 'Detail Produk',
            'activeMenu'     => 'produk',
            'product'        => $product,
            'produkSupplier' => $produkSupplier,
            'supplier'       => $supplier,
            'histories'      => $histories,
            'years'          => $years,
            'totalNilai'     => $totalNilai,
        ];

        return view('sales/produk/detail', $data);
    }
}
