<?php

namespace App\Controllers\admin;

use App\Models\SalesModel;
use App\Models\SupplierModel;
use App\Models\SupplierUserModel;
use Config\Database;

class Akun extends AdminAuthBase
{
    protected $salesModel;
    protected $supplierUserModel;
    protected $supplierModel;

    public function __construct()
    {
        $this->salesModel        = new SalesModel();
        $this->supplierUserModel = new SupplierUserModel();
        $this->supplierModel     = new SupplierModel();
        $this->ensureTipeAksesColumnExists();
    }

    /**
     * Memastikan kolom tipe_akses ada di tabel tb_sales agar tidak terjadi error SQL #1054
     */
    protected function ensureTipeAksesColumnExists(): void
    {
        try {
            $db = Database::connect('default');
            if ($db->tableExists('tb_sales') && !$db->fieldExists('tipe_akses', 'tb_sales')) {
                $forge = \Config\Database::forge();
                $forge->addColumn('tb_sales', [
                    'tipe_akses' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 10,
                        'default'    => 'internal',
                        'null'       => false,
                        'after'      => 'status',
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'ensureTipeAksesColumnExists error: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan daftar gabungan akun Sales dan Supplier
     * Route: GET admin/akun atau admin/akun/index
     */
    public function index()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $db = Database::connect('default');

        // Filter parameter
        $tipeFilter = strtolower(trim((string)($this->request->getGet('tipe') ?? 'all')));
        if (!in_array($tipeFilter, ['all', 'sales', 'supplier'], true)) {
            $tipeFilter = 'all';
        }

        $statusFilter = strtolower(trim((string)($this->request->getGet('status') ?? 'all')));
        if (!in_array($statusFilter, ['all', 'aktif', 'nonaktif'], true)) {
            $statusFilter = 'all';
        }

        $aksesFilter = strtolower(trim((string)($this->request->getGet('akses') ?? 'all')));
        if (!in_array($aksesFilter, ['all', 'internal', 'eksternal'], true)) {
            $aksesFilter = 'all';
        }

        $searchQuery = trim((string)($this->request->getGet('q') ?? ''));

        // 1. Ambil data Sales jika tipeFilter 'all' atau 'sales'
        $salesList = [];
        if ($tipeFilter === 'all' || $tipeFilter === 'sales') {
            $rawSales = $this->salesModel->findAll();
            foreach ($rawSales as $s) {
                $isActive = (isset($s['status']) && $s['status'] === 'aktif') ? 1 : 0;
                $salesList[] = [
                    'id'          => $s['id_sales'],
                    'username'    => $s['username'],
                    'nama'        => $s['nama_sales'] ?? $s['username'],
                    'tipe'        => 'sales',
                    'tipe_label'  => 'Sales',
                    'tipe_akses'  => $s['tipe_akses'] ?? 'internal',
                    'perusahaan'  => '-',
                    'is_active'   => $isActive,
                    'status_text' => $isActive ? 'Aktif' : 'Nonaktif',
                    'created_at'  => $s['created_at'] ?? null,
                ];
            }
        }

        // 2. Ambil data Supplier jika tipeFilter 'all' atau 'supplier'
        $supplierList = [];
        if ($tipeFilter === 'all' || $tipeFilter === 'supplier') {
            $rawSuppliers = $db->table('tb_supplier_user u')
                ->select('u.*, s.nama_perusahaan')
                ->join('tb_supplier s', 's.id_supplier = u.id_supplier', 'left')
                ->get()
                ->getResultArray();

            foreach ($rawSuppliers as $sp) {
                $isActive = ((int)($sp['is_active'] ?? 1) === 1) ? 1 : 0;
                $supplierList[] = [
                    'id'          => $sp['id_supplier_user'],
                    'username'    => $sp['username'],
                    'nama'        => $sp['name'] ?? $sp['username'],
                    'tipe'        => 'supplier',
                    'tipe_label'  => 'Supplier',
                    'tipe_akses'  => null,
                    'perusahaan'  => !empty($sp['nama_perusahaan']) ? $sp['nama_perusahaan'] : '-',
                    'is_active'   => $isActive,
                    'status_text' => $isActive ? 'Aktif' : 'Nonaktif',
                    'created_at'  => $sp['created_at'] ?? null,
                ];
            }
        }

        // 3. Gabungkan data
        $combined = array_merge($salesList, $supplierList);

        // 4. Filter status jika bukan 'all'
        if ($statusFilter !== 'all') {
            $targetActive = ($statusFilter === 'aktif') ? 1 : 0;
            $combined = array_values(array_filter($combined, function ($item) use ($targetActive) {
                return $item['is_active'] === $targetActive;
            }));
        }

        // 5. Filter tipe akses jika bukan 'all' (hanya relevan untuk akun Sales)
        if ($aksesFilter !== 'all') {
            $combined = array_values(array_filter($combined, function ($item) use ($aksesFilter) {
                return ($item['tipe_akses'] ?? '') === $aksesFilter;
            }));
        }

        // 6. Filter pencarian kata kunci (username, nama, perusahaan)
        if ($searchQuery !== '') {
            $qLower = mb_strtolower($searchQuery, 'UTF-8');
            $combined = array_values(array_filter($combined, function ($item) use ($qLower) {
                $u = mb_strtolower((string)$item['username'], 'UTF-8');
                $n = mb_strtolower((string)$item['nama'], 'UTF-8');
                $p = mb_strtolower((string)$item['perusahaan'], 'UTF-8');
                return str_contains($u, $qLower) || str_contains($n, $qLower) || str_contains($p, $qLower);
            }));
        }

        // 7. Urutkan terbaru dulu (created_at DESC, fallback ID DESC)
        usort($combined, function ($a, $b) {
            $tA = !empty($a['created_at']) ? strtotime($a['created_at']) : 0;
            $tB = !empty($b['created_at']) ? strtotime($b['created_at']) : 0;
            if ($tA === $tB) {
                return $b['id'] <=> $a['id'];
            }
            return $tB <=> $tA;
        });

        // 8. Paginasi 10 item per halaman
        $perPage     = 10;
        $totalRows   = count($combined);
        $totalPages  = max(1, (int)ceil($totalRows / $perPage));
        $currentPage = max(1, min((int)($this->request->getGet('page') ?? 1), $totalPages));
        $offset      = ($currentPage - 1) * $perPage;
        $pagedAkun   = array_slice($combined, $offset, $perPage);

        $data = [
            'title'         => 'Manajemen Akun (Sales & Supplier)',
            'akun_list'     => $pagedAkun,
            'total_rows'    => $totalRows,
            'total_pages'   => $totalPages,
            'current_page'  => $currentPage,
            'per_page'      => $perPage,
            'tipe_filter'   => $tipeFilter,
            'status_filter' => $statusFilter,
            'akses_filter'  => $aksesFilter,
            'search_query'  => $searchQuery,
        ];

        return view('admin/akun/index', $data);
    }

    /**
     * Menampilkan form tambah akun
     * Route: GET admin/akun/tambah
     */
    public function tambah()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $suppliers = $this->supplierModel->orderBy('nama_perusahaan', 'ASC')->findAll();

        $defaultTipe = strtolower(trim((string)($this->request->getGet('tipe') ?? 'sales')));
        if (!in_array($defaultTipe, ['sales', 'supplier'], true)) {
            $defaultTipe = 'sales';
        }

        $data = [
            'title'        => 'Tambah Akun Pengguna',
            'is_edit'      => false,
            'default_tipe' => $defaultTipe,
            'suppliers'    => $suppliers,
            'validation'   => \Config\Services::validation(),
        ];

        return view('admin/akun/form', $data);
    }

    /**
     * Memproses penambahan akun baru
     * Route: POST admin/akun/proses_tambah
     */
    public function proses_tambah()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $tipe = strtolower(trim((string)$this->request->getPost('tipe')));
        if (!in_array($tipe, ['sales', 'supplier'], true)) {
            session()->setFlashdata('error', 'Tipe akun tidak valid.');
            return redirect()->back()->withInput();
        }

        // Aturan validasi
        $rules = [
            'nama' => [
                'rules'  => 'required|max_length[100]',
                'errors' => [
                    'required'   => 'Nama wajib diisi.',
                    'max_length' => 'Nama maksimal 100 karakter.',
                ],
            ],
            'username' => [
                'rules'  => 'required|min_length[4]|max_length[50]|regex_match[/^[a-z0-9._-]+$/]',
                'errors' => [
                    'required'    => 'Username wajib diisi.',
                    'min_length'  => 'Username minimal 4 karakter.',
                    'max_length'  => 'Username maksimal 50 karakter.',
                    'regex_match' => 'Username hanya boleh mengandung huruf kecil, angka, titik, garis bawah, dan strip.',
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
                'rules'  => 'required|in_list[0,1]',
                'errors' => [
                    'required' => 'Status akun wajib dipilih.',
                    'in_list'  => 'Status akun tidak valid.',
                ],
            ],
        ];

        if ($tipe === 'supplier') {
            $rules['id_supplier'] = [
                'rules'  => 'required|is_natural_no_zero',
                'errors' => [
                    'required'           => 'Perusahaan Supplier wajib dipilih.',
                    'is_natural_no_zero' => 'Perusahaan Supplier tidak valid.',
                ],
            ];
        } else {
            // Field wajib "Tipe Akses" untuk akun Sales
            $rules['tipe_akses'] = [
                'rules'  => 'required|in_list[internal,eksternal]',
                'errors' => [
                    'required' => 'Tipe Akses wajib dipilih untuk akun Sales.',
                    'in_list'  => 'Tipe Akses harus bernilai Internal atau Eksternal.',
                ],
            ];
        }

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', implode('<br>', $this->validator->getErrors()));
            return redirect()->back()->withInput();
        }

        $username = strtolower(trim((string)$this->request->getPost('username')));
        $nama     = trim((string)$this->request->getPost('nama'));
        $password = (string)$this->request->getPost('password');
        $status   = (int)$this->request->getPost('status');

        // Pemeriksaan keunikan username di KEDUA tabel (tb_sales dan tb_supplier_user)
        $existSales    = $this->salesModel->where('username', $username)->first();
        $existSupplier = $this->supplierUserModel->where('username', $username)->first();

        if ($existSales || $existSupplier) {
            session()->setFlashdata('error', "Username '{$username}' sudah digunakan di sistem. Silakan pilih username lain.");
            return redirect()->back()->withInput();
        }

        $adminUser = session()->get('username') ?? 'admin';

        if ($tipe === 'sales') {
            $this->ensureTipeAksesColumnExists();
            $tipeAkses = strtolower(trim((string)$this->request->getPost('tipe_akses')));
            if ($tipeAkses !== 'internal' && $tipeAkses !== 'eksternal') {
                $tipeAkses = 'internal';
            }

            $dataInsert = [
                'nama_sales' => $nama,
                'username'   => $username,
                'password'   => password_hash($password, PASSWORD_DEFAULT),
                'status'     => ($status === 1 ? 'aktif' : 'nonaktif'),
            ];

            $db = Database::connect('default');
            if ($db->fieldExists('tipe_akses', 'tb_sales')) {
                $dataInsert['tipe_akses'] = $tipeAkses;
            }

            $this->salesModel->insert($dataInsert);

            log_message('info', "Admin '{$adminUser}' berhasil membuat akun sales baru '{$username}' dengan tipe akses '{$tipeAkses}'.");
        } else {
            $idSupplier = (int)$this->request->getPost('id_supplier');
            $supplier = $this->supplierModel->find($idSupplier);
            if (!$supplier) {
                session()->setFlashdata('error', 'Perusahaan Supplier tidak ditemukan di basis data.');
                return redirect()->back()->withInput();
            }

            $this->supplierUserModel->insert([
                'id_supplier' => $idSupplier,
                'username'    => $username,
                'password'    => password_hash($password, PASSWORD_DEFAULT),
                'name'        => $nama,
                'is_active'   => $status,
            ]);

            log_message('info', "Admin '{$adminUser}' berhasil membuat akun supplier baru '{$username}'.");
        }

        $tipeLabel = ($tipe === 'sales') ? 'Sales' : 'Supplier';

        // Flash sukses dan flashdata password hanya sekali
        session()->setFlashdata('success', "Akun {$tipeLabel} dengan username '{$username}' berhasil dibuat.");
        session()->setFlashdata('created_account', [
            'username'   => $username,
            'tipe_label' => $tipeLabel,
            'password'   => $password,
        ]);

        return redirect()->to(base_url('admin/akun/index'));
    }

    /**
     * Menampilkan form edit akun
     * Route: GET admin/akun/edit/{tipe}/{id}
     */
    public function edit($tipe = null, $id = null)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $tipe = strtolower((string)$tipe);
        if (!in_array($tipe, ['sales', 'supplier'], true) || empty($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $account = null;
        if ($tipe === 'sales') {
            $s = $this->salesModel->find($id);
            if (!$s) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
            $account = [
                'id'          => $s['id_sales'],
                'username'    => $s['username'],
                'nama'        => $s['nama_sales'] ?? $s['username'],
                'tipe'        => 'sales',
                'tipe_label'  => 'Sales',
                'tipe_akses'  => $s['tipe_akses'] ?? 'internal',
                'is_active'   => (isset($s['status']) && $s['status'] === 'aktif') ? 1 : 0,
                'id_supplier' => null,
            ];
        } else {
            $sp = $this->supplierUserModel->find($id);
            if (!$sp) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
            $account = [
                'id'          => $sp['id_supplier_user'],
                'username'    => $sp['username'],
                'nama'        => $sp['name'] ?? $sp['username'],
                'tipe'        => 'supplier',
                'tipe_label'  => 'Supplier',
                'tipe_akses'  => null,
                'is_active'   => (int)($sp['is_active'] ?? 1),
                'id_supplier' => $sp['id_supplier'],
            ];
        }

        $suppliers = $this->supplierModel->orderBy('nama_perusahaan', 'ASC')->findAll();

        $data = [
            'title'      => 'Edit Akun Pengguna (' . ($tipe === 'sales' ? 'Sales' : 'Supplier') . ')',
            'is_edit'    => true,
            'account'    => $account,
            'suppliers'  => $suppliers,
            'validation' => \Config\Services::validation(),
        ];

        return view('admin/akun/form', $data);
    }

    /**
     * Memproses update akun
     * Route: POST admin/akun/proses_edit/{tipe}/{id}
     */
    public function proses_edit($tipe = null, $id = null)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $tipe = strtolower((string)$tipe);
        if (!in_array($tipe, ['sales', 'supplier'], true) || empty($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'nama' => [
                'rules'  => 'required|max_length[100]',
                'errors' => [
                    'required'   => 'Nama wajib diisi.',
                    'max_length' => 'Nama maksimal 100 karakter.',
                ],
            ],
            'status' => [
                'rules'  => 'required|in_list[0,1]',
                'errors' => [
                    'required' => 'Status akun wajib dipilih.',
                    'in_list'  => 'Status akun tidak valid.',
                ],
            ],
        ];

        if ($tipe === 'supplier') {
            $rules['id_supplier'] = [
                'rules'  => 'required|is_natural_no_zero',
                'errors' => [
                    'required'           => 'Perusahaan Supplier wajib dipilih.',
                    'is_natural_no_zero' => 'Perusahaan Supplier tidak valid.',
                ],
            ];
        } else {
            $rules['tipe_akses'] = [
                'rules'  => 'required|in_list[internal,eksternal]',
                'errors' => [
                    'required' => 'Tipe Akses wajib dipilih untuk akun Sales.',
                    'in_list'  => 'Tipe Akses harus bernilai Internal atau Eksternal.',
                ],
            ];
        }

        $password = (string)$this->request->getPost('password');
        if (!empty($password)) {
            $rules['password'] = [
                'rules'  => 'min_length[8]',
                'errors' => [
                    'min_length' => 'Password baru minimal 8 karakter.',
                ],
            ];
            $rules['konfirmasi_password'] = [
                'rules'  => 'required|matches[password]',
                'errors' => [
                    'required' => 'Konfirmasi password wajib diisi jika mengubah password.',
                    'matches'  => 'Konfirmasi password tidak cocok dengan password baru.',
                ],
            ];
        }

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', implode('<br>', $this->validator->getErrors()));
            return redirect()->back()->withInput();
        }

        $nama      = trim((string)$this->request->getPost('nama'));
        $status    = (int)$this->request->getPost('status');
        $adminUser = session()->get('username') ?? 'admin';

        if ($tipe === 'sales') {
            $account = $this->salesModel->find($id);
            if (!$account) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            $this->ensureTipeAksesColumnExists();
            $oldTipeAkses = $account['tipe_akses'] ?? 'internal';
            $newTipeAkses = strtolower(trim((string)$this->request->getPost('tipe_akses')));
            if ($newTipeAkses !== 'internal' && $newTipeAkses !== 'eksternal') {
                $newTipeAkses = 'internal';
            }

            $updateData = [
                'nama_sales' => $nama,
                'status'     => ($status === 1 ? 'aktif' : 'nonaktif'),
            ];
            $db = Database::connect('default');
            if ($db->fieldExists('tipe_akses', 'tb_sales')) {
                $updateData['tipe_akses'] = $newTipeAkses;
            }
            if (!empty($password)) {
                $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $this->salesModel->update($id, $updateData);

            if ($oldTipeAkses !== $newTipeAkses) {
                log_message('info', "Admin '{$adminUser}' mengubah tipe akses akun sales '{$account['username']}' dari '{$oldTipeAkses}' menjadi '{$newTipeAkses}'.");
            } else {
                log_message('info', "Admin '{$adminUser}' berhasil memperbarui akun sales '{$account['username']}'.");
            }
            session()->setFlashdata('success', "Akun Sales '{$account['username']}' berhasil diperbarui.");
        } else {
            $account = $this->supplierUserModel->find($id);
            if (!$account) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            $idSupplier = (int)$this->request->getPost('id_supplier');
            $supplier = $this->supplierModel->find($idSupplier);
            if (!$supplier) {
                session()->setFlashdata('error', 'Perusahaan Supplier tidak ditemukan di basis data.');
                return redirect()->back()->withInput();
            }

            $updateData = [
                'name'        => $nama,
                'id_supplier' => $idSupplier,
                'is_active'   => $status,
            ];
            if (!empty($password)) {
                $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $this->supplierUserModel->update($id, $updateData);
            log_message('info', "Admin '{$adminUser}' berhasil memperbarui akun supplier '{$account['username']}'.");
            session()->setFlashdata('success', "Akun Supplier '{$account['username']}' berhasil diperbarui.");
        }

        return redirect()->to(base_url('admin/akun/index'));
    }

    /**
     * Mengaktifkan atau menonaktifkan status akun
     * Route: POST admin/akun/toggle/{tipe}/{id}
     */
    public function toggle($tipe = null, $id = null)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            session()->setFlashdata('error', 'Metode permintaan tidak diizinkan.');
            return redirect()->to(base_url('admin/akun/index'));
        }

        $tipe = strtolower((string)$tipe);
        if (!in_array($tipe, ['sales', 'supplier'], true) || empty($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $adminUser = session()->get('username') ?? 'admin';

        if ($tipe === 'sales') {
            $account = $this->salesModel->find($id);
            if (!$account) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            $currentActive = (isset($account['status']) && $account['status'] === 'aktif');
            $newStatus     = $currentActive ? 'nonaktif' : 'aktif';

            $this->salesModel->update($id, ['status' => $newStatus]);
            log_message('info', "Admin '{$adminUser}' mengubah status akun sales '{$account['username']}' menjadi '{$newStatus}'.");
            session()->setFlashdata('success', "Status akun Sales '{$account['username']}' berhasil diubah menjadi " . ucfirst($newStatus) . ".");
        } else {
            $account = $this->supplierUserModel->find($id);
            if (!$account) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            $currentActive = ((int)$account['is_active'] === 1);
            $newActive     = $currentActive ? 0 : 1;
            $statusLabel   = $newActive === 1 ? 'aktif' : 'nonaktif';

            $this->supplierUserModel->update($id, ['is_active' => $newActive]);
            log_message('info', "Admin '{$adminUser}' mengubah status akun supplier '{$account['username']}' menjadi '{$statusLabel}'.");
            session()->setFlashdata('success', "Status akun Supplier '{$account['username']}' berhasil diubah menjadi " . ucfirst($statusLabel) . ".");
        }

        return redirect()->to(base_url('admin/akun/index'));
    }

    /**
     * Menghapus akun sales atau supplier
     * Route: POST admin/akun/delete/{tipe}/{id}
     */
    public function delete($tipe = null, $id = null)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        // Siapkan redirect URL dengan mempertahankan tab, filter, dan pencarian yang aktif
        $redirectParams = [];
        $tipeParam = (string)$this->request->getPost('tipe');
        if ($tipeParam !== '' && $tipeParam !== 'all') {
            $redirectParams['tipe'] = $tipeParam;
        }
        $statusParam = (string)$this->request->getPost('status');
        if ($statusParam !== '' && $statusParam !== 'all') {
            $redirectParams['status'] = $statusParam;
        }
        $aksesParam = (string)$this->request->getPost('akses');
        if ($aksesParam !== '' && $aksesParam !== 'all') {
            $redirectParams['akses'] = $aksesParam;
        }
        $qParam = trim((string)$this->request->getPost('q'));
        if ($qParam !== '') {
            $redirectParams['q'] = $qParam;
        }
        $pageParam = (int)$this->request->getPost('page');
        if ($pageParam > 1) {
            $redirectParams['page'] = $pageParam;
        }

        $redirectUrl = base_url('admin/akun/index');
        if (!empty($redirectParams)) {
            $redirectUrl .= '?' . http_build_query($redirectParams);
        }

        if (strtolower($this->request->getMethod()) !== 'post') {
            session()->setFlashdata('error', 'Metode permintaan tidak diizinkan.');
            return redirect()->to($redirectUrl);
        }

        $tipe = strtolower((string)$tipe);
        if (!in_array($tipe, ['sales', 'supplier'], true) || empty($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $id = (int)$id;
        $adminUser = session()->get('username') ?? 'admin';

        if ($tipe === 'sales') {
            $account = $this->salesModel->find($id);
            if (!$account) {
                session()->setFlashdata('error', 'Akun Sales tidak ditemukan.');
                return redirect()->to($redirectUrl);
            }

            // Aturan pengaman: Akun yang sedang login tidak boleh menghapus dirinya sendiri
            $loggedUsername = (string)session()->get('username');
            $loggedSalesId  = (int)session()->get('id_sales');
            if (
                ($loggedSalesId > 0 && $loggedSalesId === $id) ||
                ($loggedUsername !== '' && strtolower($loggedUsername) === strtolower((string)$account['username']))
            ) {
                session()->setFlashdata('error', 'Anda tidak dapat menghapus akun yang sedang Anda gunakan untuk login.');
                return redirect()->to($redirectUrl);
            }

            try {
                $this->salesModel->delete($id);
                log_message('info', "Admin '{$adminUser}' berhasil menghapus akun Sales '{$account['username']}' (ID: {$id}).");
                session()->setFlashdata('success', 'Akun berhasil dihapus');
            } catch (\Throwable $e) {
                log_message('error', "Gagal menghapus akun Sales ID {$id}: " . $e->getMessage());
                session()->setFlashdata('error', 'Gagal menghapus akun: ' . $e->getMessage());
            }
        } else {
            $account = $this->supplierUserModel->find($id);
            if (!$account) {
                session()->setFlashdata('error', 'Akun Supplier tidak ditemukan.');
                return redirect()->to($redirectUrl);
            }

            // Aturan pengaman: Akun yang sedang login tidak boleh menghapus dirinya sendiri
            $loggedUsername    = (string)session()->get('username');
            $loggedSupplierId  = (int)session()->get('id_supplier_user');
            if (
                ($loggedSupplierId > 0 && $loggedSupplierId === $id) ||
                ($loggedUsername !== '' && strtolower($loggedUsername) === strtolower((string)$account['username']))
            ) {
                session()->setFlashdata('error', 'Anda tidak dapat menghapus akun yang sedang Anda gunakan untuk login.');
                return redirect()->to($redirectUrl);
            }

            try {
                $this->supplierUserModel->delete($id);
                log_message('info', "Admin '{$adminUser}' berhasil menghapus akun Supplier '{$account['username']}' (ID: {$id}).");
                session()->setFlashdata('success', 'Akun berhasil dihapus');
            } catch (\Throwable $e) {
                log_message('error', "Gagal menghapus akun Supplier ID {$id}: " . $e->getMessage());
                session()->setFlashdata('error', 'Gagal menghapus akun: ' . $e->getMessage());
            }
        }

        return redirect()->to($redirectUrl);
    }
}
