<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProfilModel;

class SalesController extends BaseController
{
    /**
     * Menampilkan halaman login Sales
     * Route: GET /sales atau GET /sales/login
     */
    public function index()
    {
        // 1. Pengecekan jika sesi Sales sudah aktif (opsional ketika backend login sudah aktif)
        if (session()->get('sales_logged_in')) {
            // Contoh redirect jika sudah login di kemudian hari:
            // return redirect()->to(base_url('sales/dashboard'));
        }

        // 2. Ambil profil perusahaan jika tersedia untuk menjaga konsistensi logo & nama
        $profil = null;
        try {
            $profilModel = new ProfilModel();
            $profil = $profilModel->where('role', 'user')->first();
        } catch (\Throwable $e) {
            // Fallback aman jika koneksi DB offline / konfigurasi profil belum siap
            $profil = null;
        }

        $data = [
            'title'      => 'Login Sales | Indonesia Eksporter',
            'profil'     => $profil,
            'action_url' => base_url('sales/login'),
        ];

        return view('sales/login', $data);
    }

    /**
     * Endpoint penerima form login Sales (Method POST)
     * Route: POST /sales/login
     *
     * Catatan:
     * - Tidak menggunakan username/password hardcoded.
     * - Tidak mengubah database atau LoginController Admin yang sudah ada.
     * - Disediakan validasi input dan placeholder siap pakai untuk backend developer.
     */
    public function loginProcess()
    {
        // 1. Validasi Input Form
        $rules = [
            'username' => [
                'rules'  => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required'   => 'Username atau ID Sales wajib diisi.',
                    'min_length' => 'Username minimal 3 karakter.',
                    'max_length' => 'Username maksimal 100 karakter.'
                ]
            ],
            'password' => [
                'rules'  => 'required|min_length[4]',
                'errors' => [
                    'required'   => 'Password wajib diisi.',
                    'min_length' => 'Password minimal 4 karakter.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            // Kembalikan ke halaman login beserta input sebelumnya dan pesan validasi
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        /*
         * =====================================================================
         * PANDUAN INTEGRASI BACKEND SALES (Untuk Tim Backend):
         * =====================================================================
         * Silakan sambungkan logika query ke tabel Sales Anda di bawah ini:
         * 
         * Contoh menggunakan Model:
         * -------------------------------------------------------------
         * $salesModel = new \App\Models\SalesModel();
         * $salesUser  = $salesModel->where('username', $username)->first();
         * 
         * if ($salesUser && password_verify($password, $salesUser['password'])) {
         *     // Set session Sales
         *     session()->set([
         *         'sales_id'        => $salesUser['id_sales'],
         *         'sales_name'      => $salesUser['nama_sales'],
         *         'sales_username'  => $salesUser['username'],
         *         'sales_email'     => $salesUser['email'],
         *         'sales_logged_in' => true,
         *     ]);
         *     return redirect()->to(base_url('sales/dashboard'));
         * } else {
         *     session()->setFlashdata('error', 'Kombinasi Username dan Password Sales tidak sesuai.');
         *     return redirect()->back()->withInput();
         * }
         * =====================================================================
         */

        // Placeholder respon jika backend database Sales belum dihubungkan:
        session()->setFlashdata(
            'info',
            'Form login POST berhasil diterima oleh controller <code>SalesController::loginProcess()</code>. ' .
            'Frontend form telah siap dihubungkan ke tabel/model Sales backend.'
        );

        return redirect()->back()->withInput();
    }
}
