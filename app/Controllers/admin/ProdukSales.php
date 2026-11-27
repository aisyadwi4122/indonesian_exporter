<?php

namespace App\Controllers\admin;

use App\Libraries\LocationHelper;
use App\Models\ProdukSalesModel;
use App\Models\SupplierExportHistoryModel;
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
    protected SupplierExportHistoryModel $historyModel;

    public function __construct()
    {
        $this->productModel  = new ProdukSalesModel();
        $this->supplierModel = new SupplierModel();
        $this->historyModel  = new SupplierExportHistoryModel();
    }

    /**
     * Menampilkan daftar produk sales dengan pencarian dan pagination
     */
    public function index()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $searchQuery  = trim((string)($this->request->getGet('q') ?? ''));
        $supplierId   = (int)($this->request->getGet('supplier_id') ?? 0);
        $daerahFilter = trim((string)($this->request->getGet('daerah') ?? ''));
        if (mb_strlen($daerahFilter) > 200) {
            $daerahFilter = mb_substr($daerahFilter, 0, 200);
        }
        $page = (int)($this->request->getGet('page') ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        $perPage = 10;

        // Ambil data produk terpadu (Supplier IE + ECP)
        $allItems = $this->productModel->getUnifiedProducts('');

        // Filter pencarian kata kunci (mendukung nilai asli DB maupun lokasi yang sudah diformat tampilan)
        if ($searchQuery !== '') {
            $qLower = mb_strtolower($searchQuery, 'UTF-8');
            $allItems = array_values(array_filter($allItems, function ($item) use ($qLower) {
                $rawLok = (string)($item['lokasi_barang'] ?? '');
                $rawDom = (string)($item['domisili_usaha'] ?? '');
                $fmtLok = LocationHelper::formatDisplayLocation($rawLok);
                $fmtDom = LocationHelper::formatDisplayLocation($rawDom);

                $haystack = mb_strtolower(
                    ($item['kode_produk'] ?? '') . ' ' .
                    ($item['nama_produk'] ?? '') . ' ' .
                    ($item['nama_perusahaan'] ?? '') . ' ' .
                    $rawLok . ' ' . $rawDom . ' ' .
                    $fmtLok . ' ' . $fmtDom . ' ' .
                    ($item['spesifikasi'] ?? ''),
                    'UTF-8'
                );

                return strpos($haystack, $qLower) !== false;
            }));
        }

        // Filter berdasarkan supplier jika dipilih
        if ($supplierId > 0) {
            $allItems = array_values(array_filter($allItems, function ($item) use ($supplierId) {
                return isset($item['id_supplier']) && (int)$item['id_supplier'] === $supplierId;
            }));
        }

        // Filter berdasarkan daerah (provinsi atau kota/lokasi spesifik)
        $isProvinceFilter = false;
        $selectedDaerahLabel = '';
        if ($daerahFilter !== '') {
            $resolvedProvince = LocationHelper::resolveExactProvince($daerahFilter);
            $isProvinceFilter = ($resolvedProvince !== null);
            $selectedDaerahLabel = LocationHelper::formatDisplayLocation($daerahFilter);

            $allItems = array_values(array_filter($allItems, function ($item) use ($daerahFilter) {
                return LocationHelper::matchesRegion($item, $daerahFilter);
            }));
        }

        $totalRows = count($allItems);

        // Potong data sesuai halaman aktif
        $products = array_slice($allItems, ($page - 1) * $perPage, $perPage);

        // Inisialisasi pager CI4 agar links('default', 'pager_rapi') berjalan normal
        $pager = \Config\Services::pager();
        $pager->store('default', $page, $perPage, $totalRows);

        // Ambil daftar supplier untuk dropdown filter
        $suppliers = $this->supplierModel->orderBy('nama_perusahaan', 'ASC')->findAll();

        // Hitung jumlah produk per supplier di tb_produk_supplier
        $db = \Config\Database::connect();
        $countRows = $db->table('tb_produk_supplier')
            ->select('id_supplier, COUNT(*) as total')
            ->groupBy('id_supplier')
            ->get()
            ->getResultArray();
        $productCounts = [];
        foreach ($countRows as $cr) {
            $productCounts[(int)$cr['id_supplier']] = (int)$cr['total'];
        }

        return view('admin/produk_sales/index', [
            'products'              => $products,
            'pager'                 => $pager,
            'search_query'          => $searchQuery,
            'selected_supplier_id'  => $supplierId,
            'selected_daerah'       => $daerahFilter,
            'selected_daerah_label' => $selectedDaerahLabel,
            'is_province_filter'    => $isProvinceFilter,
            'total_rows'            => $totalRows,
            'suppliers'             => $suppliers,
            'product_counts'        => $productCounts,
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

        $suppliers          = $this->supplierModel->orderBy('nama_perusahaan', 'ASC')->findAll();
        $suggestedCode      = $this->productModel->getNextSuggestedCode();
        $selectedSupplierId = (int)($this->request->getGet('id_supplier') ?? 0);

        return view('admin/produk_sales/tambah', [
            'suppliers'            => $suppliers,
            'suggested_code'       => $suggestedCode,
            'selected_supplier_id' => $selectedSupplierId,
            'validation'           => \Config\Services::validation(),
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
            'deskripsi'          => 'permit_empty',
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

        $productValid = $this->validate($rules, $messages);
        $productErrors = $productValid ? [] : $this->validator->getErrors();

        // Validasi keberadaan supplier di database
        $supplierId = (int)$this->request->getPost('id_supplier');
        if ($supplierId > 0) {
            $supplier = $this->supplierModel->find($supplierId);
            if (!$supplier) {
                $productErrors['id_supplier'] = 'Supplier yang dipilih tidak terdaftar di database.';
            }
        }

        // Proses dan validasi baris history ekspor (opsional)
        $rawHistory = $this->request->getPost('history');
        $validHistoryRows = [];
        $historyErrors = [];

        if (is_array($rawHistory)) {
            if (count($rawHistory) > 20) {
                $historyErrors[] = 'Maksimal 20 baris history ekspor yang dapat diproses sekaligus.';
            } else {
                $rowIdx = 0;
                foreach ($rawHistory as $h) {
                    $rowIdx++;
                    if (!is_array($h)) {
                        continue;
                    }

                    $tahun        = trim((string)($h['tahun'] ?? ''));
                    $produk       = trim((string)($h['produk'] ?? ''));
                    $negaraTujuan = trim((string)($h['negara_tujuan'] ?? ''));
                    $jenisEkspor  = trim((string)($h['jenis_ekspor'] ?? ''));
                    $kuantitas    = trim((string)($h['kuantitas'] ?? ''));
                    $nilaiUsdRaw  = trim((string)($h['nilai_ekspor_usd'] ?? ''));
                    $catatan      = trim((string)($h['catatan'] ?? ''));

                    // Cek apakah seluruh kolom pada baris ini kosong
                    $isAllEmpty = ($tahun === '' && $produk === '' && $negaraTujuan === '' && $jenisEkspor === '' && $kuantitas === '' && $nilaiUsdRaw === '' && $catatan === '');
                    if ($isAllEmpty) {
                        continue; // Baris kosong diabaikan
                    }

                    // Baris yang terisi sebagian atau lengkap wajib memenuhi aturan
                    $prefix = "Baris history #{$rowIdx}: ";

                    // Validasi Tahun: wajib, 4 digit angka
                    if ($tahun === '') {
                        $historyErrors[] = $prefix . 'Tahun ekspor wajib diisi.';
                    } elseif (!preg_match('/^\d{4}$/', $tahun) || (int)$tahun < 1900 || (int)$tahun > 2099) {
                        $historyErrors[] = $prefix . 'Tahun harus berupa 4 digit angka valid (contoh: 2024).';
                    }

                    // Validasi Produk: wajib, min 2 max 255 karakter
                    if ($produk === '') {
                        $historyErrors[] = $prefix . 'Nama produk ekspor wajib diisi.';
                    } elseif (mb_strlen($produk) < 2 || mb_strlen($produk) > 255) {
                        $historyErrors[] = $prefix . 'Nama produk ekspor harus antara 2 hingga 255 karakter.';
                    }

                    // Validasi Negara Tujuan: wajib, min 2 max 150 karakter
                    if ($negaraTujuan === '') {
                        $historyErrors[] = $prefix . 'Negara tujuan wajib diisi.';
                    } elseif (mb_strlen($negaraTujuan) < 2 || mb_strlen($negaraTujuan) > 150) {
                        $historyErrors[] = $prefix . 'Negara tujuan harus antara 2 hingga 150 karakter.';
                    }

                    // Validasi Nilai USD: jika diisi, numeric dan >= 0
                    $nilaiUsd = null;
                    if ($nilaiUsdRaw !== '') {
                        if (!is_numeric($nilaiUsdRaw)) {
                            $historyErrors[] = $prefix . 'Nilai ekspor USD harus berupa angka valid.';
                        } elseif ((float)$nilaiUsdRaw < 0) {
                            $historyErrors[] = $prefix . 'Nilai ekspor USD tidak boleh bernilai negatif.';
                        } else {
                            $nilaiUsd = (float)$nilaiUsdRaw;
                        }
                    }

                    if (mb_strlen($jenisEkspor) > 255) {
                        $historyErrors[] = $prefix . 'Jenis ekspor maksimal 255 karakter.';
                    }
                    if (mb_strlen($kuantitas) > 255) {
                        $historyErrors[] = $prefix . 'Kuantitas maksimal 255 karakter.';
                    }
                    if (mb_strlen($catatan) > 255) {
                        $historyErrors[] = $prefix . 'Catatan maksimal 255 karakter.';
                    }

                    $validHistoryRows[] = [
                        'tahun'            => (int)$tahun,
                        'produk'           => $produk,
                        'negara_tujuan'    => $negaraTujuan,
                        'jenis_ekspor'     => $jenisEkspor !== '' ? $jenisEkspor : null,
                        'kuantitas'        => $kuantitas !== '' ? $kuantitas : null,
                        'nilai_ekspor_usd' => $nilaiUsd,
                        'catatan'          => $catatan !== '' ? $catatan : null,
                    ];
                }
            }
        }

        // Jika terdapat error pada produk atau history
        if (!empty($productErrors) || !empty($historyErrors)) {
            $allErrors = array_merge(array_values($productErrors), array_values($historyErrors));
            session()->setFlashdata('error', 'Terdapat kesalahan pada input formulir. Silakan periksa kembali.');
            return redirect()->back()->withInput()->with('errors', $allErrors);
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
            'deskripsi'          => trim((string)$this->request->getPost('deskripsi')),
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

        // Eksekusi penyimpanan produk dan history dalam satu transaksi database
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $insertId = $this->productModel->insert($dataInsert);
            if (!$insertId) {
                throw new \Exception('Gagal menyimpan produk ke database.');
            }

            // Simpan semua baris history dengan id_supplier dari supplier produk yang dipilih
            if (!empty($validHistoryRows)) {
                foreach ($validHistoryRows as $hRow) {
                    $hRow['id_supplier'] = $supplierId;
                    $this->historyModel->insert($hRow);
                }
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                if ($fotoPath && file_exists(FCPATH . $fotoPath)) {
                    unlink(FCPATH . $fotoPath);
                }
                session()->setFlashdata('error', 'Gagal menyimpan produk dan history ke database.');
                return redirect()->back()->withInput();
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            if ($fotoPath && file_exists(FCPATH . $fotoPath)) {
                unlink(FCPATH . $fotoPath);
            }
            session()->setFlashdata('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        $kodeBaru = SupplierProductModel::getKodeProduk((int)$insertId);
        $msgSuccess = 'Produk sales berhasil ditambahkan dengan kode ' . $kodeBaru;
        if (!empty($validHistoryRows)) {
            $msgSuccess .= ' beserta ' . count($validHistoryRows) . ' catatan history ekspor';
        }
        $msgSuccess .= '.';

        session()->setFlashdata('success', $msgSuccess);
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
                'deskripsi'          => $ecp['produk_dipasarkan'] ?: '',
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
                'exportHistories'   => [],
                'supplier_id'       => 0,
                'validation'        => \Config\Services::validation(),
            ]);
        }

        $produk = $this->productModel->find($id);
        if (!$produk) {
            session()->setFlashdata('error', 'Produk tidak ditemukan atau telah dihapus.');
            return redirect()->to(base_url('admin/produk_sales/index'));
        }

        $supplierId      = !empty($produk['id_supplier']) ? (int)$produk['id_supplier'] : 0;
        $exportHistories = $supplierId > 0 ? $this->historyModel->getBySupplier($supplierId) : [];
        $suppliers       = $this->supplierModel->orderBy('nama_perusahaan', 'ASC')->findAll();
        $kodeProduk      = SupplierProductModel::getKodeProduk($id);
        $currentPhoto    = SupplierProductModel::photoUrl($produk, 1);

        return view('admin/produk_sales/edit', [
            'produk'            => $produk,
            'suppliers'         => $suppliers,
            'kode_produk'       => $kodeProduk,
            'current_photo_url' => $currentPhoto,
            'exportHistories'   => $exportHistories,
            'supplier_id'       => $supplierId,
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
            'deskripsi'          => 'permit_empty',
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
            'deskripsi'          => trim((string)$this->request->getPost('deskripsi')),
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

    /**
     * Menambah catatan history ekspor untuk perusahaan pemilik produk
     */
    public function history_tambah(int $idProduk)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $produk = $this->productModel->find($idProduk);
        if (!$produk || empty($produk['id_supplier'])) {
            session()->setFlashdata('error', 'Produk atau supplier tidak valid untuk menambahkan history ekspor.');
            return redirect()->to(base_url('admin/produk_sales/index'));
        }

        $supplierId = (int)$produk['id_supplier'];

        $rules = [
            'tahun'            => 'required|numeric|exact_length[4]',
            'produk'           => 'required|min_length[2]|max_length[255]',
            'negara_tujuan'    => 'required|min_length[2]|max_length[150]',
            'jenis_ekspor'     => 'permit_empty|max_length[255]',
            'kuantitas'        => 'permit_empty|max_length[255]',
            'nilai_ekspor_usd' => 'permit_empty|numeric|greater_than_equal_to[0]',
            'catatan'          => 'permit_empty|max_length[255]',
        ];

        $messages = [
            'tahun' => [
                'required'     => 'Tahun ekspor wajib diisi.',
                'numeric'      => 'Tahun harus berupa angka.',
                'exact_length' => 'Tahun harus 4 digit angka (contoh: 2024).',
            ],
            'produk' => [
                'required'   => 'Nama produk ekspor wajib diisi.',
                'min_length' => 'Nama produk ekspor minimal 2 karakter.',
                'max_length' => 'Nama produk ekspor maksimal 255 karakter.',
            ],
            'negara_tujuan' => [
                'required'   => 'Negara tujuan wajib diisi.',
                'min_length' => 'Negara tujuan minimal 2 karakter.',
                'max_length' => 'Negara tujuan maksimal 150 karakter.',
            ],
            'nilai_ekspor_usd' => [
                'numeric'               => 'Nilai ekspor USD harus berupa angka valid.',
                'greater_than_equal_to' => 'Nilai ekspor USD tidak boleh bernilai negatif.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            $errList = implode(' ', $this->validator->getErrors());
            session()->setFlashdata('error', $errList);
            return redirect()->to(base_url('admin/produk_sales/edit/' . $idProduk))->withInput();
        }

        $nilaiUsdRaw = trim((string)$this->request->getPost('nilai_ekspor_usd'));
        $nilaiUsd    = ($nilaiUsdRaw !== '') ? (float)$nilaiUsdRaw : null;

        $dataInsert = [
            'id_supplier'       => $supplierId,
            'tahun'             => (int)$this->request->getPost('tahun'),
            'produk'            => trim((string)$this->request->getPost('produk')),
            'negara_tujuan'     => trim((string)$this->request->getPost('negara_tujuan')),
            'jenis_ekspor'      => trim((string)$this->request->getPost('jenis_ekspor')) ?: null,
            'kuantitas'         => trim((string)$this->request->getPost('kuantitas')) ?: null,
            'nilai_ekspor_usd'  => $nilaiUsd,
            'nilai_ekspor_asli' => $nilaiUsdRaw ?: null,
            'catatan'           => trim((string)$this->request->getPost('catatan')) ?: null,
        ];

        $this->historyModel->insert($dataInsert);
        session()->setFlashdata('success', 'Catatan history ekspor berhasil ditambahkan.');

        return redirect()->to(base_url('admin/produk_sales/edit/' . $idProduk));
    }

    /**
     * Memperbarui catatan history ekspor
     */
    public function history_ubah(int $idProduk, int $idHistory)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $produk = $this->productModel->find($idProduk);
        if (!$produk || empty($produk['id_supplier'])) {
            session()->setFlashdata('error', 'Produk atau supplier tidak valid.');
            return redirect()->to(base_url('admin/produk_sales/index'));
        }

        $supplierId = (int)$produk['id_supplier'];
        $history    = $this->historyModel->find($idHistory);

        if (!$history) {
            session()->setFlashdata('error', 'Catatan history ekspor tidak ditemukan.');
            return redirect()->to(base_url('admin/produk_sales/edit/' . $idProduk));
        }

        // Validasi kepemilikan data (Cegah mengubah data perusahaan lain)
        if ((int)$history['id_supplier'] !== $supplierId) {
            session()->setFlashdata('error', 'Akses ditolak: History ekspor ini bukan milik perusahaan dari produk yang sedang diedit.');
            return redirect()->to(base_url('admin/produk_sales/edit/' . $idProduk));
        }

        $rules = [
            'tahun'            => 'required|numeric|exact_length[4]',
            'produk'           => 'required|min_length[2]|max_length[255]',
            'negara_tujuan'    => 'required|min_length[2]|max_length[150]',
            'jenis_ekspor'     => 'permit_empty|max_length[255]',
            'kuantitas'        => 'permit_empty|max_length[255]',
            'nilai_ekspor_usd' => 'permit_empty|numeric|greater_than_equal_to[0]',
            'catatan'          => 'permit_empty|max_length[255]',
        ];

        $messages = [
            'tahun' => [
                'required'     => 'Tahun ekspor wajib diisi.',
                'numeric'      => 'Tahun harus berupa angka.',
                'exact_length' => 'Tahun harus 4 digit angka (contoh: 2024).',
            ],
            'produk' => [
                'required'   => 'Nama produk ekspor wajib diisi.',
                'min_length' => 'Nama produk ekspor minimal 2 karakter.',
                'max_length' => 'Nama produk ekspor maksimal 255 karakter.',
            ],
            'negara_tujuan' => [
                'required'   => 'Negara tujuan wajib diisi.',
                'min_length' => 'Negara tujuan minimal 2 karakter.',
                'max_length' => 'Negara tujuan maksimal 150 karakter.',
            ],
            'nilai_ekspor_usd' => [
                'numeric'               => 'Nilai ekspor USD harus berupa angka valid.',
                'greater_than_equal_to' => 'Nilai ekspor USD tidak boleh bernilai negatif.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            $errList = implode(' ', $this->validator->getErrors());
            session()->setFlashdata('error', $errList);
            return redirect()->to(base_url('admin/produk_sales/edit/' . $idProduk))->withInput();
        }

        $nilaiUsdRaw = trim((string)$this->request->getPost('nilai_ekspor_usd'));
        $nilaiUsd    = ($nilaiUsdRaw !== '') ? (float)$nilaiUsdRaw : null;

        $dataUpdate = [
            'tahun'             => (int)$this->request->getPost('tahun'),
            'produk'            => trim((string)$this->request->getPost('produk')),
            'negara_tujuan'     => trim((string)$this->request->getPost('negara_tujuan')),
            'jenis_ekspor'      => trim((string)$this->request->getPost('jenis_ekspor')) ?: null,
            'kuantitas'         => trim((string)$this->request->getPost('kuantitas')) ?: null,
            'nilai_ekspor_usd'  => $nilaiUsd,
            'nilai_ekspor_asli' => $nilaiUsdRaw ?: null,
            'catatan'           => trim((string)$this->request->getPost('catatan')) ?: null,
        ];

        $this->historyModel->update($idHistory, $dataUpdate);
        session()->setFlashdata('success', 'Catatan history ekspor berhasil diperbarui.');

        return redirect()->to(base_url('admin/produk_sales/edit/' . $idProduk));
    }

    /**
     * Menghapus catatan history ekspor
     */
    public function history_hapus(int $idProduk, int $idHistory)
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $produk = $this->productModel->find($idProduk);
        if (!$produk || empty($produk['id_supplier'])) {
            session()->setFlashdata('error', 'Produk atau supplier tidak valid.');
            return redirect()->to(base_url('admin/produk_sales/index'));
        }

        $supplierId = (int)$produk['id_supplier'];
        $history    = $this->historyModel->find($idHistory);

        if (!$history) {
            session()->setFlashdata('error', 'Catatan history ekspor tidak ditemukan.');
            return redirect()->to(base_url('admin/produk_sales/edit/' . $idProduk));
        }

        // Validasi kepemilikan data (Cegah menghapus data perusahaan lain)
        if ((int)$history['id_supplier'] !== $supplierId) {
            session()->setFlashdata('error', 'Akses ditolak: History ekspor ini bukan milik perusahaan dari produk yang sedang diedit.');
            return redirect()->to(base_url('admin/produk_sales/edit/' . $idProduk));
        }

        $this->historyModel->delete($idHistory);
        session()->setFlashdata('success', 'Catatan history ekspor berhasil dihapus.');

        return redirect()->to(base_url('admin/produk_sales/edit/' . $idProduk));
    }
}
