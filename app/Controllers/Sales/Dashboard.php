<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $data = [
            'title'      => 'Dashboard Sales - Indonesian Exporter',
            'page_title' => 'Dashboard',
            'activeMenu' => 'dashboard',
        ];

        return view('sales/dashboard', $data);
    }
}
