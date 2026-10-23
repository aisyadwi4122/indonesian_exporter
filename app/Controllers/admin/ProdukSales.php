<?php

namespace App\Controllers\admin;

use App\Models\ProdukSalesModel;
use App\Models\SupplierModel;
use App\Models\SupplierProductModel;

/**
 * Controller Admin untuk Manajemen Produk Sales
 * Mengelola data produk katalog sales (tabel `tb_produk_supplier`).
 */
class ProdukSales extends AdminAuthBase
{
    protected ProdukSalesModel $productModel;
    protected SupplierModel $supplierModel;

    public function __construct()
    {
        $this->productModel  = new ProdukSalesModel();
        $this->supplierModel = new SupplierModel();
    }

    /**
     * Menampilkan daftar produk sales dengan pencarian dan pagination
     */
    public function index()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $searchQuery = trim((string)($this->request->getGet('q') ?? ''));
        $page = (int)($this->request->getGet('page') ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        $perPage = 10;

        // Ambil data produk terpadu 127 produk (10 Supplier IE di awal + 117 ECP)
        $allItems  = $this->productModel->getUnifiedProducts($searchQuery);
        $totalRows = count($allItems);

        // Potong data sesuai halaman aktif
        $products = array_slice($allItems, ($page - 1) * $perPage, $perPage);

        // Inisialisasi pager CI4 agar links('default', 'pager_rapi') berjalan normal
        $pager = \Config\Services::pager();
        $pager->store('default', $page, $perPage, $totalRows);

        return view('admin/produk_sales/index', [
            'products'     => $products,
            'pager'        => $pager,
            'search_query' => $searchQuery,
        ]);
    }

    /**
     * Menampilkan form tambah produk sales baru
     */
    public function tambah()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $suppliers     = $this->supplierModel->orderBy('nama_perusahaan', 'ASC')->findAll();
        $suggestedCode = $this->productModel->getNextSuggestedCode();

        return view('admin/produk_sales/tambah', [
            'suppliers'      => $suppliers,
            'suggested_code' => $suggestedCode,
            'validation'     => \Config\Services::validation(),
        ]);
    }

    /**
     * Memproses penyimpanan produk sales baru
     */
    public function proses_tambah()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $rules = [
            'nama_produk'        => 'required|min_length[3]|max_length[255]',
            'id_supplier'        => 'required|is_natural_no_zero',
            'lokasi_barang'      => 'permit_empty|max_length[255]',
            'kapasitas_produksi' => 'permit_empty|max_length[255]',
            'moq'                => 'permit_empty|max_length[255]',
            'komisi'             => 'permit_empty|max_length[255]',
            'sudah_ekspor'       => 'permit_empty|in_list[Sudah,Belum]',
        ];

        // Validasi foto jika ada yang diupload
        $fotoFile = $this->request->getFile('foto_1');
        if ($fotoFile && $fotoFile->getError() !== UPLOAD_ERR_NO_FILE) {
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
            'id_supplier' => [
                'required'           => 'Supplier / Perusahaan wajib dipilih.',
                'is_natural_no_zero' => 'Supplier yang dipilih tidak valid.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            session()->setFlashdata('error', 'Terdapat kesalahan pada input formulir. Silakan periksa kembali.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Upload foto jika valid
        $fotoPath = null;
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/produk_sales';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newName = $fotoFile->getRandomName();
            $fotoFile->move($uploadDir, $newName);
            $fotoPath = 'uploads/produk_sales/' . $newName;
        }

        $dataInsert = [
            'id_supplier'        => (int)$this->request->getPost('id_supplier'),
            'nama_produk'        => trim((string)$this->request->getPost('nama_produk')),
            'lokasi_barang'      => trim((string)$this->request->getPost('lokasi_barang')),
            'spesifikasi'        => trim((string)$this->request->getPost('spesifikasi')),
            'kapasitas_produksi' => trim((string)$this->request->getPost('kapasitas_produksi')),
            'moq'                => trim((string)$this->request->getPost('moq')),
            'harga_exw'          => trim((string)$this->request->getPost('harga_exw')),
            'komisi'             => trim((string)$this->request->getPost('komisi')),
            'sertifikasi'        => trim((string)$this->request->getPost('sertifikasi')),
            'legalitas'          => trim((string)$this->request->getPost('legalitas')),
            'sudah_ekspor'       => $this->request->getPost('sudah_ekspor') ?: 'Belum',
            'foto_1'             => $fotoPath,
            'views'              => 0,
        ];

        $insertId = $this->productModel->insert($dataInsert);
        if (!$insertId) {
            session()->setFlashdata('error', 'Gagal menyimpan produk ke database.');
            return redirect()->back()->withInput();
        }

        $kodeBaru = SupplierProductModel::getKodeProduk((int)$insertId);
        session()->setFlashdata('success', 'Produk sales berhasil ditambahkan dengan kode ' . $kodeBaru . '.');

        return redirect()->to(base_url('admin/produk_sales/index'));
    }

    /**
     * Menampilkan form edit produk sales
     */
    public function edit(int $id)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        // Penanganan untuk produk ECP (ID virtual >= 10000)
        if ($id >= 10000) {
            $idEcp = $id - 10000;
            $db = \Config\Database::connect();
            $ecp = $db->table('tb_ecp_ekspor')->where('id_ecp', $idEcp)->get()->getRowArray();
            if (!$ecp) {
                session()->setFlashdata('error', 'Produk ECP tidak ditemukan atau telah dihapus.');
                return redirect()->to(base_url('admin/produk_sales/index'));
            }

            $namaProduk = !empty($ecp['produk_ekspor']) ? $ecp['produk_ekspor'] : (!empty($ecp['produk_dipasarkan']) ? $ecp['produk_dipasarkan'] : 'Produk Ekspor');
            $lokasi = !empty($ecp['domisili']) ? $ecp['domisili'] : (!empty($ecp['wilayah_ecp']) ? $ecp['wilayah_ecp'] : '');
            $spesifikasi = !empty($ecp['kategori_produk']) ? ('Kategori: ' . $ecp['kategori_produk'] . (!empty($ecp['merk_dagang']) ? ' | Merk: ' . $ecp['merk_dagang'] : '')) : ($ecp['produk_dipasarkan'] ?: '');
            $kapasitas = !empty($ecp['kapasitas_produksi']) ? $ecp['kapasitas_produksi'] : (!empty($ecp['kuantitas_ekspor']) ? 'Ekspor: ' . $ecp['kuantitas_ekspor'] : '');
            $hargaExw = isset($ecp['nilai_ekspor_usd']) ? ('Ekspor: $' . number_format((float)$ecp['nilai_ekspor_usd'], 0, ',', '.') . ' USD') : '';

            $produk = [
                'id_produk_supplier' => $id,
                'id_supplier'        => null,
                'nama_produk'        => $namaProduk,
                'nama_perusahaan'    => $ecp['nama_perusahaan'] ?: '',
                'lokasi_barang'      => $lokasi,
                'spesifikasi'        => $spesifikasi,
                'kapasitas_produksi' => $kapasitas,
                'moq'                => $ecp['kuantitas_ekspor'] ?: '',
                'harga_exw'          => $hargaExw,
                'komisi'             => '',
                'sertifikasi'        => '',
                'legalitas'          => '',
                'sudah_ekspor'       => 'Sudah',
                'foto_1'             => null,
            ];

            $suppliers    = $this->supplierModel->orderBy('nama_perusahaan', 'ASC')->findAll();
            $kodeProduk   = 'ECP-' . str_pad((string)$idEcp, 4, '0', STR_PAD_LEFT);
            $currentPhoto = base_url('assets/img/logo.png');

            return view('admin/produk_sales/edit', [
                'produk'            => $produk,
                'suppliers'         => $suppliers,
                'kode_produk'       => $kodeProduk,
                'current_photo_url' => $currentPhoto,
                'validation'        => \Config\Services::validation(),
            ]);
        }

        $produk = $this->productModel->find($id);
        if (!$produk) {
            session()->setFlashdata('error', 'Produk tidak ditemukan atau telah dihapus.');
            return redirect()->to(base_url('admin/produk_sales/index'));
        }

        $suppliers      = $this->supplierModel->orderBy('nama_perusahaan', 'ASC')->findAll();
        $kodeProduk     = SupplierProductModel::getKodeProduk($id);
        $currentPhoto   = SupplierProductModel::photoUrl($produk, 1);

        return view('admin/produk_sales/edit', [
            'produk'            => $produk,
            'suppliers'         => $suppliers,
            'kode_produk'       => $kodeProduk,
            'current_photo_url' => $currentPhoto,
            'validation'        => \Config\Services::validation(),
        ]);
    }

    /**
     * Memproses pembaruan data produk sales
     */
    public function proses_edit(int $id)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        // Penanganan simpan edit untuk produk ECP (ID virtual >= 10000)
        if ($id >= 10000) {
            $idEcp = $id - 10000;
            $db = \Config\Database::connect();
            $ecp = $db->table('tb_ecp_ekspor')->where('id_ecp', $idEcp)->get()->getRowArray();
            if (!$ecp) {
                session()->setFlashdata('error', 'Produk ECP tidak ditemukan atau telah dihapus.');
                return redirect()->to(base_url('admin/produk_sales/index'));
            }

            $rules = [
                'nama_produk' => 'required|min_length[3]|max_length[255]',
            ];
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

            $namaProduk  = trim((string)$this->request->getPost('nama_produk'));
            $lokasi      = trim((string)$this->request->getPost('lokasi_barang'));
            $spesifikasi = trim((string)$this->request->getPost('spesifikasi'));
            $kapasitas   = trim((string)$this->request->getPost('kapasitas_produksi'));

            $db->table('tb_ecp_ekspor')->where('id_ecp', $idEcp)->update([
                'produk_ekspor'      => $namaProduk,
                'domisili'           => $lokasi,
                'produk_dipasarkan'  => $spesifikasi,
                'kapasitas_produksi' => $kapasitas,
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);

            $kodeProduk = 'ECP-' . str_pad((string)$idEcp, 4, '0', STR_PAD_LEFT);
            session()->setFlashdata('success', 'Data produk sales ' . $kodeProduk . ' berhasil diperbarui.');

            return redirect()->to(base_url('admin/produk_sales/index'));
        }

        $produk = $this->productModel->find($id);
        if (!$produk) {
            session()->setFlashdata('error', 'Produk tidak ditemukan atau telah dihapus.');
            return redirect()->to(base_url('admin/produk_sales/index'));
        }

        $rules = [
            'nama_produk'        => 'required|min_length[3]|max_length[255]',
            'id_supplier'        => 'required|is_natural_no_zero',
            'lokasi_barang'      => 'permit_empty|max_length[255]',
            'kapasitas_produksi' => 'permit_empty|max_length[255]',
            'moq'                => 'permit_empty|max_length[255]',
            'komisi'             => 'permit_empty|max_length[255]',
            'sudah_ekspor'       => 'permit_empty|in_list[Sudah,Belum]',
        ];

        // Validasi file foto baru jika ada yang diupload
        $fotoFile = $this->request->getFile('foto_1');
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
            'id_supplier' => [
                'required'           => 'Supplier / Perusahaan wajib dipilih.',
                'is_natural_no_zero' => 'Supplier yang dipilih tidak valid.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            session()->setFlashdata('error', 'Terdapat kesalahan pada input formulir. Silakan periksa kembali.');
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $fotoPath = $produk['foto_1']; // Pertahankan foto lama secara default

        if ($hasNewPhoto) {
            $uploadDir = FCPATH . 'uploads/produk_sales';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Hapus file fisik lama di uploads/produk_sales jika ada
            if (!empty($produk['foto_1'])) {
                $oldRel = ltrim((string)$produk['foto_1'], '/\\');
                if (is_file(FCPATH . $oldRel)) {
                    @unlink(FCPATH . $oldRel);
                }
                if (is_file(FCPATH . 'uploads/produk_sales/' . $oldRel)) {
                    @unlink(FCPATH . 'uploads/produk_sales/' . $oldRel);
                }
            }

            // Hapus juga file supplier override lama jika ada agar tidak menimpa foto baru
            $oldSupplierPhoto = FCPATH . 'uploads/supplier/' . $id . '_1.jpg';
            if (is_file($oldSupplierPhoto)) {
                @unlink($oldSupplierPhoto);
            }

            // Pindahkan foto baru
            $newName = $fotoFile->getRandomName();
            $fotoFile->move($uploadDir, $newName);
            $fotoPath = 'uploads/produk_sales/' . $newName;
        }

        $dataUpdate = [
            'id_supplier'        => (int)$this->request->getPost('id_supplier'),
            'nama_produk'        => trim((string)$this->request->getPost('nama_produk')),
            'lokasi_barang'      => trim((string)$this->request->getPost('lokasi_barang')),
            'spesifikasi'        => trim((string)$this->request->getPost('spesifikasi')),
            'kapasitas_produksi' => trim((string)$this->request->getPost('kapasitas_produksi')),
            'moq'                => trim((string)$this->request->getPost('moq')),
            'harga_exw'          => trim((string)$this->request->getPost('harga_exw')),
            'komisi'             => trim((string)$this->request->getPost('komisi')),
            'sertifikasi'        => trim((string)$this->request->getPost('sertifikasi')),
            'legalitas'          => trim((string)$this->request->getPost('legalitas')),
            'sudah_ekspor'       => $this->request->getPost('sudah_ekspor') ?: 'Belum',
            'foto_1'             => $fotoPath,
        ];

        $this->productModel->update($id, $dataUpdate);

        $kodeProduk = SupplierProductModel::getKodeProduk($id);
        session()->setFlashdata('success', 'Data produk sales ' . $kodeProduk . ' berhasil diperbarui.');

        return redirect()->to(base_url('admin/produk_sales/index'));
    }

    /**
     * Menghapus produk sales dan file foto terkait (hanya via POST)
     */
    public function delete(int $id)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        // Penanganan hapus untuk produk ECP (ID virtual >= 10000)
        if ($id >= 10000) {
            $idEcp = $id - 10000;
            $db = \Config\Database::connect();
            $ecp = $db->table('tb_ecp_ekspor')->where('id_ecp', $idEcp)->get()->getRowArray();
            if (!$ecp) {
                session()->setFlashdata('error', 'Produk ECP tidak ditemukan atau telah dihapus.');
                return redirect()->to(base_url('admin/produk_sales/index'));
            }

            $db->table('tb_ecp_ekspor')->where('id_ecp', $idEcp)->delete();
            $kode = 'ECP-' . str_pad((string)$idEcp, 4, '0', STR_PAD_LEFT);
            $nama = !empty($ecp['produk_ekspor']) ? $ecp['produk_ekspor'] : (!empty($ecp['produk_dipasarkan']) ? $ecp['produk_dipasarkan'] : 'Produk Ekspor');
            session()->setFlashdata('success', 'Produk sales ' . $kode . ' (' . esc($nama) . ') berhasil dihapus.');

            return redirect()->to(base_url('admin/produk_sales/index'));
        }

        $produk = $this->productModel->find($id);
        if (!$produk) {
            session()->setFlashdata('error', 'Produk tidak ditemukan atau telah dihapus.');
            return redirect()->to(base_url('admin/produk_sales/index'));
        }

        // Hapus file fisik foto jika tersimpan lokal
        if (!empty($produk['foto_1'])) {
            $rel = ltrim((string)$produk['foto_1'], '/\\');
            if (is_file(FCPATH . $rel)) {
                @unlink(FCPATH . $rel);
            }
            if (is_file(FCPATH . 'uploads/produk_sales/' . $rel)) {
                @unlink(FCPATH . 'uploads/produk_sales/' . $rel);
            }
        }

        // Hapus file di uploads/supplier/{id}_1.jpg jika ada
        $supplierPhoto = FCPATH . 'uploads/supplier/' . $id . '_1.jpg';
        if (is_file($supplierPhoto)) {
            @unlink($supplierPhoto);
        }

        // Hapus record dari database
        $this->productModel->delete($id);

        $kode = SupplierProductModel::getKodeProduk($id);
        session()->setFlashdata('success', 'Produk sales ' . $kode . ' (' . esc($produk['nama_produk']) . ') berhasil dihapus.');

        return redirect()->to(base_url('admin/produk_sales/index'));
    }
}
