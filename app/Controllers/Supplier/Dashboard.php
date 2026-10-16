<?php

namespace App\Controllers\Supplier;

use App\Controllers\BaseController;
use Config\Database;

class Dashboard extends SupplierBase
{
    /**
     * Dashboard sementara Supplier
     * Route: GET /supplier/dashboard
     */
    public function index()
    {
        $supplierId = session()->get('supplier_id');
        $nama = session()->get('supplier_name') ?? session()->get('supplier_username') ?? 'Supplier';

        $namaPerusahaan = 'Akun ini belum terhubung ke perusahaan';
        $totalProduk = 0;
        $totalHistory = 0;

        if (!empty($supplierId)) {
            $db = Database::connect('default');

            $supplier = $db->table('tb_supplier')
                ->select('nama_perusahaan')
                ->where('id_supplier', $supplierId)
                ->get()
                ->getRowArray();

            if ($supplier && !empty($supplier['nama_perusahaan'])) {
                $namaPerusahaan = $supplier['nama_perusahaan'];
            }

            $totalProduk = $db->table('tb_produk_supplier')
                ->where('id_supplier', $supplierId)
                ->countAllResults();

            $totalHistory = $db->table('tb_history_export_supplier')
                ->where('id_supplier', $supplierId)
                ->countAllResults();
        }

        $data = [
            'title'           => 'Area Supplier - Indonesian Exporter',
            'page_title'      => 'Dashboard',
            'activeMenu'      => 'dashboard',
            'nama'            => $nama,
            'nama_perusahaan' => $namaPerusahaan,
            'total_produk'    => $totalProduk,
            'total_history'   => $totalHistory,
        ];

        return view('supplier/dashboard', $data);
    }
}
