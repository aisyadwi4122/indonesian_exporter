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

    /**
     * Menambahkan data history ekspor milik supplier yang sedang login
     * Route: POST /supplier/history/tambah
     */
    public function tambah()
    {
        if ($redirect = $this->requireCompany()) {
            return $redirect;
        }

        $supplierId = (int) session()->get('supplier_id');
        if ($supplierId <= 0) {
            session()->setFlashdata('error', 'Akun Anda tidak terhubung ke profil perusahaan yang valid.');
            return redirect()->to(base_url('supplier/history'));
        }

        $rules = [
            'tahun' => [
                'rules'  => 'required|numeric|exact_length[4]',
                'errors' => [
                    'required'     => 'Tahun ekspor wajib diisi.',
                    'numeric'      => 'Tahun ekspor harus berupa angka 4 digit.',
                    'exact_length' => 'Tahun ekspor harus 4 digit angka.'
                ]
            ],
            'produk' => [
                'rules'  => 'required|min_length[2]|max_length[255]',
                'errors' => [
                    'required'   => 'Nama produk ekspor wajib diisi.',
                    'min_length' => 'Nama produk minimal 2 karakter.',
                    'max_length' => 'Nama produk maksimal 255 karakter.'
                ]
            ],
            'negara_tujuan' => [
                'rules'  => 'required|min_length[2]|max_length[150]',
                'errors' => [
                    'required'   => 'Negara tujuan ekspor wajib diisi.',
                    'min_length' => 'Negara tujuan minimal 2 karakter.',
                    'max_length' => 'Negara tujuan maksimal 150 karakter.'
                ]
            ],
            'nilai_ekspor_usd' => [
                'rules'  => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric'               => 'Nilai ekspor USD harus berupa angka.',
                    'greater_than_equal_to' => 'Nilai ekspor USD tidak boleh bernilai negatif.'
                ]
            ],
            'jenis_ekspor' => [
                'rules'  => 'permit_empty|max_length[100]',
                'errors' => [
                    'max_length' => 'Jenis ekspor maksimal 100 karakter.'
                ]
            ],
            'kuantitas' => [
                'rules'  => 'permit_empty|max_length[100]',
                'errors' => [
                    'max_length' => 'Kuantitas maksimal 100 karakter.'
                ]
            ],
            'catatan' => [
                'rules'  => 'permit_empty|max_length[500]',
                'errors' => [
                    'max_length' => 'Catatan maksimal 500 karakter.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $nilaiUsd = trim((string) $this->request->getPost('nilai_ekspor_usd'));

        // id_supplier WAJIB diambil dari sesi login supplier, TIDAK dari input form
        $data = [
            'id_supplier'      => $supplierId,
            'tahun'            => (int) $this->request->getPost('tahun'),
            'produk'           => trim((string) $this->request->getPost('produk')),
            'negara_tujuan'    => trim((string) $this->request->getPost('negara_tujuan')),
            'jenis_ekspor'     => trim((string) $this->request->getPost('jenis_ekspor')) ?: null,
            'kuantitas'        => trim((string) $this->request->getPost('kuantitas')) ?: null,
            'nilai_ekspor_usd' => $nilaiUsd !== '' && is_numeric($nilaiUsd) ? (float) $nilaiUsd : null,
            'catatan'          => trim((string) $this->request->getPost('catatan')) ?: null,
        ];

        $this->historyModel->insert($data);

        session()->setFlashdata('success', 'History ekspor berhasil ditambahkan.');
        return redirect()->to(base_url('supplier/history'));
    }
}
