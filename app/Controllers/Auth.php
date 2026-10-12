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
         * Ganti gambar login di sini / Change login background image here
         * ==========================================================================
         * Path default: assets/img/login-bg.jpg
         */
        $bgImage = 'assets/img/login-bg.jpg';

        $data = [
            'title'      => 'Log in to Indonesian Exporter',
            'action_url' => base_url('sales/login'),
            'bg_image'   => $bgImage,
        ];

        return view('auth/login', $data);
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

        // 2. Ambil user dari tabel tb_profil dengan username dan role = 'sales'
        $user = $db->table('tb_profil')
            ->where('username', $username)
            ->where('role', 'sales')
            ->get()
            ->getRowArray();

        // 3. Verifikasi pengguna dan role
        if (!$user) {
            session()->setFlashdata('error', 'Username atau password salah');
            return redirect()->back()->withInput();
        }

        // 4. Verifikasi password menggunakan password_verify() (dengan fallback plain-text jika legacy)
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

        // 5. Simpan sesi login (user_id, username, role) ke session
        session()->set([
            'user_id'         => $user['id_perusahaan'],
            'username'        => $user['username'],
            'role'            => 'sales',
            'sales_logged_in' => true,
        ]);

        // 6. Redirect ke halaman dashboard sales
        return redirect()->to(base_url('sales/dashboard'));
    }

    /**
     * Logout Sales
     * Route: GET /sales/logout
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('sales/login'));
    }
}
