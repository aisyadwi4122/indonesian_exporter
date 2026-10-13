<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class Produk extends BaseController
{
    public function index()
    {
        $productModel = new ProductModel();
        $q = trim((string)($this->request->getGet('q') ?? ''));

        // Hanya tampilkan produk khusus area Sales (tidak menggunakan data produk web Indonesian Exporter)
        $productModel->whereIn('id_produk', [264, 265, 266]);

        if ($q !== '') {
            $productModel->groupStart()
                ->like('nama_produk_id', $q)
                ->orLike('nama_produk_en', $q)
                ->orLike('deskripsi_produk_id', $q)
                ->orLike('deskripsi_produk_en', $q)
                ->groupEnd();
        }

        $products = $productModel->orderBy('id_produk', 'DESC')->findAll();

        $data = [
            'title'      => 'Produk Sales - Indonesian Exporter',
            'page_title' => 'Produk',
            'activeMenu' => 'produk',
            'products'   => $products,
            'q'          => $q,
        ];

        return view('sales/produk/index', $data);
    }
}
