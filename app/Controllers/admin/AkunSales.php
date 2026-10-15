<?php

namespace App\Controllers\admin;

use App\Models\SalesModel;

class AkunSales extends AdminAuthBase
{
    protected $salesModel;

    public function __construct()
    {
        $this->salesModel = new SalesModel();
    }

    /**
     * Menampilkan daftar akun sales
     * Route: GET admin/akun_sales/index
     */
    public function index()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $data = [
            'title'      => 'Manajemen Akun Sales',
            'akun_sales' => $this->salesModel->orderBy('created_at', 'DESC')->findAll(),
        ];

        return view('admin/akun_sales/index', $data);
    }

    /**
     * Menampilkan form tambah akun sales
     * Route: GET admin/akun_sales/tambah
     */
    public function tambah()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $data = [
            'title'      => 'Tambah Akun Sales',
            'validation' => \Config\Services::validation(),
        ];

        return view('admin/akun_sales/tambah', $data);
    }

    /**
     * Memproses tambah akun sales
     * Route: POST admin/akun_sales/proses_tambah
     */
    public function proses_tambah()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $rules = [
            'nama_sales' => [
                'rules'  => 'required|max_length[100]',
                'errors' => [
                    'required'   => 'Nama wajib diisi.',
                    'max_length' => 'Nama maksimal 100 karakter.',
                ],
            ],
            'username' => [
                'rules'  => 'required|regex_match[/^[a-zA-Z0-9._-]+$/]|min_length[3]|max_length[100]|is_unique[tb_sales.username]',
                'errors' => [
                    'required'    => 'Username wajib diisi.',
                    'regex_match' => 'Username tidak boleh mengandung spasi dan hanya boleh huruf, angka, titik, garis bawah, atau strip.',
                    'min_length'  => 'Username minimal 3 karakter.',
                    'max_length'  => 'Username maksimal 100 karakter.',
                    'is_unique'   => 'Username sudah terdaftar. Silakan gunakan username lain.',
                ],
            ],
            'password' => [
                'rules'  => 'required|min_length[8]',
                'errors' => [
                    'required'   => 'Password wajib diisi.',
                    'min_length' => 'Password minimal 8 karakter.',
                ],
            ],
            'konfirmasi_password' => [
                'rules'  => 'required|matches[password]',
                'errors' => [
                    'required' => 'Konfirmasi password wajib diisi.',
                    'matches'  => 'Konfirmasi password tidak cocok dengan password.',
                ],
            ],
            'status' => [
                'rules'  => 'required|in_list[aktif,nonaktif]',
                'errors' => [
                    'required' => 'Status akun wajib dipilih.',
                    'in_list'  => 'Status akun harus Aktif atau Nonaktif.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', implode('<br>', $this->validator->getErrors()));
            return redirect()->back()->withInput();
        }

        $saveData = [
            'nama_sales' => trim((string)$this->request->getPost('nama_sales')),
            'username'   => trim((string)$this->request->getPost('username')),
            'password'   => password_hash((string)$this->request->getPost('password'), PASSWORD_DEFAULT),
            'status'     => $this->request->getPost('status'),
        ];

        $this->salesModel->insert($saveData);

        session()->setFlashdata('success', 'Akun Sales berhasil ditambahkan.');
        return redirect()->to(base_url('admin/akun_sales/index'));
    }

    /**
     * Menampilkan form edit akun sales
     * Route: GET admin/akun_sales/edit/(:num)
     */
    public function edit($id = null)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $akun = $this->salesModel->find($id);
        if (!$akun) {
            session()->setFlashdata('error', 'Akun Sales tidak ditemukan.');
            return redirect()->to(base_url('admin/akun_sales/index'));
        }

        $data = [
            'title'      => 'Edit Akun Sales',
            'akun'       => $akun,
            'validation' => \Config\Services::validation(),
        ];

        return view('admin/akun_sales/edit', $data);
    }

    /**
     * Memproses edit akun sales
     * Route: POST admin/akun_sales/proses_edit/(:num)
     */
    public function proses_edit($id = null)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $akun = $this->salesModel->find($id);
        if (!$akun) {
            session()->setFlashdata('error', 'Akun Sales tidak ditemukan.');
            return redirect()->to(base_url('admin/akun_sales/index'));
        }

        $rules = [
            'nama_sales' => [
                'rules'  => 'required|max_length[100]',
                'errors' => [
                    'required'   => 'Nama wajib diisi.',
                    'max_length' => 'Nama maksimal 100 karakter.',
                ],
            ],
            'username' => [
                'rules'  => "required|regex_match[/^[a-zA-Z0-9._-]+$/]|min_length[3]|max_length[100]|is_unique[tb_sales.username,id_sales,{$id}]",
                'errors' => [
                    'required'    => 'Username wajib diisi.',
                    'regex_match' => 'Username tidak boleh mengandung spasi dan hanya boleh huruf, angka, titik, garis bawah, atau strip.',
                    'min_length'  => 'Username minimal 3 karakter.',
                    'max_length'  => 'Username maksimal 100 karakter.',
                    'is_unique'   => 'Username sudah terdaftar. Silakan gunakan username lain.',
                ],
            ],
            'status' => [
                'rules'  => 'required|in_list[aktif,nonaktif]',
                'errors' => [
                    'required' => 'Status akun wajib dipilih.',
                    'in_list'  => 'Status akun harus Aktif atau Nonaktif.',
                ],
            ],
        ];

        $password = (string)$this->request->getPost('password');
        if (!empty($password)) {
            $rules['password'] = [
                'rules'  => 'min_length[8]',
                'errors' => [
                    'min_length' => 'Password minimal 8 karakter.',
                ],
            ];
            $rules['konfirmasi_password'] = [
                'rules'  => 'required|matches[password]',
                'errors' => [
                    'required' => 'Konfirmasi password wajib diisi jika mengubah password.',
                    'matches'  => 'Konfirmasi password tidak cocok dengan password.',
                ],
            ];
        }

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', implode('<br>', $this->validator->getErrors()));
            return redirect()->back()->withInput();
        }

        $updateData = [
            'nama_sales' => trim((string)$this->request->getPost('nama_sales')),
            'username'   => trim((string)$this->request->getPost('username')),
            'status'     => $this->request->getPost('status'),
        ];

        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->salesModel->update($id, $updateData);

        session()->setFlashdata('success', 'Akun Sales berhasil diperbarui.');
        return redirect()->to(base_url('admin/akun_sales/index'));
    }

    /**
     * Menghapus akun sales (Hanya via POST)
     * Route: POST admin/akun_sales/delete/(:num)
     */
    public function delete($id = null)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            session()->setFlashdata('error', 'Metode penghapusan tidak diizinkan.');
            return redirect()->to(base_url('admin/akun_sales/index'));
        }

        $akun = $this->salesModel->find($id);
        if (!$akun) {
            session()->setFlashdata('error', 'Akun Sales tidak ditemukan.');
            return redirect()->to(base_url('admin/akun_sales/index'));
        }

        $this->salesModel->delete($id);

        session()->setFlashdata('success', 'Akun Sales berhasil dihapus.');
        return redirect()->to(base_url('admin/akun_sales/index'));
    }
}
