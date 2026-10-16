<?php

namespace App\Controllers\Supplier;

use App\Models\SupplierProductModel;
use Config\Database;

class Produk extends SupplierBase
{
    protected $supplierProductModel;

    public function __construct()
    {
        $this->supplierProductModel = new SupplierProductModel();
    }

    /**
     * Menampilkan daftar produk supplier
     * Route: GET /supplier/produk
     */
    public function index()
    {
        if ($redirect = $this->requireCompany()) {
            return $redirect;
        }

        $supplierId = session()->get('supplier_id');
        $products   = $this->supplierProductModel->where('id_supplier', $supplierId)->findAll();

        $data = [
            'title'      => 'Katalog Produk - Area Supplier',
            'page_title' => 'Produk Supplier',
            'activeMenu' => 'produk',
            'products'   => $products,
        ];

        return view('supplier/produk/index', $data);
    }
}
