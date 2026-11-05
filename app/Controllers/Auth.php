<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class Auth extends BaseController
{
    /**
     * Menampilkan formulir login Sales
     * Route: GET /sales atau GET /sales/login
     */
    public function login()
    {
        // Jika sudah login dengan role sales, redirect ke dashboard sales
        if (session()->get('sales_logged_in') && session()->get('role') === 'sales') {
            return redirect()->to(base_url('sales/dashboard'));
        }

        /*
         * ==========================================================================
         * Gambar latar belakang login sales / Sales login background image
         * ==========================================================================
         * Menggunakan foto slider web resmi Indonesian Exporter:
         * Path: assets/img/slider/slide1.png
         */
        $bgImage = 'assets/img/slider/slide1.png';

        $data = [
            'title'      => 'Log in to Indonesian Exporter',
            'action_url' => base_url('sales'),
            'bg_image'   => $bgImage,
        ];

        return view('sales/login', $data);
    }

    /**
     * Memproses autentikasi login (Method POST)
     * Route: POST /sales/login
     */
    public function attempt()
    {
        // 1. Validasi input: username dan password wajib diisi
        $rules = [
            'username' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Username wajib diisi.'
                ]
            ],
            'password' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Password wajib diisi.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', 'Username atau password salah');
            return redirect()->back()->withInput();
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $db = Database::connect();

        // 2. Autentikasi Sales: Cek tabel tb_sales (SalesModel)
        $salesModel = new \App\Models\SalesModel();
        $salesUser  = $salesModel->where('username', $username)->first();

        if ($salesUser) {
            if (isset($salesUser['status']) && $salesUser['status'] !== 'aktif') {
                session()->setFlashdata('error', 'Akun Sales Anda berstatus nonaktif. Silakan hubungi admin.');
                return redirect()->back()->withInput();
            }

            $passwordHash = $salesUser['password'] ?? '';
            $isPasswordValid = false;

            if (password_verify($password, $passwordHash)) {
                $isPasswordValid = true;
            } elseif ($password === $passwordHash) {
                // Fallback jika password di database belum di-hash
                $isPasswordValid = true;
            }

            if (!$isPasswordValid) {
                session()->setFlashdata('error', 'Username atau password salah');
                return redirect()->back()->withInput();
            }

            session()->set([
                'user_id'         => $salesUser['id_sales'],
                'sales_id'        => $salesUser['id_sales'],
                'sales_name'      => $salesUser['nama_sales'],
                'nama_sales'      => $salesUser['nama_sales'],
                'username'        => $salesUser['username'],
                'sales_username'  => $salesUser['username'],
                'sales_email'     => $salesUser['email'] ?? '',
                'role'            => 'sales',
                'sales_logged_in' => true,
            ]);

        // Catat login history (Non-blocking)
        try {
            $loginHistoryModel = new \App\Models\LoginHistoryModel();
            $ipAddress = (string) $this->request->getIPAddress();
            $userAgent = (string) $this->request->getUserAgent()->getAgentString();
            $loginHistoryModel->recordLogin((int) $user['id_perusahaan'], $ipAddress, $userAgent);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal mencatat login history: ' . $e->getMessage());
        }

            // Catat login history (Non-blocking)
            try {
                $loginHistoryModel = new \App\Models\LoginHistoryModel();
                $ipAddress = (string) $this->request->getIPAddress();
                $userAgent = (string) $this->request->getUserAgent()->getAgentString();
                $loginHistoryModel->recordLogin((int) $salesUser['id_sales'], $ipAddress, $userAgent);
            } catch (\Throwable $e) {
                log_message('error', 'Gagal mencatat login history: ' . $e->getMessage());
            }

            return redirect()->to(base_url('sales/dashboard'));
        }

        // 3. Fallback: Ambil user dari tabel tb_profil dengan username dan role = 'sales'
        $user = $db->table('tb_profil')
            ->where('username', $username)
            ->where('role', 'sales')
            ->get()
            ->getRowArray();

        // Verifikasi pengguna dan role
        if (!$user) {
            session()->setFlashdata('error', 'Username atau password salah');
            return redirect()->back()->withInput();
        }

        // Verifikasi password menggunakan password_verify() (dengan fallback plain-text jika legacy)
        $passwordHash = $user['password'] ?? '';
        $isPasswordValid = false;

        if (password_verify($password, $passwordHash)) {
            $isPasswordValid = true;
        } elseif ($password === $passwordHash) {
            // Fallback jika password di database belum di-hash
            $isPasswordValid = true;
        }

        if (!$isPasswordValid) {
            session()->setFlashdata('error', 'Username atau password salah');
            return redirect()->back()->withInput();
        }

        // Simpan sesi login (user_id, username, role) ke session
        session()->set([
            'user_id'         => $user['id_perusahaan'],
            'username'        => $user['username'],
            'role'            => 'sales',
            'sales_logged_in' => true,
        ]);

        // Catat login history (Non-blocking)
        try {
            $loginHistoryModel = new \App\Models\LoginHistoryModel();
            $ipAddress = (string) $this->request->getIPAddress();
            $userAgent = (string) $this->request->getUserAgent()->getAgentString();
            $loginHistoryModel->recordLogin((int) $user['id_perusahaan'], $ipAddress, $userAgent);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal mencatat login history: ' . $e->getMessage());
        }

        // Redirect ke halaman dashboard sales
        return redirect()->to(base_url('sales/dashboard'));
    }

    /**
     * Logout Sales
     * Route: GET /sales/logout
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('sales'));
    }
}
