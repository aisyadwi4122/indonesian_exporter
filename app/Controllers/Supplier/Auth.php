<?php

namespace App\Controllers\Supplier;

use App\Controllers\BaseController;
use App\Models\SupplierUserModel;

class Auth extends BaseController
{
    /**
     * Menampilkan formulir login Supplier
     * Route: GET /supplier atau GET /supplier/login
     */
    public function login()
    {
        if (session()->get('supplier_logged_in') === true) {
            return redirect()->to(base_url('supplier/dashboard'));
        }

        $data = [
            'title'            => 'Log in to Indonesian Exporter',
            'action_url'       => base_url('supplier/login'),
            'bg_image'         => 'assets/img/login-bg.jpg',
            'badge_tag'        => 'Indonesian Exporter',
            'caption_title'    => 'Membawa Produk Terbaik Indonesia ke Dunia',
            'caption_subtitle' => 'Portal Manajemen Supplier & Katalog Komoditas Ekspor Nusantara',
        ];

        return view('auth/login', $data);
    }

    /**
     * Memproses autentikasi login Supplier
     * Route: POST /supplier/login
     */
    public function attempt()
    {
        // Throttler: maksimal 5 percobaan per menit per IP (kunci terpisah untuk supplier)
        $ip = $this->request->getIPAddress();
        $throttler = service('throttler');
        $throttleKey = 'supplier_login_' . md5($ip);

        if ($throttler->check($throttleKey, 5, MINUTE) === false) {
            session()->setFlashdata('error', 'Terlalu banyak percobaan. Coba lagi sebentar lagi.');
            return redirect()->back()->withInput();
        }

        // Validasi input: username dan password wajib diisi
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

        $username = trim((string)$this->request->getPost('username'));
        $password = (string)$this->request->getPost('password');

        $userModel = new SupplierUserModel();
        $user = $userModel->where('username', $username)->first();

        // Verifikasi HANYA dengan password_verify(), periksa is_active = 1
        // Semua kegagalan menggunakan pesan yang sama
        if (!$user || (int)$user['is_active'] !== 1 || !password_verify($password, $user['password'])) {
            session()->setFlashdata('error', 'Username atau password salah');
            return redirect()->back()->withInput();
        }

        // Regenerasi session ID untuk keamanan
        session()->regenerate();

        // Set session khusus supplier
        session()->set([
            'supplier_logged_in' => true,
            'supplier_user_id'   => $user['id_supplier_user'],
            'supplier_id'        => $user['id_supplier'] ?? null,
            'supplier_username'  => $user['username'],
            'supplier_name'      => $user['name'],
        ]);

        return redirect()->to(base_url('supplier/dashboard'));
    }

    /**
     * Logout Supplier
     * Route: GET /supplier/logout
     */
    public function logout()
    {
        // Hapus HANYA kunci session supplier_* agar sesi Sales di browser yang sama tidak ikut terhapus
        session()->remove([
            'supplier_logged_in',
            'supplier_user_id',
            'supplier_id',
            'supplier_username',
            'supplier_name',
        ]);

        return redirect()->to(base_url('supplier/login'));
    }
}
