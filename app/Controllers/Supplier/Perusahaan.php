<?php

namespace App\Controllers\Supplier;

use App\Models\SupplierModel;
use App\Models\SupplierUserModel;
use Config\Database;

class Perusahaan extends SupplierBase
{
    protected $supplierModel;
    protected $supplierUserModel;

    public function __construct()
    {
        $this->supplierModel     = new SupplierModel();
        $this->supplierUserModel = new SupplierUserModel();
    }

    /**
     * Menampilkan form profil perusahaan (Lengkapi / Edit)
     * Route: GET /supplier/perusahaan
     */
    public function index()
    {
        $supplierId = session()->get('supplier_id');
        $perusahaan = null;
        $isNew      = true;

        if (!empty($supplierId)) {
            $perusahaan = $this->supplierModel->find($supplierId);
            if ($perusahaan) {
                $isNew = false;
            } else {
                // Sesi supplier_id tidak sinkron dengan database
                session()->set('supplier_id', null);
            }
        }

        $pageTitle = $isNew ? 'Lengkapi Profil Perusahaan' : 'Profil Perusahaan';

        $data = [
            'title'      => $pageTitle . ' - Area Supplier',
            'page_title' => $pageTitle,
            'activeMenu' => 'perusahaan',
            'isNew'      => $isNew,
            'perusahaan' => $perusahaan ?? [],
            'validation' => \Config\Services::validation(),
        ];

        return view('supplier/perusahaan', $data);
    }

    /**
     * Menyimpan data profil perusahaan (Tambah atau Edit)
     * Route: POST /supplier/perusahaan/simpan
     */
    public function simpan()
    {
        $rules = [
            'nama_perusahaan' => [
                'rules'  => 'required|max_length[255]',
                'errors' => [
                    'required'   => 'Nama Perusahaan wajib diisi.',
                    'max_length' => 'Nama Perusahaan maksimal 255 karakter.',
                ],
            ],
            'email' => [
                'rules'  => 'permit_empty|valid_email|max_length[150]',
                'errors' => [
                    'valid_email' => 'Format email perusahaan tidak valid.',
                    'max_length'  => 'Email maksimal 150 karakter.',
                ],
            ],
            'no_hp' => [
                'rules'  => 'permit_empty|max_length[30]',
                'errors' => [
                    'max_length' => 'Nomor HP maksimal 30 karakter.',
                ],
            ],
            'link_dokumen_legalitas' => [
                'rules'  => 'permit_empty|valid_url_strict[http,https]|max_length[1000]',
                'errors' => [
                    'valid_url_strict' => 'Link dokumen legalitas harus berupa URL valid (diawali http:// atau https://).',
                    'max_length'       => 'Link dokumen legalitas maksimal 1000 karakter.',
                ],
            ],
            'link_dokumen_sertifikat' => [
                'rules'  => 'permit_empty|valid_url_strict[http,https]|max_length[1000]',
                'errors' => [
                    'valid_url_strict' => 'Link dokumen sertifikat harus berupa URL valid (diawali http:// atau https://).',
                    'max_length'       => 'Link dokumen sertifikat maksimal 1000 karakter.',
                ],
            ],
        ];

        $namaPerusahaan = trim((string)($this->request->getPost('nama_perusahaan') ?? $_POST['nama_perusahaan'] ?? ''));
        $email          = trim((string)($this->request->getPost('email') ?? $_POST['email'] ?? ''));
        $noHp           = trim((string)($this->request->getPost('no_hp') ?? $_POST['no_hp'] ?? ''));
        $linkLegalitas  = trim((string)($this->request->getPost('link_dokumen_legalitas') ?? $_POST['link_dokumen_legalitas'] ?? ''));
        $linkSertifikat = trim((string)($this->request->getPost('link_dokumen_sertifikat') ?? $_POST['link_dokumen_sertifikat'] ?? ''));

        $dataToValidate = [
            'nama_perusahaan'         => $namaPerusahaan,
            'email'                   => $email,
            'no_hp'                   => $noHp,
            'link_dokumen_legalitas'  => $linkLegalitas,
            'link_dokumen_sertifikat' => $linkSertifikat,
        ];

        if (!$this->validateData($dataToValidate, $rules)) {
            session()->setFlashdata('error', implode('<br>', $this->validator->getErrors()));
            return redirect()->back()->withInput();
        }

        $namaKunci = SupplierModel::makeKey($namaPerusahaan);

        if ($namaKunci === '') {
            session()->setFlashdata('error', 'Nama perusahaan tidak valid.');
            return redirect()->back()->withInput();
        }

        $supplierId = session()->get('supplier_id');
        $userId     = session()->get('supplier_user_id');
        $username   = session()->get('supplier_username') ?? 'supplier';

        $getPostVal = function($key) {
            return trim((string)($this->request->getPost($key) ?? $_POST[$key] ?? ''));
        };

        // Kumpulkan data field perusahaan
        $formData = [
            'nama_perusahaan'            => $namaPerusahaan,
            'nama_kunci'                 => $namaKunci,
            'nama_kontak'                => $getPostVal('nama_kontak'),
            'email'                      => $email,
            'no_hp'                      => $noHp,
            'website'                    => $getPostVal('website'),
            'instagram'                  => $getPostVal('instagram'),
            'domisili_usaha'             => $getPostVal('domisili_usaha'),
            'lokasi_pabrik_gudang'       => $getPostVal('lokasi_pabrik_gudang'),
            'produk_dipasarkan'          => $getPostVal('produk_dipasarkan'),
            'jenis_perusahaan'           => $getPostVal('jenis_perusahaan'),
            'kapasitas_produksi'         => $getPostVal('kapasitas_produksi'),
            'jumlah_pegawai'             => $getPostVal('jumlah_pegawai'),
            'pameran_internasional'      => $getPostVal('pameran_internasional'),
            'keunggulan_produk'          => $getPostVal('keunggulan_produk'),
            'link_foto_usaha'            => $getPostVal('link_foto_usaha'),
            'legalitas'                  => $getPostVal('legalitas'),
            'link_dokumen_legalitas'     => $linkLegalitas,
            'sertifikasi'                => $getPostVal('sertifikasi'),
            'link_dokumen_sertifikat'    => $linkSertifikat,
        ];

        if (empty($supplierId)) {
            // ==========================================
            // KASUS A: TAMBAH PERUSAHAAN BARU
            // ==========================================
            // Cek apakah nama_kunci sudah digunakan perusahaan lain
            $existing = $this->supplierModel->where('nama_kunci', $namaKunci)->first();
            if ($existing) {
                session()->setFlashdata('error', 'Perusahaan dengan nama ini sudah terdaftar. Hubungi admin untuk menghubungkan akun Anda.');
                return redirect()->back()->withInput();
            }

            $formData['sumber_tahun'] = date('Y');

            $newSupplierId = $this->supplierModel->insert($formData);
            if (!$newSupplierId) {
                session()->setFlashdata('error', 'Terjadi kesalahan saat menyimpan data perusahaan.');
                return redirect()->back()->withInput();
            }

            // Hubungkan akun supplier_user ke perusahaan baru
            if (!empty($userId)) {
                $this->supplierUserModel->update($userId, ['id_supplier' => $newSupplierId]);
            }

            // Simpan supplier_id ke session
            session()->set('supplier_id', $newSupplierId);

            log_message('info', "Supplier user '{$username}' berhasil menambahkan profil perusahaan baru '{$namaPerusahaan}' (ID: {$newSupplierId}).");
            session()->setFlashdata('success', 'Profil perusahaan berhasil disimpan.');
            return redirect()->to(base_url('supplier/dashboard'));
        } else {
            // ==========================================
            // KASUS B: EDIT PERUSAHAAN YANG ADA
            // ==========================================
            $company = $this->supplierModel->find($supplierId);
            if (!$company) {
                session()->set('supplier_id', null);
                session()->setFlashdata('error', 'Data perusahaan tidak ditemukan.');
                return redirect()->to(base_url('supplier/perusahaan'));
            }

            // Cek apakah nama_kunci baru sudah dipakai perusahaan lain
            $existing = $this->supplierModel
                ->where('nama_kunci', $namaKunci)
                ->where('id_supplier !=', $supplierId)
                ->first();

            if ($existing) {
                session()->setFlashdata('error', 'Perusahaan dengan nama ini sudah terdaftar. Hubungi admin untuk menghubungkan akun Anda.');
                return redirect()->back()->withInput();
            }

            $this->supplierModel->update($supplierId, $formData);

            log_message('info', "Supplier user '{$username}' memperbarui profil perusahaan ID: {$supplierId} ('{$namaPerusahaan}').");
            session()->setFlashdata('success', 'Profil perusahaan berhasil diperbarui.');
            return redirect()->to(base_url('supplier/perusahaan'));
        }
    }

    /**
     * Menghapus perusahaan milik akun yang sedang login beserta produk, foto, dan history ekspornya
     * Route: POST /supplier/perusahaan/hapus
     */
    public function hapus()
    {
        $supplierId = session()->get('supplier_id');
        $username   = session()->get('supplier_username') ?? 'supplier';

        if (empty($supplierId)) {
            session()->setFlashdata('error', 'Anda belum memiliki data perusahaan untuk dihapus.');
            return redirect()->to(base_url('supplier/perusahaan'));
        }

        $company = $this->supplierModel->find($supplierId);
        if (!$company) {
            session()->set('supplier_id', null);
            session()->setFlashdata('error', 'Data perusahaan tidak ditemukan.');
            return redirect()->to(base_url('supplier/perusahaan'));
        }

        // Verifikasi konfirmasi nama perusahaan di sisi server
        $confirmName = trim((string)($this->request->getPost('confirm_nama_perusahaan') ?? $_POST['confirm_nama_perusahaan'] ?? ''));
        $realName    = trim((string)$company['nama_perusahaan']);

        if ($confirmName !== $realName) {
            session()->setFlashdata('error', 'Konfirmasi nama perusahaan tidak cocok. Penghapusan dibatalkan.');
            return redirect()->to(base_url('supplier/perusahaan'));
        }

        $db = Database::connect('default');

        // 1. Ambil semua id_produk_supplier milik perusahaan untuk penghapusan file foto
        $products = $db->table('tb_produk_supplier')
            ->select('id_produk_supplier, foto_1, foto_2, foto_3, foto_4, foto_5')
            ->where('id_supplier', $supplierId)
            ->get()
            ->getResultArray();

        $filesToDelete = [];
        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'supplier' . DIRECTORY_SEPARATOR;

        foreach ($products as $p) {
            $prodId = $p['id_produk_supplier'];

            // Format pola standar: {id}_{1..5}.jpg
            for ($i = 1; $i <= 5; $i++) {
                $filesToDelete[] = $uploadDir . $prodId . '_' . $i . '.jpg';
            }

            // Cek juga nama file pada kolom foto_1 s/d foto_5
            for ($i = 1; $i <= 5; $i++) {
                $fotoCol = $p['foto_' . $i] ?? null;
                if (!empty($fotoCol)) {
                    $basename = basename($fotoCol);
                    $filesToDelete[] = $uploadDir . $basename;
                }
            }
        }

        $filesToDelete = array_unique($filesToDelete);

        // 2. Transaksi database untuk penghapusan tb_supplier
        // (tb_produk_supplier dan tb_history_export_supplier terhapus lewat FK CASCADE,
        // tb_supplier_user.id_supplier otomatis SET NULL lewat FK)
        $db->transBegin();

        try {
            $db->table('tb_supplier')->where('id_supplier', $supplierId)->delete();

            if ($db->transStatus() === false) {
                $db->transRollback();
                session()->setFlashdata('error', 'Terjadi kesalahan sistem saat menghapus data perusahaan.');
                return redirect()->to(base_url('supplier/perusahaan'));
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', "Gagal menghapus perusahaan ID {$supplierId}: " . $e->getMessage());
            session()->setFlashdata('error', 'Gagal menghapus perusahaan: ' . $e->getMessage());
            return redirect()->to(base_url('supplier/perusahaan'));
        }

        // 3. Hapus file foto dari disk SETELAH transaksi database berhasil
        foreach ($filesToDelete as $filePath) {
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        // 4. Update session
        session()->set('supplier_id', null);

        // 5. Catat log_message TANPA password
        log_message('info', "Supplier user '{$username}' menghapus perusahaan ID: {$supplierId} ('{$realName}') beserta seluruh data produk, foto, dan history ekspornya.");

        // 6. Redirect dengan flash sukses
        session()->setFlashdata('success', 'Perusahaan dan seluruh datanya telah dihapus.');
        return redirect()->to(base_url('supplier/perusahaan'));
    }
}
