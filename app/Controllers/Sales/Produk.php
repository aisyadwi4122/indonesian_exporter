<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\SupplierModel;
use App\Models\SupplierExportHistoryModel;
use App\Models\SupplierProdukModel;
use App\Models\EcpEksporModel;
use App\Models\SupplierProductModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;
use Dompdf\Options;

class Produk extends BaseController
{
    protected $helpers = ['url'];

    /**
     * Menampilkan katalog produk terpadu dalam satu daftar format grid kartu 4 kolom.
     * Route: GET /sales/produk
     */
    public function index()
    {
        $modeInfo = $this->resolveSalesMode();
        $isInternal = $modeInfo['is_internal'];
        $isPreview = $modeInfo['is_preview'];
        $accountTipe = $modeInfo['account_tipe'];

        $q = trim((string)($this->request->getGet('q') ?? ''));
        $page = max(1, (int)($this->request->getGet('page') ?? 1));
        $perPage = 16; // 4 baris x 4 kolom

        // Ambil seluruh produk (127 produk: 10 Supplier di awal + 117 ECP) sesuai kata kunci pencarian
        $allItems = $this->fetchProducts($q, $isInternal);

        // Jika mode eksternal, samarkan nama perusahaan & sembunyikan komisi
        if (!$isInternal) {
<<<<<<< HEAD
            $komisiMap = array_column($products, 'komisi', 'id_produk_supplier');
            $products = SupplierProductModel::maskForExternal($products);
            foreach ($products as &$p) {
                $prodId = $p['id_produk_supplier'] ?? ($p['id_produk'] ?? 0);
                if (isset($komisiMap[$prodId])) {
                    $p['komisi'] = $komisiMap[$prodId];
                }
            }
            unset($p);
=======
            foreach ($allItems as &$it) {
                $it['nama_perusahaan'] = 'PT Indonesian Exporter Partner';
                $it['komisi'] = null;
            }
            unset($it);
>>>>>>> 0f0f67a875448aefeb56fcb6704b2d6918ef7410
        }

        $totalRows = count($allItems);
        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;
        $items = array_slice($allItems, $offset, $perPage);

        $data = [
            'title'        => 'Katalog Produk Sales - Indonesian Exporter',
            'page_title'   => 'Katalog Produk',
            'activeMenu'   => 'produk',
            'q'            => $q,
            'page'         => $page,
            'perPage'      => $perPage,
            'totalRows'    => $totalRows,
            'totalPages'   => $totalPages,
            'items'        => $items,
            'is_internal'  => $isInternal,
            'is_preview'   => $isPreview,
            'account_tipe' => $accountTipe,
        ];

        return view('sales/produk/index', $data);
    }

    /**
     * Menampilkan detail 1 produk (3 tab: 1. Data Produk, 2. Data Perusahaan, 3. Data History Ekspor).
     * Route: GET /sales/produk/detail/{id} dan GET /sales/produk/{id}
     */
    public function detail($id = null)
    {
        if ($id === null || trim((string)$id) === '') {
            throw PageNotFoundException::forPageNotFound('ID produk tidak valid');
        }

        $modeInfo = $this->resolveSalesMode();
        $isInternal = $modeInfo['is_internal'];
        $isPreview = $modeInfo['is_preview'];
        $accountTipe = $modeInfo['account_tipe'];

        $detailData = $this->buildDetailData((string)$id);
        if (empty($detailData)) {
            throw PageNotFoundException::forPageNotFound('Data produk tidak ditemukan');
        }

        if (!$isInternal) {
            $detailData['company']['nama_perusahaan'] = 'PT Indonesian Exporter Partner';
            $detailData['company']['nama_pic'] = 'Tim Sales Indonesian Exporter';
            $detailData['company']['no_hp'] = '-';
            $detailData['company']['email'] = 'sales@indonesian-exporter.com';
            $detailData['company']['website'] = 'indonesian-exporter.com';
            $detailData['company']['instagram'] = '@indonesian.exporter';
            $detailData['company']['lokasi_pabrik'] = '-';
            $detailData['product']['komisi'] = '-';
        }

        $namaProduk = $detailData['product']['nama_produk'] ?? 'Detail Produk';
        $namaPerusahaan = $detailData['company']['nama_perusahaan'] ?? '';

        $data = array_merge([
            'title'        => esc($namaProduk) . ' - ' . esc($namaPerusahaan) . ' | Area Sales',
            'page_title'   => 'Detail Produk',
            'activeMenu'   => 'produk',
            'id'           => (string)$id,
            'is_internal'  => $isInternal,
            'is_preview'   => $isPreview,
            'account_tipe' => $accountTipe,
        ], $detailData);

        return view('sales/produk/detail', $data);
    }

    /**
     * Download PDF detail 1 produk (ringkasan 3 tab + gambar produk).
     * Route: GET /sales/produk/detail/{id}/pdf dan GET /sales/produk/{id}/pdf
     */
    public function detailPdf($id = null)
    {
        if ($id === null || trim((string)$id) === '') {
            throw PageNotFoundException::forPageNotFound('ID produk tidak valid');
        }

        $modeInfo = $this->resolveSalesMode();
        $accountTipe = $modeInfo['account_tipe'];
        $isPreview = $modeInfo['is_preview'];

        $pdfType = strtolower(trim((string)$this->request->getGet('tipe_pdf')));
        $isPdfInternal = false;

        if ($accountTipe === 'internal' && !$isPreview && $pdfType === 'internal') {
            $isPdfInternal = true;
        } else {
            $isPdfInternal = false;
        }

        $detailData = $this->buildDetailData((string)$id);
        if (empty($detailData)) {
            throw PageNotFoundException::forPageNotFound('Data produk tidak ditemukan');
        }

        if (!$isPdfInternal) {
            $detailData['company']['nama_perusahaan'] = 'PT Indonesian Exporter Partner';
            $detailData['company']['nama_pic'] = 'Tim Sales Indonesian Exporter';
            $detailData['company']['no_hp'] = '-';
            $detailData['company']['email'] = 'sales@indonesian-exporter.com';
            $detailData['company']['website'] = 'indonesian-exporter.com';
            $detailData['company']['instagram'] = '@indonesian.exporter';
            $detailData['company']['lokasi_pabrik'] = '-';
            $detailData['product']['komisi'] = '-';
        }

        ini_set('memory_limit', '512M');
        set_time_limit(120);

        // Logo base64
        $logoData = $this->getBase64Logo();

        // Siapkan foto produk base64
        $productImageData = $this->resolveBase64Image($detailData['product']['foto_raw'] ?? null);

        $html = view('sales/produk/pdf_detail', [
            'data'             => $detailData,
            'logoData'         => $logoData,
            'productImageData' => $productImageData,
            'tanggal'          => date('d F Y'),
            'is_internal'      => $isPdfInternal,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', FCPATH);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        $this->addPageNumbersToCanvas($dompdf);

        $rawName = $detailData['product']['nama_produk'] ?? ('item-' . $id);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $rawName), '-'));
        if (empty($slug)) {
            $slug = 'produk';
        }
        $filename = 'produk-' . $slug . ($isPdfInternal ? '-internal' : '') . '-' . date('Ymd') . '.pdf';

        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /**
     * Export PDF daftar produk sesuai pencarian saat ini.
     * Route: GET /sales/produk/pdf
     */
    public function exportPdf()
    {
        $modeInfo = $this->resolveSalesMode();
        $accountTipe = $modeInfo['account_tipe'];
        $isPreview = $modeInfo['is_preview'];

        $pdfType = strtolower(trim((string)$this->request->getGet('tipe_pdf')));
        $isPdfInternal = false;

        if ($accountTipe === 'internal' && !$isPreview && $pdfType === 'internal') {
            $isPdfInternal = true;
        } else {
            $isPdfInternal = false;
        }

        $q = trim((string)($this->request->getGet('q') ?? ''));

        $items = $this->fetchProducts($q, $isPdfInternal);
        if (empty($items)) {
            $redirectUrl = base_url('sales/produk') . ($q !== '' ? '?q=' . urlencode($q) : '');
            return redirect()->to($redirectUrl)->with('error', 'Tidak ada data produk yang ditemukan untuk diekspor');
        }

        if (!$isPdfInternal) {
            foreach ($items as &$it) {
                $it['nama_perusahaan'] = 'PT Indonesian Exporter Partner';
                $it['komisi'] = null;
            }
            unset($it);
        }

        ini_set('memory_limit', '512M');
        set_time_limit(180);

        $logoData = $this->getBase64Logo();

        $html = view('sales/produk/pdf_list', [
            'items'       => $items,
            'q'           => $q,
            'logoData'    => $logoData,
            'tanggal'     => date('d F Y'),
            'is_internal' => $isPdfInternal,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', FCPATH);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();

        $this->addPageNumbersToCanvas($dompdf);

        $filename = 'daftar-produk-sales-' . ($isPdfInternal ? 'internal-' : '') . date('Ymd') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /**
     * Menentukan hak akses akun Sales dari database (bukan hanya session)
     * dan menentukan mode aktif (internal vs eksternal) serta status pratinjau.
     *
     * @return array{account_tipe: string, is_internal: bool, is_preview: bool}
     */
    private function resolveSalesMode(): array
    {
        $salesId = session()->get('sales_id') ?? session()->get('user_id');
        $username = session()->get('username');

        $accountTipeAkses = 'eksternal'; // Nilai selain 'internal' atau jika akun tidak ditemukan dianggap eksternal

        if (!empty($salesId) || !empty($username)) {
            $salesModel = new \App\Models\SalesModel();
            $user = null;
            if (!empty($salesId)) {
                $user = $salesModel->find($salesId);
            }
            if (!$user && !empty($username)) {
                $user = $salesModel->where('username', $username)->first();
            }

            if ($user && isset($user['tipe_akses']) && strtolower(trim((string)$user['tipe_akses'])) === 'internal') {
                $accountTipeAkses = 'internal';
            }
        }

        $requestedMode = strtolower(trim((string)$this->request->getGet('mode')));
        $isInternal = false;
        $isPreview = false;

        if ($accountTipeAkses === 'internal') {
            if ($requestedMode === 'eksternal') {
                $isInternal = false;
                $isPreview = true;
            } else {
                $isInternal = true;
                $isPreview = false;
            }
        } else {
            // Akun eksternal: ?mode=internal diabaikan sepenuhnya
            $isInternal = false;
            $isPreview = false;
        }

        return [
            'account_tipe' => $accountTipeAkses,
            'is_internal'  => $isInternal,
            'is_preview'   => $isPreview,
        ];
    }

    /**
     * Mengambil seluruh daftar produk terpadu sesuai kata kunci pencarian.
     * Urutan: 10 produk Supplier di awal, lalu 117 produk ECP.
     */
    private function fetchProducts(string $q, bool $isInternal = true): array
    {
        $items = [];
        $db = \Config\Database::connect();

        // 1. Ambil data Supplier Produk (10 baris) - diletakkan di awal / halaman pertama
        $spBuilder = $db->table('tb_supplier_produk');
        if ($q !== '') {
            $spBuilder->groupStart()
                ->like('nama_produk', $q)
                ->orLike('lokasi', $q)
                ->orLike('jenis_usaha', $q)
                ->orLike('spesifikasi_kapasitas', $q);
            if ($isInternal) {
                $spBuilder->orLike('nama_perusahaan', $q)
                    ->orLike('legalitas', $q)
                    ->orLike('sertifikasi', $q);
            }
            $spBuilder->groupEnd();
        }

        $spRows = $spBuilder->orderBy('id_supplier_produk', 'ASC')->get()->getResultArray();
        foreach ($spRows as $r) {
            $photoUrl = SupplierProdukModel::extractPhotoUrl($r['foto_produk'] ?? null);
            $idSupplierProd = (int)$r['id_supplier_produk'];
            $items[] = [
                'id'               => 'sp-' . $idSupplierProd,
                'raw_id'           => $idSupplierProd,
                'source'           => 'supplier',
                'kode_produk'      => SupplierProductModel::getKodeProduk($idSupplierProd),
                'nama_produk'      => $r['nama_produk'] ?: 'Produk Supplier',
                'nama_perusahaan'  => $r['nama_perusahaan'] ?: '-',
                'lokasi'           => $r['lokasi'] ?: '-',
                'kapasitas'        => $r['spesifikasi_kapasitas'] ?: '-',
                'kategori'         => $r['jenis_usaha'] ?: 'Supplier',
                'foto_url'         => $photoUrl,
                'has_photo'        => !empty($photoUrl),
                'komisi'           => $r['komisi'] ?: null,
                'harga_moq'        => $r['harga_moq'] ?: null,
                'negara_tujuan'    => null,
                'nilai_ekspor_usd' => null,
            ];
        }

        // 2. Ambil data ECP (117 baris)
        $builder = $db->table('tb_ecp_ekspor');
        if ($q !== '') {
            $builder->groupStart()
                ->like('produk_ekspor', $q)
                ->orLike('produk_dipasarkan', $q)
                ->orLike('domisili', $q)
                ->orLike('wilayah_ecp', $q)
                ->orLike('kategori_produk', $q)
                ->orLike('merk_dagang', $q)
                ->orLike('negara_tujuan', $q);
            if ($isInternal) {
                $builder->orLike('nama_perusahaan', $q);
            }
            $builder->groupEnd();
        }

        $ecpRows = $builder->orderBy('id_ecp', 'ASC')
                           ->get()
                           ->getResultArray();

        foreach ($ecpRows as $r) {
            $namaProduk = !empty($r['produk_ekspor']) ? $r['produk_ekspor'] : (!empty($r['produk_dipasarkan']) ? $r['produk_dipasarkan'] : 'Produk Ekspor');
            $idEcp = (int)$r['id_ecp'];
            $items[] = [
                'id'               => 'ecp-' . $idEcp,
                'raw_id'           => $idEcp,
                'source'           => 'ecp',
                'kode_produk'      => 'ECP-' . str_pad((string)$idEcp, 4, '0', STR_PAD_LEFT),
                'nama_produk'      => $namaProduk,
                'nama_perusahaan'  => $r['nama_perusahaan'] ?: '-',
                'lokasi'           => !empty($r['domisili']) ? $r['domisili'] : (!empty($r['wilayah_ecp']) ? $r['wilayah_ecp'] : '-'),
                'kapasitas'        => !empty($r['kapasitas_produksi']) ? $r['kapasitas_produksi'] : (!empty($r['kuantitas_ekspor']) ? 'Ekspor: ' . $r['kuantitas_ekspor'] : '-'),
                'kategori'         => $r['kategori_produk'] ?: 'Umum',
                'foto_url'         => null,
                'has_photo'        => false,
                'komisi'           => null,
                'harga_moq'        => null,
                'negara_tujuan'    => $r['negara_tujuan'] ?? null,
                'nilai_ekspor_usd' => isset($r['nilai_ekspor_usd']) ? (float)$r['nilai_ekspor_usd'] : null,
            ];
        }

        return $items;
    }

    /**
     * Membangun data terstruktur untuk halaman detail produk (3 tab).
     */
    private function buildDetailData(string $idStr): array
    {
        $db = \Config\Database::connect();
        $source = 'ecp';
        $rawId = 0;

        $idStr = trim($idStr);
        if (str_starts_with($idStr, 'sp-')) {
            $source = 'supplier';
            $rawId = (int)substr($idStr, 3);
        } elseif (str_starts_with($idStr, 'ecp-')) {
            $source = 'ecp';
            $rawId = (int)substr($idStr, 4);
        } elseif (is_numeric($idStr)) {
            $numericId = (int)$idStr;
            $checkEcp = $db->table('tb_ecp_ekspor')->where('id_ecp', $numericId)->countAllResults();
            if ($checkEcp > 0) {
                $source = 'ecp';
                $rawId = $numericId;
            } else {
                $checkSp = $db->table('tb_supplier_produk')->where('id_supplier_produk', $numericId)->countAllResults();
                if ($checkSp > 0) {
                    $source = 'supplier';
                    $rawId = $numericId;
                } else {
                    $source = 'ecp';
                    $rawId = $numericId;
                }
            }
        } else {
            return [];
        }

        if ($source === 'ecp') {
            $ecp = $db->table('tb_ecp_ekspor')->where('id_ecp', $rawId)->get()->getRowArray();
            if (!$ecp) {
                return [];
            }

            $namaPerusahaan = $ecp['nama_perusahaan'];

            // Cari profil supplier tambahan jika ada
            $kunci = strtolower(trim(preg_replace('/[^a-z0-9]/i', '', $namaPerusahaan)));
            $supplierExtra = $db->table('tb_supplier')->where('nama_kunci', $kunci)->get()->getRowArray();

            $company = [
                'nama_perusahaan'  => $namaPerusahaan,
                'domisili'         => $ecp['domisili'] ?: ($supplierExtra['domisili_usaha'] ?? '-'),
                'wilayah_ecp'      => $ecp['wilayah_ecp'] ?: '-',
                'lokasi_pabrik'    => $supplierExtra['lokasi_pabrik_gudang'] ?? '-',
                'nama_pic'         => $ecp['nama_peserta'] ?: ($supplierExtra['nama_kontak'] ?? '-'),
                'email'            => $ecp['email'] ?: ($supplierExtra['email'] ?? '-'),
                'no_hp'            => $ecp['no_hp'] ?: ($supplierExtra['no_hp'] ?? '-'),
                'website'          => $ecp['website'] ?: ($supplierExtra['website'] ?? '-'),
                'instagram'        => $ecp['instagram'] ?: ($supplierExtra['instagram'] ?? '-'),
                'jenis_perusahaan' => $ecp['jenis_perusahaan'] ?: ($supplierExtra['jenis_perusahaan'] ?? '-'),
                'omset'            => $ecp['omset'] ?: '-',
                'jumlah_pegawai'   => $supplierExtra['jumlah_pegawai'] ?? '-',
                'legalitas'        => $supplierExtra['legalitas'] ?? '-',
                'sertifikasi'      => $supplierExtra['sertifikasi'] ?? '-',
            ];

            $namaProduk = !empty($ecp['produk_ekspor']) ? $ecp['produk_ekspor'] : (!empty($ecp['produk_dipasarkan']) ? $ecp['produk_dipasarkan'] : 'Produk Ekspor');

            $product = [
                'id'                 => 'ecp-' . $ecp['id_ecp'],
                'kode_produk'        => 'ECP-' . str_pad((string)$ecp['id_ecp'], 4, '0', STR_PAD_LEFT),
                'nama_produk'        => $namaProduk,
                'produk_dipasarkan'  => $ecp['produk_dipasarkan'] ?: '-',
                'kategori_produk'    => $ecp['kategori_produk'] ?: 'Umum',
                'merk_dagang'        => $ecp['merk_dagang'] ?: '-',
                'kapasitas_produksi' => $ecp['kapasitas_produksi'] ?: ($supplierExtra['kapasitas_produksi'] ?? '-'),
                'deskripsi'          => !empty($ecp['produk_dipasarkan']) ? $ecp['produk_dipasarkan'] : $namaProduk,
                'kuantitas_ekspor'   => $ecp['kuantitas_ekspor'] ?: '-',
                'nilai_ekspor_usd'   => $ecp['nilai_ekspor_usd'] !== null ? (float)$ecp['nilai_ekspor_usd'] : null,
                'foto_url'           => null,
                'foto_raw'           => null,
                'keunggulan'         => $supplierExtra['keunggulan_produk'] ?? '-',
            ];

            // Riwayat Ekspor untuk perusahaan ini
            $histRows = $db->table('tb_ecp_ekspor')
                           ->where('nama_perusahaan', $namaPerusahaan)
                           ->orderBy('id_ecp', 'ASC')
                           ->get()
                           ->getResultArray();

            $histories = [];
            $totalNilai = 0.0;

            foreach ($histRows as $h) {
                $usd = $h['nilai_ekspor_usd'] !== null ? (float)$h['nilai_ekspor_usd'] : 0.0;
                $totalNilai += $usd;

                $histories[] = [
                    'tahun'            => $h['tahun'] ?? '-',
                    'produk'           => !empty($h['produk_ekspor']) ? $h['produk_ekspor'] : ($h['produk_dipasarkan'] ?: '-'),
                    'negara_tujuan'    => $h['negara_tujuan'] ?: '-',
                    'jenis_ekspor'     => $h['jenis_ekspor'] ?: '-',
                    'kuantitas'        => $h['kuantitas_ekspor'] ?: '-',
                    'nilai_ekspor_usd' => $usd,
                    'nilai_ekspor_raw' => $h['nilai_ekspor_raw'] ?: null,
                ];
            }

            return [
                'source'     => 'ecp',
                'company'    => $company,
                'product'    => $product,
                'histories'  => $histories,
                'totalNilai' => $totalNilai,
            ];
        }

        // SOURCE: SUPPLIER (tb_supplier_produk)
        $sp = $db->table('tb_supplier_produk')->where('id_supplier_produk', $rawId)->get()->getRowArray();
        if (!$sp) {
            return [];
        }

        $namaPerusahaan = $sp['nama_perusahaan'];
        $kunci = strtolower(trim(preg_replace('/[^a-z0-9]/i', '', $namaPerusahaan)));
        $supplierExtra = $db->table('tb_supplier')->where('nama_kunci', $kunci)->get()->getRowArray();

        $company = [
            'nama_perusahaan'  => $namaPerusahaan,
            'domisili'         => $sp['lokasi'] ?: ($supplierExtra['domisili_usaha'] ?? '-'),
            'wilayah_ecp'      => $sp['lokasi'] ?: '-',
            'lokasi_pabrik'    => $supplierExtra['lokasi_pabrik_gudang'] ?? '-',
            'nama_pic'         => $sp['nama_pic'] ?: ($supplierExtra['nama_kontak'] ?? '-'),
            'email'            => $sp['email_perusahaan'] ?: ($sp['email_pengirim'] ?: ($supplierExtra['email'] ?? '-')),
            'no_hp'            => $sp['no_hp'] ?: ($supplierExtra['no_hp'] ?? '-'),
            'website'          => $sp['website'] ?: ($supplierExtra['website'] ?? '-'),
            'instagram'        => $sp['instagram'] ?: ($supplierExtra['instagram'] ?? '-'),
            'jenis_perusahaan' => $sp['jenis_usaha'] ?: ($supplierExtra['jenis_perusahaan'] ?? '-'),
            'omset'            => '-',
            'jumlah_pegawai'   => $supplierExtra['jumlah_pegawai'] ?? '-',
            'legalitas'        => $sp['legalitas'] ?: ($supplierExtra['legalitas'] ?? '-'),
            'sertifikasi'      => $sp['sertifikasi'] ?: ($supplierExtra['sertifikasi'] ?? '-'),
        ];

        $photoUrl = SupplierProdukModel::extractPhotoUrl($sp['foto_produk'] ?? null);

        $product = [
            'id'                 => 'sp-' . $sp['id_supplier_produk'],
            'kode_produk'        => SupplierProductModel::getKodeProduk((int)$sp['id_supplier_produk']),
            'nama_produk'        => $sp['nama_produk'] ?: 'Produk Supplier',
            'produk_dipasarkan'  => $sp['nama_produk'] ?: '-',
            'kategori_produk'    => $sp['jenis_usaha'] ?: 'Supplier',
            'merk_dagang'        => '-',
            'kapasitas_produksi' => $sp['spesifikasi_kapasitas'] ?: '-',
            'deskripsi'          => $sp['spesifikasi_kapasitas'] ?: '-',
            'kuantitas_ekspor'   => '-',
            'nilai_ekspor_usd'   => null,
            'foto_url'           => $photoUrl,
            'foto_raw'           => $sp['foto_produk'] ?? null,
            'legalitas'          => $sp['legalitas'] ?: '-',
            'sertifikasi'        => $sp['sertifikasi'] ?: '-',
            'harga_moq'          => $sp['harga_moq'] ?: '-',
            'komisi'             => $sp['komisi'] ?: '-',
            'pernah_ekspor'      => $sp['pernah_ekspor'] ?: '-',
            'keunggulan'         => $supplierExtra['keunggulan_produk'] ?? '-',
        ];

        // Cari histori ekspor dari tb_history_export_supplier
        $histories = [];
        $totalNilai = 0.0;

        if (!empty($supplierExtra['id_supplier'])) {
            $hRows = $db->table('tb_history_export_supplier')
                        ->where('id_supplier', (int)$supplierExtra['id_supplier'])
                        ->get()
                        ->getResultArray();
            foreach ($hRows as $h) {
                $usd = $h['nilai_ekspor_usd'] !== null ? (float)$h['nilai_ekspor_usd'] : 0.0;
                $totalNilai += $usd;
                $histories[] = [
                    'tahun'            => $h['tahun_ekspor'] ?? ($h['tahun'] ?? '-'),
                    'produk'           => $h['produk'] ?: '-',
                    'negara_tujuan'    => $h['negara_tujuan'] ?: '-',
                    'jenis_ekspor'     => $h['jenis_ekspor'] ?: '-',
                    'kuantitas'        => $h['kuantitas'] ?: '-',
                    'nilai_ekspor_usd' => $usd,
                    'nilai_ekspor_raw' => $h['nilai_ekspor_asli'] ?: null,
                ];
            }
        }

        return [
            'source'     => 'supplier',
            'company'    => $company,
            'product'    => $product,
            'histories'  => $histories,
            'totalNilai' => $totalNilai,
        ];
    }

    /**
     * Mengambil logo resmi Indonesian Exporter sebagai data URI base64.
     */
    private function getBase64Logo(): ?string
    {
        $logoPath = FCPATH . 'assets/img/logo.png';
        if (is_file($logoPath)) {
            $raw = @file_get_contents($logoPath);
            if ($raw !== false && !empty($raw)) {
                return 'data:image/png;base64,' . base64_encode($raw);
            }
        }
        return null;
    }

    /**
     * Mengubah URL/path gambar produk menjadi base64 data URI untuk Dompdf.
     */
    private function resolveBase64Image(?string $fotoRaw): ?string
    {
        if (empty($fotoRaw)) {
            return null;
        }

        $fotoRaw = trim($fotoRaw);

        // Jika file lokal ada
        $cleanPath = ltrim($fotoRaw, '/\\');
        $localCandidates = [
            FCPATH . $cleanPath,
            FCPATH . 'assets/img/produk/' . $cleanPath,
            FCPATH . 'uploads/supplier/' . $cleanPath,
        ];

        foreach ($localCandidates as $loc) {
            if (is_file($loc)) {
                $raw = @file_get_contents($loc);
                if ($raw !== false && !empty($raw)) {
                    $mime = mime_content_type($loc) ?: 'image/jpeg';
                    return 'data:' . $mime . ';base64,' . base64_encode($raw);
                }
            }
        }

        // Jika Google Drive / URL eksternal
        $thumbUrl = SupplierProdukModel::extractPhotoUrl($fotoRaw);
        if ($thumbUrl) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'header'  => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ]
            ]);
            $raw = @file_get_contents($thumbUrl, false, $ctx);
            if ($raw !== false && !empty($raw)) {
                return 'data:image/jpeg;base64,' . base64_encode($raw);
            }
        }

        return null;
    }

    /**
     * Menambahkan penomoran halaman pada canvas Dompdf.
     */
    private function addPageNumbersToCanvas(Dompdf $dompdf): void
    {
        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
        $w = $canvas->get_width();
        $h = $canvas->get_height();
        $canvas->page_text($w / 2 - 50, $h - 25, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, 9, [0.4, 0.45, 0.5]);
    }
}
