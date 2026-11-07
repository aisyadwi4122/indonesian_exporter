<?php

namespace App\Controllers\Supplier;

use App\Models\SupplierModel;
use App\Models\SupplierProductModel;

/**
 * Controller Area Supplier untuk Manajemen Produk Mandiri
 * Supplier hanya dapat melihat, menambah, mengubah, dan menghapus produk miliknya sendiri.
 */
class Produk extends SupplierBase
{
    protected SupplierProductModel $supplierProductModel;
    protected SupplierModel $supplierModel;

    public function __construct()
    {
        $this->supplierProductModel = new SupplierProductModel();
        $this->supplierModel        = new SupplierModel();
    }

    /**
     * Memeriksa autentikasi login supplier dan kelengkapan profil perusahaan
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    private function checkSupplierAuth()
    {
        if (session()->get('supplier_logged_in') !== true) {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu');
            return redirect()->to(base_url('supplier/login'));
        }

        if ($redirect = $this->requireCompany()) {
            return $redirect;
        }

        return null;
    }

    /**
     * Menampilkan daftar produk MILIK SUPPLIER ITU SAJA dengan pencarian dan pagination
     * Route: GET /supplier/produk atau GET /supplier/produk/index
     */
    public function index()
    {
        if ($redirect = $this->checkSupplierAuth()) {
            return $redirect;
        }

        $supplierId  = (int)session()->get('supplier_id');
        $searchQuery = trim((string)($this->request->getGet('q') ?? ''));

        // Query produk hanya milik supplier ini
        $products = $this->supplierProductModel
            ->getSupplierProducts($supplierId, $searchQuery)
            ->paginate(10);

        $pager = $this->supplierProductModel->pager;

        $totalProducts = $this->supplierProductModel
            ->where('id_supplier', $supplierId)
            ->countAllResults();

        $data = [
            'title'          => 'Katalog Produk - Area Supplier',
            'page_title'     => 'Produk Perusahaan',
            'activeMenu'     => 'produk',
            'products'       => $products,
            'pager'          => $pager,
            'search_query'   => $searchQuery,
            'total_products' => $totalProducts,
        ];

        return view('supplier/produk/index', $data);
    }

    /**
     * Menampilkan formulir tambah produk baru
     * Route: GET /supplier/produk/tambah
     */
    public function tambah()
    {
        if ($redirect = $this->checkSupplierAuth()) {
            return $redirect;
        }

        $supplierId = (int)session()->get('supplier_id');
        $supplier   = $this->supplierModel->find($supplierId);

        $data = [
            'title'          => 'Tambah Produk - Area Supplier',
            'page_title'     => 'Tambah Produk Baru',
            'activeMenu'     => 'produk',
            'domisili_usaha' => $supplier['domisili_usaha'] ?? '',
            'validation'     => \Config\Services::validation(),
        ];

        return view('supplier/produk/tambah', $data);
    }

    /**
     * Memproses penyimpanan produk baru
     * Route: POST /supplier/produk/proses_tambah
     */
    public function proses_tambah()
    {
        if ($redirect = $this->checkSupplierAuth()) {
            return $redirect;
        }

        // Keamanan: id_supplier SELALU dari session yang login
        $supplierId = (int)session()->get('supplier_id');

        $rules = [
            'nama_produk'        => 'required|min_length[3]|max_length[255]',
            'lokasi_barang'      => 'permit_empty|max_length[255]',
            'kapasitas_produksi' => 'permit_empty|max_length[255]',
            'moq'                => 'permit_empty|max_length[255]',
            'harga_exw'          => 'permit_empty|max_length[255]',
            'legalitas'          => 'permit_empty|max_length[255]',
            'sertifikasi'        => 'permit_empty|max_length[255]',
            'sudah_ekspor'       => 'permit_empty|in_list[Sudah,Belum]',
        ];

        // Validasi foto jika ada file yang diunggah
        $fotoFile = $this->request->getFile('foto_1');
        $hasFoto  = ($fotoFile && $fotoFile->getError() !== UPLOAD_ERR_NO_FILE);

        if ($hasFoto) {
            $rules['foto_1'] = [
                'rules'  => 'is_image[foto_1]|mime_in[foto_1,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto_1,2048]',
                'errors' => [
                    'is_image' => 'File yang diunggah harus berupa gambar asli.',
                    'mime_in'  => 'Format gambar harus bertipe JPG, JPEG, PNG, atau WEBP.',
                    'max_size' => 'Ukuran gambar maksimal adalah 2 MB.',
                ],
            ];
        }

        $messages = [
            'nama_produk' => [
                'required'   => 'Nama produk wajib diisi.',
                'min_length' => 'Nama produk minimal 3 karakter.',
                'max_length' => 'Nama produk maksimal 255 karakter.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            session()->setFlashdata('error', 'Terdapat kesalahan pada input formulir. Silakan periksa kembali.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Validasi keunikan nama produk untuk supplier yang sama
        $namaProduk = trim((string)$this->request->getPost('nama_produk'));
        $existing = $this->supplierProductModel
            ->where('id_supplier', $supplierId)
            ->where('LOWER(TRIM(nama_produk))', strtolower($namaProduk))
            ->first();

        if ($existing) {
            session()->setFlashdata('error', 'Produk dengan nama "' . $namaProduk . '" sudah ada di katalog Anda. Silakan gunakan nama produk yang berbeda.');
            return redirect()->back()->withInput();
        }

        // Lokasi barang: jika kosong, isi dengan domisili supplier
        $lokasiBarang = trim((string)$this->request->getPost('lokasi_barang'));
        if ($lokasiBarang === '') {
            $supplier = $this->supplierModel->find($supplierId);
            $lokasiBarang = !empty($supplier['domisili_usaha']) ? trim((string)$supplier['domisili_usaha']) : null;
        }

        // Simpan foto jika diunggah
        $fotoPath = null;
        if ($hasFoto && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/produk_sales';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newName = $fotoFile->getRandomName();
            $fotoFile->move($uploadDir, $newName);
            $fotoPath = 'uploads/produk_sales/' . $newName;
        }

        $dataInsert = [
            'id_supplier'        => $supplierId, // STRICTLY DARI SESSION
            'nama_produk'        => $namaProduk,
            'lokasi_barang'      => $lokasiBarang,
            'spesifikasi'        => trim((string)$this->request->getPost('spesifikasi')),
            'kapasitas_produksi' => trim((string)$this->request->getPost('kapasitas_produksi')),
            'moq'                => trim((string)$this->request->getPost('moq')),
            'harga_exw'          => trim((string)$this->request->getPost('harga_exw')),
            'legalitas'          => trim((string)$this->request->getPost('legalitas')),
            'sertifikasi'        => trim((string)$this->request->getPost('sertifikasi')),
            'sudah_ekspor'       => $this->request->getPost('sudah_ekspor') ?: 'Belum',
            'foto_1'             => $fotoPath,
            'views'              => 0,
            // Kolom komisi sengaja TIDAK dimasukkan agar supplier tidak dapat mengisi komisi internal
        ];

        $insertId = $this->supplierProductModel->insert($dataInsert);
        if (!$insertId) {
            session()->setFlashdata('error', 'Gagal menyimpan produk ke database.');
            return redirect()->back()->withInput();
        }

        $kodeBaru = SupplierProductModel::getKodeProduk((int)$insertId);
        session()->setFlashdata('success', 'Produk berhasil ditambahkan dengan kode ' . $kodeBaru . '.');

        return redirect()->to(base_url('supplier/produk'));
    }

    /**
     * Menampilkan formulir ubah data produk milik supplier
     * Route: GET /supplier/produk/edit/(:num)
     */
    public function edit(int $id)
    {
        if ($redirect = $this->checkSupplierAuth()) {
            return $redirect;
        }

        $supplierId = (int)session()->get('supplier_id');

        // KEAMANAN WAJIB: Pastikan produk milik supplier yang sedang login
        $produk = $this->supplierProductModel
            ->where('id_produk_supplier', $id)
            ->where('id_supplier', $supplierId)
            ->first();

        if (!$produk) {
            session()->setFlashdata('error', 'Produk tidak ditemukan atau Anda tidak memiliki akses.');
            return redirect()->to(base_url('supplier/produk'));
        }

        $supplier        = $this->supplierModel->find($supplierId);
        $kodeProduk      = SupplierProductModel::getKodeProduk($id);
        $currentPhotoUrl = SupplierProductModel::photoUrl($produk, 1);

        $data = [
            'title'             => 'Ubah Produk - Area Supplier',
            'page_title'        => 'Ubah Data Produk',
            'activeMenu'        => 'produk',
            'produk'            => $produk,
            'kode_produk'       => $kodeProduk,
            'current_photo_url' => $currentPhotoUrl,
            'domisili_usaha'    => $supplier['domisili_usaha'] ?? '',
            'validation'        => \Config\Services::validation(),
        ];

        return view('supplier/produk/edit', $data);
    }

    /**
     * Memproses pembaruan data produk milik supplier
     * Route: POST /supplier/produk/proses_edit/(:num)
     */
    public function proses_edit(int $id)
    {
        if ($redirect = $this->checkSupplierAuth()) {
            return $redirect;
        }

        $supplierId = (int)session()->get('supplier_id');

        // KEAMANAN WAJIB: Pastikan produk milik supplier yang sedang login
        $produk = $this->supplierProductModel
            ->where('id_produk_supplier', $id)
            ->where('id_supplier', $supplierId)
            ->first();

        if (!$produk) {
            session()->setFlashdata('error', 'Produk tidak ditemukan atau Anda tidak memiliki akses.');
            return redirect()->to(base_url('supplier/produk'));
        }

        $rules = [
            'nama_produk'        => 'required|min_length[3]|max_length[255]',
            'lokasi_barang'      => 'permit_empty|max_length[255]',
            'kapasitas_produksi' => 'permit_empty|max_length[255]',
            'moq'                => 'permit_empty|max_length[255]',
            'harga_exw'          => 'permit_empty|max_length[255]',
            'legalitas'          => 'permit_empty|max_length[255]',
            'sertifikasi'        => 'permit_empty|max_length[255]',
            'sudah_ekspor'       => 'permit_empty|in_list[Sudah,Belum]',
        ];

        // Validasi foto baru jika diunggah
        $fotoFile    = $this->request->getFile('foto_1');
        $hasNewPhoto = ($fotoFile && $fotoFile->getError() !== UPLOAD_ERR_NO_FILE);

        if ($hasNewPhoto) {
            $rules['foto_1'] = [
                'rules'  => 'is_image[foto_1]|mime_in[foto_1,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto_1,2048]',
                'errors' => [
                    'is_image' => 'File yang diunggah harus berupa gambar asli.',
                    'mime_in'  => 'Format gambar harus bertipe JPG, JPEG, PNG, atau WEBP.',
                    'max_size' => 'Ukuran gambar maksimal adalah 2 MB.',
                ],
            ];
        }

        $messages = [
            'nama_produk' => [
                'required'   => 'Nama produk wajib diisi.',
                'min_length' => 'Nama produk minimal 3 karakter.',
                'max_length' => 'Nama produk maksimal 255 karakter.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            session()->setFlashdata('error', 'Terdapat kesalahan pada input formulir. Silakan periksa kembali.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Validasi keunikan nama produk untuk supplier yang sama (selain produk ini)
        $namaProduk = trim((string)$this->request->getPost('nama_produk'));
        $existing = $this->supplierProductModel
            ->where('id_supplier', $supplierId)
            ->where('LOWER(TRIM(nama_produk))', strtolower($namaProduk))
            ->where('id_produk_supplier !=', $id)
            ->first();

        if ($existing) {
            session()->setFlashdata('error', 'Produk dengan nama "' . $namaProduk . '" sudah ada di katalog Anda. Silakan gunakan nama produk yang berbeda.');
            return redirect()->back()->withInput();
        }

        // Lokasi barang: jika kosong, isi dengan domisili supplier
        $lokasiBarang = trim((string)$this->request->getPost('lokasi_barang'));
        if ($lokasiBarang === '') {
            $supplier = $this->supplierModel->find($supplierId);
            $lokasiBarang = !empty($supplier['domisili_usaha']) ? trim((string)$supplier['domisili_usaha']) : null;
        }

        $fotoPath = $produk['foto_1']; // Pertahankan foto lama secara default

        if ($hasNewPhoto && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/produk_sales';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Hapus file fisik foto lama HANYA jika berada di uploads/produk_sales
            if (!empty($produk['foto_1'])) {
                $oldRel = ltrim((string)$produk['foto_1'], '/\\');
                $targetOld = FCPATH . $oldRel;
                $uploadReal = realpath($uploadDir);
                $oldReal = realpath($targetOld);
                if ($oldReal && $uploadReal && str_starts_with($oldReal, $uploadReal) && is_file($oldReal)) {
                    @unlink($oldReal);
                }
            }

            // Hapus override foto supplier lama jika ada
            $oldSupplierPhoto = FCPATH . 'uploads/supplier/' . $id . '_1.jpg';
            if (is_file($oldSupplierPhoto)) {
                @unlink($oldSupplierPhoto);
            }

            $newName = $fotoFile->getRandomName();
            $fotoFile->move($uploadDir, $newName);
            $fotoPath = 'uploads/produk_sales/' . $newName;
        }

        $dataUpdate = [
            'nama_produk'        => $namaProduk,
            'lokasi_barang'      => $lokasiBarang,
            'spesifikasi'        => trim((string)$this->request->getPost('spesifikasi')),
            'kapasitas_produksi' => trim((string)$this->request->getPost('kapasitas_produksi')),
            'moq'                => trim((string)$this->request->getPost('moq')),
            'harga_exw'          => trim((string)$this->request->getPost('harga_exw')),
            'legalitas'          => trim((string)$this->request->getPost('legalitas')),
            'sertifikasi'        => trim((string)$this->request->getPost('sertifikasi')),
            'sudah_ekspor'       => $this->request->getPost('sudah_ekspor') ?: 'Belum',
            'foto_1'             => $fotoPath,
            // Kolom komisi TIDAK diubah
        ];

        $this->supplierProductModel->update($id, $dataUpdate);

        $kodeProduk = SupplierProductModel::getKodeProduk($id);
        session()->setFlashdata('success', 'Data produk ' . $kodeProduk . ' berhasil diperbarui.');

        return redirect()->to(base_url('supplier/produk'));
    }

    /**
     * Menghapus produk dan file foto terkait (hanya via POST dengan CSRF)
     * Route: POST /supplier/produk/delete/(:num)
     */
    public function delete(int $id)
    {
        if ($redirect = $this->checkSupplierAuth()) {
            return $redirect;
        }

        $supplierId = (int)session()->get('supplier_id');

        // KEAMANAN WAJIB: Pastikan produk milik supplier yang sedang login
        $produk = $this->supplierProductModel
            ->where('id_produk_supplier', $id)
            ->where('id_supplier', $supplierId)
            ->first();

        if (!$produk) {
            session()->setFlashdata('error', 'Produk tidak ditemukan atau Anda tidak memiliki akses.');
            return redirect()->to(base_url('supplier/produk'));
        }

        // Hapus file fisik foto jika tersimpan di uploads/produk_sales
        if (!empty($produk['foto_1'])) {
            $oldRel = ltrim((string)$produk['foto_1'], '/\\');
            $uploadDir = FCPATH . 'uploads/produk_sales';
            $targetOld = FCPATH . $oldRel;
            $uploadReal = realpath($uploadDir);
            $oldReal = realpath($targetOld);
            if ($oldReal && $uploadReal && str_starts_with($oldReal, $uploadReal) && is_file($oldReal)) {
                @unlink($oldReal);
            }
        }

        // Hapus override foto supplier lama jika ada
        $oldSupplierPhoto = FCPATH . 'uploads/supplier/' . $id . '_1.jpg';
        if (is_file($oldSupplierPhoto)) {
            @unlink($oldSupplierPhoto);
        }

        // Hapus record produk
        $this->supplierProductModel->delete($id);

        $kode = SupplierProductModel::getKodeProduk($id);
        session()->setFlashdata('success', 'Produk ' . $kode . ' (' . esc($produk['nama_produk']) . ') berhasil dihapus.');

        return redirect()->to(base_url('supplier/produk'));
    }
}
