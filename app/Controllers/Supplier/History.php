<?php

namespace App\Controllers\Supplier;

use App\Models\SupplierExportHistoryModel;
use Config\Database;

class History extends SupplierBase
{
    protected $historyModel;

    public function __construct()
    {
        $this->historyModel = new SupplierExportHistoryModel();
    }

    /**
     * Menampilkan daftar history ekspor supplier
     * Route: GET /supplier/history
     */
    public function index()
    {
        if ($redirect = $this->requireCompany()) {
            return $redirect;
        }

        $supplierId = session()->get('supplier_id');
        $histories  = $this->historyModel->where('id_supplier', $supplierId)->orderBy('tahun', 'DESC')->findAll();

        $data = [
            'title'      => 'History Ekspor - Area Supplier',
            'page_title' => 'History Ekspor',
            'activeMenu' => 'history',
            'histories'  => $histories,
        ];

        return view('supplier/history/index', $data);
    }
}
