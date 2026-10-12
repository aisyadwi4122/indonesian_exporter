<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\CategoryProductModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $productModel = new ProductModel();
        $categoryModel = new CategoryProductModel();

        // 1. Total Produk
        $totalProduk = $productModel->countAll();

        // 2. Total Kategori
        $totalKategori = $categoryModel->countAll();

        // 3. Produk Terbaru (30 hari terakhir berdasarkan kolom created_at)
        $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
        $produkBaru30Hari = $productModel->where('created_at >=', $thirtyDaysAgo)->countAllResults();

        // 4. 5 Produk Terakhir
        $latestProducts = $productModel
            ->select('tb_produk.*, tb_kategori_produk.nama_kategori_id, tb_kategori_produk.nama_kategori_en')
            ->join('tb_kategori_produk', 'tb_kategori_produk.id_kategori_produk = tb_produk.id_kategori_produk', 'left')
            ->orderBy('tb_produk.id_produk', 'DESC')
            ->limit(5)
            ->find();

        $data = [
            'title'              => 'Dashboard Sales - Indonesian Exporter',
            'page_title'         => 'Dashboard',
            'activeMenu'         => 'dashboard',
            'totalProduk'        => $totalProduk,
            'totalKategori'      => $totalKategori,
            'produkBaru30Hari'   => $produkBaru30Hari,
            'latestProducts'     => $latestProducts,
            'username'           => session()->get('username') ?? 'Sales',
        ];

        return view('sales/dashboard', $data);
    }
}
