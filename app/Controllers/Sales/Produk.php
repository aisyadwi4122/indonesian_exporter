<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\SalesProductModel;

class Produk extends BaseController
{
    public function index()
    {
        $productModel = new SalesProductModel();
        $q = trim((string)($this->request->getGet('q') ?? ''));

        if ($q !== '') {
            $productModel->groupStart()
                ->like('nama_produk_id', $q)
                ->orLike('nama_produk_en', $q)
                ->orLike('deskripsi_produk_id', $q)
                ->orLike('deskripsi_produk_en', $q)
                ->groupEnd();
        }

        $products = $productModel->orderBy('id_produk', 'ASC')->findAll();

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
