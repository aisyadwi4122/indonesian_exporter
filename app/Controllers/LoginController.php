<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProfilModel;
use CodeIgniter\HTTP\ResponseInterface;

class LoginController extends BaseController
{
    public function index()
    {
        // Pengecekan jika pengguna sudah login
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('admin/dashboard')); // Ubah 'vw_home' sesuai dengan halaman yang diinginkan setelah login
        }

        // Proses login jika pengguna belum login
        return view('admin/login/index');
    }

    public function process()
    {
        $username = trim((string)($this->request->getVar('username') ?? ''));
        $password = (string)($this->request->getVar('password') ?? '');

        // 1. Cek autentikasi dari tabel users (UserModel)
        try {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->where('email', $username)
                ->orWhere('name', $username)
                ->first();

            if ($user && !empty($user['is_active']) && password_verify($password, $user['password'])) {
                $sessionData = [
                    'user_id'   => $user['id'],
                    'name'      => $user['name'],
                    'email'     => $user['email'],
                    'role'      => $user['role'],
                    'logged_in' => true,
                ];

                if ($user['role'] === 'sales') {
                    $sessionData['sales_logged_in'] = true;
                    $sessionData['sales_id']        = $user['id'];
                    $sessionData['sales_name']      = $user['name'];
                    session()->set($sessionData);
                    return redirect()->to(base_url('sales/dashboard'));
                }

                session()->set($sessionData);
                return redirect()->to(base_url('admin/dashboard'));
            }
        } catch (\Throwable $e) {
            // Lanjutkan ke fallback autentikasi tabel tb_profil jika ada kendala
        }

        // 2. Fallback autentikasi legacy dari tabel tb_profil
        $users = new ProfilModel();
        $dataUser = $users->where(['username' => $username, 'role' => 'user'])->first();

        if ($dataUser) {
            $isPasswordValid = ($password === $dataUser['password']) || password_verify($password, $dataUser['password']);

            if ($isPasswordValid) {
                session()->set([
                    'user_id'                 => $dataUser['id_perusahaan'] ?? 1,
                    'username'                => $dataUser['username'],
                    'nama_perusahaan'         => $dataUser['nama_perusahaan'] ?? '',
                    'deskripsi_perusahaan_id' => $dataUser['deskripsi_perusahaan_id'] ?? '',
                    'deskripsi_perusahaan_en' => $dataUser['deskripsi_perusahaan_en'] ?? '',
                    'favicon_perusahaan'      => $dataUser['favicon_perusahaan'] ?? '',
                    'logo_perusahaan'         => $dataUser['logo_perusahaan'] ?? '',
                    'foto_perusahaan'         => $dataUser['foto_perusahaan'] ?? '',
                    'role'                    => 'admin',
                    'logged_in'               => true,
                ]);
                return redirect()->to(base_url('admin/dashboard'));
            }
        }

        session()->setFlashdata('error', 'Username & Password Salah');
        return redirect()->back();
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }
}
