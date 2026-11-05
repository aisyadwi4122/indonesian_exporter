<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\ProdukSalesModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $productSalesModel = new ProdukSalesModel();
        $totalProduk = $productSalesModel->countUnifiedProducts();

        $data = [
            'title'        => 'Dashboard Sales - Indonesian Exporter',
            'page_title'   => 'Dashboard',
            'activeMenu'   => 'dashboard',
            'totalProduk'  => $totalProduk,
            'username'     => session()->get('username') ?? 'Sales',
        ];

        return view('sales/dashboard', $data);
    }
}
