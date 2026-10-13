<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;

class Produk extends BaseController
{
    public function index()
    {
        $data = [
            'title'      => 'Produk Sales - Indonesian Exporter',
            'page_title' => 'Produk',
            'activeMenu' => 'produk',
        ];

        return view('sales/produk/index', $data);
    }
}
