<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\SupplierProductModel;
use App\Models\SupplierModel;
use App\Models\SupplierExportHistoryModel;
use App\Models\ProductModel;
use App\Models\ProdukSalesModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;
use Dompdf\Options;

class Produk extends BaseController
{
    protected $helpers = ['url'];

    public function index()
    {
        $modeInfo = $this->resolveSalesMode();
        $isInternal = $modeInfo['is_internal'];
        $isPreview = $modeInfo['is_preview'];
        $accountTipe = $modeInfo['account_tipe'];

        $productSalesModel = new ProdukSalesModel();
        $q = trim((string)($this->request->getGet('q') ?? ''));
        $page = (int)($this->request->getGet('page') ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        $perPage = 16;

        // Ambil data produk terpadu 127 produk (10 Supplier IE + 117 ECP)
        $allItems = $productSalesModel->getUnifiedProducts('');

        if ($q !== '') {
            $qLower = strtolower($q);
            $filtered = [];
            foreach ($allItems as $item) {
                $kode = strtolower($item['kode_produk'] ?? '');
                $nama = strtolower($item['nama_produk'] ?? '');
                $lokasi = strtolower($item['lokasi_barang'] ?? ($item['domisili_usaha'] ?? ''));
                $spesifikasi = strtolower($item['spesifikasi'] ?? '');

                $match = (strpos($kode, $qLower) !== false ||
                          strpos($nama, $qLower) !== false ||
                          strpos($lokasi, $qLower) !== false ||
                          strpos($spesifikasi, $qLower) !== false);

                // Mode Internal boleh mencari nama perusahaan
                if ($isInternal) {
                    $perusahaan = strtolower($item['nama_perusahaan'] ?? '');
                    if (!$match && strpos($perusahaan, $qLower) !== false) {
                        $match = true;
                    }
                }

                if ($match) {
                    $filtered[] = $item;
                }
            }
            $allItems = $filtered;
        }

        $totalRows = count($allItems);

        // Potong data sesuai halaman aktif (16 per halaman)
        $products = array_slice($allItems, ($page - 1) * $perPage, $perPage);

        // Inisialisasi pager CI4 agar links('default', 'pager_rapi') berjalan normal
        $pager = \Config\Services::pager();
        $pager->store('default', $page, $perPage, $totalRows);

        $startRow = $totalRows > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $endRow   = min($page * $perPage, $totalRows);

        // PENYARINGAN SATU TEMPAT: Mode eksternal WAJIB memanggil maskForExternal sebelum data dikirim ke view
        if (!$isInternal) {
            $komisiMap = array_column($products, 'komisi', 'id_produk_supplier');
            $products = SupplierProductModel::maskForExternal($products);
            foreach ($products as &$p) {
                $prodId = $p['id_produk_supplier'] ?? ($p['id_produk'] ?? 0);
                if (array_key_exists($prodId, $komisiMap)) {
                    $p['komisi'] = $komisiMap[$prodId];
                }
            }
            unset($p);
        }

        $data = [
            'title'        => 'Produk Sales - Indonesian Exporter',
            'page_title'   => 'Produk',
            'activeMenu'   => 'produk',
            'products'     => $products,
            'q'            => $q,
            'is_internal'  => $isInternal,
            'is_preview'   => $isPreview,
            'account_tipe' => $accountTipe,
            'pager'        => $pager,
            'totalRows'    => $totalRows,
            'startRow'     => $startRow,
            'endRow'       => $endRow,
        ];

        return view('sales/produk/index', $data);
    }

    public function detail($id = null)
    {
        if ($id === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $modeInfo = $this->resolveSalesMode();
        $isInternal = $modeInfo['is_internal'];
        $isPreview = $modeInfo['is_preview'];
        $accountTipe = $modeInfo['account_tipe'];

        $detailData = $this->buildDetailData((int)$id);
        if (empty($detailData)) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan');
        }

        // PENYARINGAN SATU TEMPAT: Mode eksternal WAJIB memanggil maskForExternal sebelum data dikirim ke view
        if (!$isInternal) {
            $detailData = SupplierProductModel::maskForExternal($detailData);
        }

        $productTitle = $detailData['product']['nama_produk_id'] ?? 'Detail Produk';

        $data = array_merge([
            'title'        => $productTitle . ' - Area Sales',
            'page_title'   => 'Detail Produk',
            'activeMenu'   => 'produk',
            'is_internal'  => $isInternal,
            'is_preview'   => $isPreview,
            'account_tipe' => $accountTipe,
        ], $detailData);

        return view('sales/produk/detail', $data);
    }

    public function detailPdf($id = null)
    {
        if ($id === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $modeInfo = $this->resolveSalesMode();
        $accountTipe = $modeInfo['account_tipe'];
        $isPreview = $modeInfo['is_preview'];

        // Tentukan mode PDF:
        // Akun internal mendapat dua pilihan: "PDF Eksternal (aman dikirim ke buyer)" sebagai default dan "PDF Internal";
        // Akun eksternal HANYA mendapat PDF Eksternal dan parameter lain diabaikan.
        $pdfType = strtolower(trim((string)$this->request->getGet('tipe_pdf')));
        $isPdfInternal = false;

        if ($accountTipe === 'internal' && !$isPreview && $pdfType === 'internal') {
            $isPdfInternal = true;
        } else {
            $isPdfInternal = false;
        }

        $detailData = $this->buildDetailData((int)$id);
        if (empty($detailData)) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan');
        }

        if (!$isPdfInternal) {
            $detailData = SupplierProductModel::maskForExternal($detailData);
        }

        $rawSlug = $detailData['product']['nama_produk_id'] ?? ('item-' . $id);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $rawSlug), '-'));
        if (empty($slug)) {
            $slug = (string)$id;
        }

        // Nama file PDF eksternal tidak boleh memuat nama perusahaan
        if ($isPdfInternal) {
            $filename = 'produk-' . $slug . '-internal-' . date('Ymd') . '.pdf';
        } else {
            $kodeProduk = SupplierProductModel::getKodeProduk((int)$id);
            $filename = 'produk-' . strtolower($kodeProduk) . '-' . $slug . '-' . date('Ymd') . '.pdf';
        }

        $this->renderPdf([$detailData], $filename, $isPdfInternal);
    }

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

        $productSalesModel = new ProdukSalesModel();
        $q = trim((string)($this->request->getGet('q') ?? ''));

        $allItems = $productSalesModel->getUnifiedProducts('');

        if ($q !== '') {
            $qLower = strtolower($q);
            $filtered = [];
            foreach ($allItems as $item) {
                $kode = strtolower($item['kode_produk'] ?? '');
                $nama = strtolower($item['nama_produk'] ?? '');
                $lokasi = strtolower($item['lokasi_barang'] ?? ($item['domisili_usaha'] ?? ''));
                $spesifikasi = strtolower($item['spesifikasi'] ?? '');

                $match = (strpos($kode, $qLower) !== false ||
                          strpos($nama, $qLower) !== false ||
                          strpos($lokasi, $qLower) !== false ||
                          strpos($spesifikasi, $qLower) !== false);

                if ($isPdfInternal) {
                    $perusahaan = strtolower($item['nama_perusahaan'] ?? '');
                    if (!$match && strpos($perusahaan, $qLower) !== false) {
                        $match = true;
                    }
                }

                if ($match) {
                    $filtered[] = $item;
                }
            }
            $allItems = $filtered;
        }

        $count = count($allItems);

        $redirectQuery = [];
        if ($q !== '') {
            $redirectQuery['q'] = $q;
        }
        if ($isPreview) {
            $redirectQuery['mode'] = 'eksternal';
        }
        $redirectUrl = base_url('sales/produk') . (!empty($redirectQuery) ? '?' . http_build_query($redirectQuery) : '');

        if ($count > 50) {
            return redirect()->to($redirectUrl)->with('error', 'Persempit pencarian (maksimal 50 produk per PDF)');
        }

        if ($count === 0) {
            return redirect()->to($redirectUrl)->with('error', 'Tidak ada produk yang ditemukan untuk diekspor');
        }

        $items = [];
        foreach ($allItems as $row) {
            $data = $this->buildDetailData((int)$row['id_produk_supplier']);
            if (!empty($data)) {
                if (!$isPdfInternal) {
                    $data = SupplierProductModel::maskForExternal($data);
                }
                $items[] = $data;
            }
        }

        if (empty($items)) {
            return redirect()->to($redirectUrl)->with('error', 'Tidak ada produk yang dapat diekspor');
        }

        if ($isPdfInternal) {
            $filename = 'katalog-produk-internal-' . date('Ymd') . '.pdf';
        } else {
            $filename = 'katalog-produk-' . date('Ymd') . '.pdf';
        }

        $this->renderPdf($items, $filename, $isPdfInternal);
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

    private function buildDetailData(int $id): array
    {
        // 1. Data dari tb_ecp_ekspor (virtual ID 10001 - 10117)
        if ($id > 10000) {
            $idEcp = $id - 10000;
            $db = \Config\Database::connect();
            $ecp = $db->table('tb_ecp_ekspor')->where('id_ecp', $idEcp)->get()->getRowArray();
            if ($ecp) {
                $kodeProduk = 'ECP-' . str_pad((string)$idEcp, 4, '0', STR_PAD_LEFT);
                $namaProduk = !empty($ecp['produk_ekspor']) ? $ecp['produk_ekspor'] : (!empty($ecp['produk_dipasarkan']) ? $ecp['produk_dipasarkan'] : 'Produk Ekspor');
                $namaPerusahaan = !empty($ecp['nama_perusahaan']) ? $ecp['nama_perusahaan'] : '-';
                $lokasi = !empty($ecp['domisili']) ? $ecp['domisili'] : (!empty($ecp['wilayah_ecp']) ? $ecp['wilayah_ecp'] : '-');
                $spesifikasi = !empty($ecp['kategori_produk']) ? ('Kategori: ' . $ecp['kategori_produk'] . (!empty($ecp['merk_dagang']) ? ' | Merk: ' . $ecp['merk_dagang'] : '')) : ($ecp['produk_dipasarkan'] ?: '');
                $kapasitas = !empty($ecp['kapasitas_produksi']) ? $ecp['kapasitas_produksi'] : (!empty($ecp['kuantitas_ekspor']) ? 'Ekspor: ' . $ecp['kuantitas_ekspor'] : '');
                $moq = !empty($ecp['kuantitas_ekspor']) ? $ecp['kuantitas_ekspor'] : '';
                $nilaiUsd = isset($ecp['nilai_ekspor_usd']) && is_numeric($ecp['nilai_ekspor_usd']) ? (float)$ecp['nilai_ekspor_usd'] : 0.0;
                $hargaExw = ($nilaiUsd > 0) ? ('Ekspor: $' . number_format($nilaiUsd, 0, ',', '.') . ' USD') : '';

                $defaultImg = 'assets/img/logo.png';
                $fotos = [base_url($defaultImg)];

                $product = [
                    'id_produk'           => $id,
                    'kode_produk'         => $kodeProduk,
                    'nama_produk_id'      => $namaProduk,
                    'nama_produk_en'      => $namaPerusahaan,
                    'nama_kategori_id'    => !empty($ecp['kategori_produk']) ? $ecp['kategori_produk'] : 'Produk Ekspor',
                    'nama_kategori_en'    => !empty($ecp['kategori_produk']) ? $ecp['kategori_produk'] : 'Export Product',
                    'foto_produk'         => $defaultImg,
                    'deskripsi_produk_id' => $spesifikasi,
                    'deskripsi_produk_en' => $spesifikasi,
                ];

                $produkSupplier = [
                    'id_produk_supplier' => $id,
                    'id_supplier'        => null,
                    'nama_produk'        => $namaProduk,
                    'spesifikasi'        => $spesifikasi,
                    'kapasitas_produksi' => $kapasitas,
                    'legalitas'          => null,
                    'sertifikasi'        => null,
                    'lokasi_barang'      => $lokasi,
                    'moq'                => $moq,
                    'harga_exw'          => $hargaExw,
                    'komisi'             => null,
                    'sudah_ekspor'       => 'Sudah',
                    'kode_produk'        => $kodeProduk,
                ];

                $supplier = [
                    'id_supplier'               => null,
                    'nama_perusahaan'           => $namaPerusahaan,
                    'nama_kontak'               => $ecp['nama_peserta'] ?? '',
                    'no_hp'                     => $ecp['no_hp'] ?? '',
                    'email'                     => $ecp['email'] ?? '',
                    'website'                   => $ecp['website'] ?? '',
                    'instagram'                 => $ecp['instagram'] ?? '',
                    'domisili_usaha'            => $lokasi,
                    'jenis_perusahaan'          => $ecp['jenis_perusahaan'] ?? '',
                    'kapasitas_produksi'        => $kapasitas,
                    'produk_dipasarkan'         => $ecp['produk_dipasarkan'] ?? '',
                    'jumlah_pegawai'            => '',
                    'frekuensi_ekspor'          => '',
                    'pameran_internasional'     => '',
                    'keunggulan_produk'         => '',
                    'legalitas'                 => '',
                    'sertifikasi'               => '',
                    'pernah_ekspor'             => 'Sudah',
                    'kategori_ekspor'           => $ecp['kategori_produk'] ?? '',
                    'ringkasan_ekspor_tanggal'  => !empty($ecp['tahun']) ? (string)$ecp['tahun'] : '',
                    'ringkasan_ekspor_produk'   => !empty($ecp['produk_ekspor']) ? $ecp['produk_ekspor'] : '',
                    'ringkasan_ekspor_kapasitas'=> !empty($ecp['kuantitas_ekspor']) ? $ecp['kuantitas_ekspor'] : '',
                    'ringkasan_ekspor_negara'   => !empty($ecp['negara_tujuan']) ? $ecp['negara_tujuan'] : '',
                    'ringkasan_ekspor_nilai'    => ($nilaiUsd > 0) ? ('$' . number_format($nilaiUsd, 0, ',', '.') . ' USD') : '',
                    'kode_produk'               => $kodeProduk,
                ];

                $histories = [];
                if (!empty($ecp['tahun']) || !empty($ecp['negara_tujuan'])) {
                    $histories[] = [
                        'tahun'            => !empty($ecp['tahun']) ? (string)$ecp['tahun'] : '-',
                        'produk'           => !empty($ecp['produk_ekspor']) ? $ecp['produk_ekspor'] : ($ecp['produk_dipasarkan'] ?? '-'),
                        'negara_tujuan'    => !empty($ecp['negara_tujuan']) ? $ecp['negara_tujuan'] : '-',
                        'jenis_ekspor'     => !empty($ecp['jenis_ekspor']) ? $ecp['jenis_ekspor'] : '-',
                        'kuantitas'        => !empty($ecp['kuantitas_ekspor']) ? $ecp['kuantitas_ekspor'] : '-',
                        'nilai_ekspor_usd' => ($nilaiUsd > 0) ? $nilaiUsd : null,
                        'catatan'          => !empty($ecp['wilayah_ecp']) ? ('ECP: ' . $ecp['wilayah_ecp']) : '',
                    ];
                }

                $years = !empty($ecp['tahun']) ? [(int)$ecp['tahun']] : [];
                $totalNilai = $nilaiUsd;

                return [
                    'product'        => $product,
                    'produkSupplier' => $produkSupplier,
                    'supplier'       => $supplier,
                    'histories'      => $histories,
                    'years'          => $years,
                    'totalNilai'     => $totalNilai,
                    'fotos'          => $fotos,
                ];
            }
        }

        $supplierProductModel = new SupplierProductModel();
        $produkSupplier = $supplierProductModel->find($id);

        $supplierModel = new SupplierModel();
        $historyModel = new SupplierExportHistoryModel();

        if ($produkSupplier) {
            $supplier = $supplierModel->find($produkSupplier['id_supplier']);
            $histories = $historyModel->where('id_supplier', $produkSupplier['id_supplier'])
                ->orderBy('tahun', 'DESC')
                ->orderBy('nilai_ekspor_usd', 'DESC')
                ->findAll();

            $years = [];
            $totalNilai = 0.0;
            foreach ($histories as $h) {
                if (!empty($h['tahun']) && !in_array((int)$h['tahun'], $years, true)) {
                    $years[] = (int)$h['tahun'];
                }
                if (isset($h['nilai_ekspor_usd']) && is_numeric($h['nilai_ekspor_usd'])) {
                    $totalNilai += (float)$h['nilai_ekspor_usd'];
                }
            }
            rsort($years);

            $fotos = SupplierProductModel::photos($produkSupplier);
            $defaultImg = 'assets/img/logo.png';
            $foto = !empty($fotos) ? $fotos[0] : $defaultImg;

            $kodeProduk = SupplierProductModel::getKodeProduk((int)$produkSupplier['id_produk_supplier']);
            $produkSupplier['kode_produk'] = $kodeProduk;
            if ($supplier) {
                $supplier['kode_produk'] = $kodeProduk;
            }

            $product = [
                'id_produk'           => $produkSupplier['id_produk_supplier'],
                'kode_produk'         => $kodeProduk,
                'nama_produk_id'      => $produkSupplier['nama_produk'],
                'nama_produk_en'      => $supplier['nama_perusahaan'] ?? '',
                'nama_kategori_id'    => 'Produk Supplier',
                'nama_kategori_en'    => 'Supplier Product',
                'foto_produk'         => $foto,
                'deskripsi_produk_id' => $produkSupplier['spesifikasi'] ?? '-',
                'deskripsi_produk_en' => $produkSupplier['spesifikasi'] ?? '-',
            ];
        } else {
            // Fallback jika id berasal dari tb_produk
            $productModel = new ProductModel();
            $product = $productModel->select('tb_produk.*, tb_kategori_produk.nama_kategori_id, tb_kategori_produk.nama_kategori_en')
                ->join('tb_kategori_produk', 'tb_kategori_produk.id_kategori_produk = tb_produk.id_kategori_produk', 'left')
                ->where('tb_produk.id_produk', $id)
                ->first();

            if (!$product) {
                return [];
            }

            $kodeProduk = SupplierProductModel::getKodeProduk($id);
            $product['kode_produk'] = $kodeProduk;

            $fotos = [];
            $legacyUrl = SupplierProductModel::photoUrl($product, 1);
            if ($legacyUrl !== null) {
                $fotos[] = $legacyUrl;
            }

            $produkSupplier = null;   // array|null: data produk dari tb_produk_supplier
            $supplier       = null;   // array|null: data perusahaan dari tb_supplier
            $histories      = [];     // array: baris tb_history_export_supplier
            $years          = [];     // array tahun yang tersedia
            $totalNilai     = 0.0;
        }

        return [
            'product'        => $product,
            'produkSupplier' => $produkSupplier,
            'supplier'       => $supplier,
            'histories'      => $histories,
            'years'          => $years,
            'totalNilai'     => $totalNilai,
            'fotos'          => $fotos,
        ];
    }

    private function renderPdf(array $items, string $filename, bool $isInternal = false)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        // Siapkan logo aplikasi sebagai data URI
        $logoPath = FCPATH . 'assets/img/logo.png';
        $logoData = null;
        if (is_file($logoPath)) {
            $rawLogo = @file_get_contents($logoPath);
            if ($rawLogo !== false) {
                $logoData = 'data:image/png;base64,' . base64_encode($rawLogo);
            }
        }

        // Siapkan base64 gambar dan sanitasi HTML deskripsi per item produk
        foreach ($items as &$item) {
            $supplierProdId = (int)($item['produkSupplier']['id_produk_supplier'] ?? ($item['product']['id_produk'] ?? 0));
            $fotoFile = $item['product']['foto_produk'] ?? null;
            $item['imageData'] = $this->pdfImageData($supplierProdId, $fotoFile);
            $item['deskripsi_id_sanitized'] = $this->sanitizeHtml($item['product']['deskripsi_produk_id'] ?? '');
            $item['deskripsi_en_sanitized'] = $this->sanitizeHtml($item['product']['deskripsi_produk_en'] ?? '');
        }
        unset($item);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', FCPATH);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');

        $html = view('sales/produk/pdf_template', [
            'items'       => $items,
            'tanggal'     => date('d F Y'),
            'logoData'    => $logoData,
            'is_internal' => $isInternal,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
        $w = $canvas->get_width();
        $h = $canvas->get_height();
        $canvas->page_text($w / 2 - 50, $h - 28, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, 9, [0.4, 0.45, 0.5]);

        $dompdf->stream($filename, ['Attachment' => false]);
        exit;
    }

    private function pdfImageData(int $supplierProductId = 0, ?string $legacyFile = null): ?string
    {
        $path = null;

        // Pakai HANYA file lokal uploads/supplier/{id}_1.jpg
        if ($supplierProductId > 0) {
            $supplierLocal = FCPATH . 'uploads/supplier/' . $supplierProductId . '_1.jpg';
            if (is_file($supplierLocal)) {
                $path = $supplierLocal;
            }
        }

        // Fallback untuk produk lokal non-supplier (tb_produk) jika berupa file lokal di disk
        if ($path === null && $legacyFile !== null && trim($legacyFile) !== '') {
            $legacyTrimmed = trim($legacyFile);
            if (!str_starts_with($legacyTrimmed, 'http://') && !str_starts_with($legacyTrimmed, 'https://')) {
                if (is_file(FCPATH . 'assets/img/produk/' . $legacyTrimmed)) {
                    $path = FCPATH . 'assets/img/produk/' . $legacyTrimmed;
                } elseif (is_file(FCPATH . ltrim($legacyTrimmed, '/\\'))) {
                    $path = FCPATH . ltrim($legacyTrimmed, '/\\');
                }
            }
        }

        if ($path === null || !is_file($path)) {
            return null;
        }

        try {
            $raw = @file_get_contents($path);
            if ($raw === false || empty($raw)) {
                return null;
            }

            $src = @imagecreatefromstring($raw);
            if (!$src) {
                return null;
            }

            $origWidth = imagesx($src);
            $origHeight = imagesy($src);

            if ($origWidth <= 0 || $origHeight <= 0) {
                imagedestroy($src);
                return null;
            }

            $maxWidth = 800;
            if ($origWidth > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int)round(($origHeight / $origWidth) * $maxWidth);
            } else {
                $newWidth = $origWidth;
                $newHeight = $origHeight;
            }

            $dst = imagecreatetruecolor($newWidth, $newHeight);
            if (!$dst) {
                imagedestroy($src);
                return null;
            }

            // Latar putih untuk transparansi PNG/WebP/GIF
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

            ob_start();
            imagejpeg($dst, null, 80);
            $jpegData = ob_get_clean();

            imagedestroy($src);
            imagedestroy($dst);

            if (!$jpegData) {
                return null;
            }

            return 'data:image/jpeg;base64,' . base64_encode($jpegData);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function sanitizeHtml(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $whitelist = '<p><br><ul><ol><li><strong><b><em><i><h3><h4><table><thead><tbody><tr><th><td>';
        $cleaned = strip_tags($html, $whitelist);

        // Hapus atribut style, class, dan on*
        $cleaned = preg_replace('/\s*(?:style|class|on[a-z]+)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $cleaned);

        return trim($cleaned ?? '');
    }
}
