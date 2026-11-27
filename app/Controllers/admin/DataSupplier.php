<?php

namespace App\Controllers\admin;

use App\Models\ProdukSalesModel;
use App\Models\SupplierModel;
use Config\Database;

class DataSupplier extends AdminAuthBase
{
    private const MAX_BULK_ROWS = 300;

    /**
     * Batas panjang kolom sesuai skema tb_supplier.
     *
     * @var array<string, array{max: int, label: string}>
     */
    private const FIELD_LIMITS = [
        'nama_perusahaan'  => ['max' => 255, 'label' => 'Nama perusahaan'],
        'nama_kontak'      => ['max' => 150, 'label' => 'Nama kontak'],
        'email'            => ['max' => 150, 'label' => 'Email'],
        'no_hp'            => ['max' => 30,  'label' => 'No. HP'],
        'website'          => ['max' => 255, 'label' => 'Website'],
        'instagram'        => ['max' => 255, 'label' => 'Instagram'],
        'domisili_usaha'   => ['max' => 255, 'label' => 'Domisili usaha'],
        'jenis_perusahaan' => ['max' => 50,  'label' => 'Jenis perusahaan'],
        'sumber_tahun'     => ['max' => 20,  'label' => 'Sumber tahun'],
    ];

    /**
     * Menampilkan daftar semua supplier dan produk-produknya yang tampil di katalog sales.
     */
    public function index()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $db = Database::connect();

        // 1. Ambil semua supplier dari tb_supplier (id_supplier, nama_perusahaan, nama_kunci), urut nama_perusahaan ASC
        $suppliers = $db->table('tb_supplier')
            ->select('id_supplier, nama_perusahaan, nama_kunci')
            ->orderBy('nama_perusahaan', 'ASC')
            ->get()
            ->getResultArray();

        // 2. Ambil semua item katalog dari method baru di ProdukSalesModel
        $produkSalesModel = new ProdukSalesModel();
        $catalogItems = $produkSalesModel->getAllCatalogItems();

        // 3. Kelompokkan item per supplier di PHP (tanpa query di dalam perulangan)
        $supplierById = [];
        $supplierByKey = [];
        $supplierProducts = [];

        foreach ($suppliers as $sup) {
            $sid = (int)$sup['id_supplier'];
            $supplierById[$sid] = $sup;
            $key = !empty($sup['nama_kunci']) ? $sup['nama_kunci'] : preg_replace('/[^a-z0-9]/', '', strtolower($sup['nama_perusahaan']));
            $supplierByKey[$key] = $sid;
            $supplierProducts[$sid] = [];
        }

        $unmatchedProducts = [];

        foreach ($catalogItems as $item) {
            $matchedSid = null;
            if (($item['sumber'] ?? '') === 'supplier') {
                $sid = !empty($item['id_supplier']) ? (int)$item['id_supplier'] : null;
                if ($sid && isset($supplierById[$sid])) {
                    $matchedSid = $sid;
                }
            } else {
                // Sumber ECP: cocokkan nama perusahaan ternormalisasi dengan tb_supplier.nama_kunci
                $rawPerusahaan = $item['nama_perusahaan'] ?? '';
                $normKey = preg_replace('/[^a-z0-9]/', '', strtolower($rawPerusahaan));
                if (isset($supplierByKey[$normKey])) {
                    $matchedSid = $supplierByKey[$normKey];
                }
            }

            if ($matchedSid !== null) {
                // Tautan ke admin edit hanya untuk produk terdaftar (tb_produk_supplier)
                $link = null;
                if (($item['sumber'] ?? '') === 'supplier' && !empty($item['id_produk_supplier'])) {
                    $link = base_url('admin/produk_sales/edit/' . (int)$item['id_produk_supplier']);
                }

                $supplierProducts[$matchedSid][] = [
                    'id_produk_supplier' => $item['id_produk_supplier'] ?? 0,
                    'nama_produk'        => $item['nama_produk'] ?? '',
                    'foto_url'           => $item['foto_url'] ?? null,
                    'sumber'             => $item['sumber'] ?? 'ecp',
                    'link'               => $link,
                ];
            } else {
                $unmatchedProducts[] = $item;
            }
        }

        return view('admin/data_supplier/index', [
            'suppliers'            => $suppliers,
            'supplierProducts'     => $supplierProducts,
            'unmatchedProducts'    => $unmatchedProducts,
            'totalCatalogProducts' => count($catalogItems),
        ]);
    }

    /**
     * Menambahkan satu perusahaan supplier baru ke tb_supplier.
     * Route: POST admin/data-supplier/tambah-perusahaan
     */
    public function tambahPerusahaan()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $fields = [
            'nama_perusahaan'  => SupplierModel::cleanText((string)($this->request->getPost('nama_perusahaan') ?? '')),
            'nama_kontak'      => SupplierModel::cleanText((string)($this->request->getPost('nama_kontak') ?? '')),
            'email'            => SupplierModel::cleanText((string)($this->request->getPost('email') ?? '')),
            'no_hp'            => SupplierModel::cleanText((string)($this->request->getPost('no_hp') ?? '')),
            'website'          => SupplierModel::cleanText((string)($this->request->getPost('website') ?? '')),
            'instagram'        => SupplierModel::cleanText((string)($this->request->getPost('instagram') ?? '')),
            'domisili_usaha'   => SupplierModel::cleanText((string)($this->request->getPost('domisili_usaha') ?? '')),
            'jenis_perusahaan' => SupplierModel::cleanText((string)($this->request->getPost('jenis_perusahaan') ?? '')),
            'sumber_tahun'     => SupplierModel::cleanText((string)($this->request->getPost('sumber_tahun') ?? '')),
        ];

        $validation = $this->validateSupplierFields($fields);
        if (!$validation['valid']) {
            return redirect()->to(base_url('admin/data-supplier'))
                ->withInput()
                ->with('error_single', implode(' ', $validation['errors']))
                ->with('open_modal', 'single');
        }

        $supplierModel = new SupplierModel();
        $existingMap   = $supplierModel->getExistingKeyMap();
        $namaKunci     = $validation['nama_kunci'];

        if (isset($existingMap[$namaKunci])) {
            return redirect()->to(base_url('admin/data-supplier'))
                ->withInput()
                ->with('error_single', 'Perusahaan sudah ada: ' . $existingMap[$namaKunci])
                ->with('open_modal', 'single');
        }

        try {
            $insertedId = $supplierModel->insert($validation['record'], true);
            if (!$insertedId) {
                return redirect()->to(base_url('admin/data-supplier'))
                    ->withInput()
                    ->with('error_single', 'Gagal menyimpan data perusahaan.')
                    ->with('open_modal', 'single');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Gagal tambah satu perusahaan supplier: ' . $e->getMessage());
            return redirect()->to(base_url('admin/data-supplier'))
                ->withInput()
                ->with('error_single', 'Terjadi kesalahan saat menyimpan data perusahaan.')
                ->with('open_modal', 'single');
        }

        return redirect()->to(base_url('admin/data-supplier'))
            ->with('success', 'Perusahaan "' . $validation['record']['nama_perusahaan'] . '" berhasil ditambahkan.');
    }

    /**
     * Memeriksa data tempelan Excel tanpa menulis ke database (Langkah 1 Tambah Banyak).
     * Route: POST admin/data-supplier/banyak-periksa
     */
    public function banyakPeriksa()
    {
        if ($redirect = $this->checkAuth()) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'ok'      => false,
                    'message' => 'Sesi admin telah berakhir. Silakan muat ulang halaman dan login kembali.',
                ]);
            }
            return $redirect;
        }

        $rawData     = (string)($this->request->getPost('raw_data') ?? '');
        $sumberTahun = (string)($this->request->getPost('sumber_tahun') ?? '');

        $supplierModel = new SupplierModel();
        $existingMap   = $supplierModel->getExistingKeyMap();
        $analysis      = $this->analyzeBulkPaste($rawData, $sumberTahun, $existingMap);

        $payload = [
            'ok'         => $analysis['ok'],
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ];

        if (!$analysis['ok']) {
            $payload['message'] = $analysis['message'];
            return $this->response->setStatusCode(422)->setJSON($payload);
        }

        $payload['header_skipped'] = $analysis['header_skipped'];
        $payload['summary']        = $analysis['summary'];
        $payload['rows']           = array_map(static function (array $row): array {
            return [
                'row_number'      => $row['row_number'],
                'nama_perusahaan' => $row['nama_perusahaan'],
                'status_code'     => $row['status_code'],
                'status_label'    => $row['status_label'],
                'reason'          => $row['reason'],
            ];
        }, $analysis['rows']);

        return $this->response->setJSON($payload);
    }

    /**
     * Memvalidasi ulang seluruh data tempelan dan menyimpan baris Baru dalam satu transaksi database.
     * Route: POST admin/data-supplier/banyak-simpan
     */
    public function banyakSimpan()
    {
        if ($redirect = $this->checkAuth()) {
            return $redirect;
        }

        $rawData     = (string)($this->request->getPost('raw_data') ?? '');
        $sumberTahun = (string)($this->request->getPost('sumber_tahun') ?? '');

        $supplierModel = new SupplierModel();
        $existingMap   = $supplierModel->getExistingKeyMap();
        $analysis      = $this->analyzeBulkPaste($rawData, $sumberTahun, $existingMap);

        if (!$analysis['ok']) {
            return redirect()->to(base_url('admin/data-supplier'))
                ->with('error', $analysis['message']);
        }

        $rowsToInsert = [];
        $skippedItems = [];

        foreach ($analysis['rows'] as $row) {
            if ($row['status_code'] === 'new') {
                $rowsToInsert[] = $row;
            } else {
                $skippedItems[] = [
                    'row_number'      => $row['row_number'],
                    'nama_perusahaan' => $row['nama_perusahaan'],
                    'status_label'    => $row['status_label'],
                    'reason'          => $row['reason'],
                ];
            }
        }

        if (empty($rowsToInsert)) {
            return redirect()->to(base_url('admin/data-supplier'))
                ->with('bulk_report', [
                    'saved_count'   => 0,
                    'skipped_count' => count($skippedItems),
                    'summary'       => $analysis['summary'],
                    'skipped_items' => $skippedItems,
                ])
                ->with('error', 'Tidak ada perusahaan baru yang dapat disimpan.');
        }

        $db = Database::connect();
        $db->transBegin();

        try {
            foreach ($rowsToInsert as $item) {
                $insertedId = $supplierModel->insert($item['record'], true);
                if (!$insertedId) {
                    throw new \RuntimeException('Gagal menyimpan baris ' . $item['row_number'] . ' (' . $item['nama_perusahaan'] . ').');
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal dijalankan.');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Gagal simpan banyak supplier (rollback): ' . $e->getMessage());

            return redirect()->to(base_url('admin/data-supplier'))
                ->with('error', 'Penyimpanan dibatalkan (rollback): terjadi kegagalan saat menyimpan data. Tidak ada perusahaan baru yang disimpan.');
        }

        return redirect()->to(base_url('admin/data-supplier'))
            ->with('bulk_report', [
                'saved_count'   => count($rowsToInsert),
                'skipped_count' => count($skippedItems),
                'summary'       => $analysis['summary'],
                'skipped_items' => $skippedItems,
            ]);
    }

    /**
     * Memvalidasi field perusahaan dan membentuk payload insert untuk tb_supplier.
     *
     * @param array<string, string> $fields
     * @return array{valid: bool, errors: list<string>, nama_kunci: string, record: array<string, ?string>}
     */
    private function validateSupplierFields(array $fields): array
    {
        $errors         = [];
        $namaPerusahaan = $fields['nama_perusahaan'] ?? '';
        $namaKunci      = '';

        if ($namaPerusahaan === '') {
            $errors[] = 'Nama perusahaan wajib diisi.';
        } else {
            $namaKunci = SupplierModel::makeKey($namaPerusahaan);
            if ($namaKunci === '') {
                $errors[] = 'Nama perusahaan tidak valid (harus mengandung huruf atau angka selain PT/CV/UD).';
            }
        }

        $email = $fields['email'] ?? '';
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Format email tidak valid.';
        }

        foreach (self::FIELD_LIMITS as $key => $meta) {
            $val = $fields[$key] ?? '';
            if ($val !== '' && mb_strlen($val, 'UTF-8') > $meta['max']) {
                $errors[] = $meta['label'] . ' maksimal ' . $meta['max'] . ' karakter.';
            }
        }

        $record = [
            'nama_perusahaan'  => $namaPerusahaan,
            'nama_kunci'       => $namaKunci,
            'nama_kontak'      => ($fields['nama_kontak'] ?? '') !== '' ? $fields['nama_kontak'] : null,
            'email'            => $email !== '' ? $email : null,
            'no_hp'            => ($fields['no_hp'] ?? '') !== '' ? $fields['no_hp'] : null,
            'website'          => ($fields['website'] ?? '') !== '' ? $fields['website'] : null,
            'instagram'        => ($fields['instagram'] ?? '') !== '' ? $fields['instagram'] : null,
            'domisili_usaha'   => ($fields['domisili_usaha'] ?? '') !== '' ? $fields['domisili_usaha'] : null,
            'jenis_perusahaan' => ($fields['jenis_perusahaan'] ?? '') !== '' ? $fields['jenis_perusahaan'] : null,
            'sumber_tahun'     => ($fields['sumber_tahun'] ?? '') !== '' ? $fields['sumber_tahun'] : null,
        ];

        return [
            'valid'      => empty($errors),
            'errors'     => $errors,
            'nama_kunci' => $namaKunci,
            'record'     => $record,
        ];
    }

    /**
     * Mem-parse dan memvalidasi data tempelan Excel secara menyeluruh tanpa menulis ke DB.
     *
     * @param array<string, string> $existingMap
     * @return array<string, mixed>
     */
    private function analyzeBulkPaste(string $rawData, string $sumberTahun, array $existingMap): array
    {
        $cleanTahun = SupplierModel::cleanText($sumberTahun);
        if ($cleanTahun !== '' && mb_strlen($cleanTahun, 'UTF-8') > self::FIELD_LIMITS['sumber_tahun']['max']) {
            return [
                'ok'      => false,
                'message' => 'Sumber tahun maksimal ' . self::FIELD_LIMITS['sumber_tahun']['max'] . ' karakter.',
            ];
        }

        $rawLines      = preg_split('/\r\n|\r|\n/', $rawData) ?: [];
        $nonEmptyLines = [];
        foreach ($rawLines as $idx => $line) {
            if (trim($line) === '') {
                continue;
            }
            $nonEmptyLines[] = [
                'line_no' => $idx + 1,
                'raw'     => $line,
            ];
        }

        if (empty($nonEmptyLines)) {
            return [
                'ok'      => false,
                'message' => 'Data tempelan kosong. Silakan tempel minimal 1 baris data dari Excel.',
            ];
        }

        $headerSkipped = false;
        $firstCols     = array_map([SupplierModel::class, 'cleanText'], explode("\t", $nonEmptyLines[0]['raw']));
        if ($this->isHeaderRow($firstCols)) {
            array_shift($nonEmptyLines);
            $headerSkipped = true;
        }

        if (empty($nonEmptyLines)) {
            return [
                'ok'      => false,
                'message' => 'Hanya baris judul kolom yang ditemukan. Tidak ada baris data perusahaan.',
            ];
        }

        $totalLines = count($nonEmptyLines);
        if ($totalLines > self::MAX_BULK_ROWS) {
            return [
                'ok'      => false,
                'message' => 'Maksimal ' . self::MAX_BULK_ROWS . ' baris per tempelan. Data Anda berisi ' . $totalLines . ' baris.',
            ];
        }

        $summary = [
            'baru'        => 0,
            'sudah_ada'   => 0,
            'ganda'       => 0,
            'tidak_valid' => 0,
            'total'       => $totalLines,
        ];
        $rows        = [];
        $seenInPaste = [];

        foreach ($nonEmptyLines as $entry) {
            $cols = explode("\t", $entry['raw']);
            $fields = [
                'nama_perusahaan'  => SupplierModel::cleanText($cols[0] ?? ''),
                'nama_kontak'      => SupplierModel::cleanText($cols[1] ?? ''),
                'email'            => SupplierModel::cleanText($cols[2] ?? ''),
                'no_hp'            => SupplierModel::cleanText($cols[3] ?? ''),
                'website'          => SupplierModel::cleanText($cols[4] ?? ''),
                'instagram'        => SupplierModel::cleanText($cols[5] ?? ''),
                'domisili_usaha'   => SupplierModel::cleanText($cols[6] ?? ''),
                'jenis_perusahaan' => SupplierModel::cleanText($cols[7] ?? ''),
                'sumber_tahun'     => $cleanTahun,
            ];

            $validation = $this->validateSupplierFields($fields);
            $displayName = $fields['nama_perusahaan'] !== '' ? $fields['nama_perusahaan'] : '(Kosong)';

            if (!$validation['valid']) {
                $statusCode  = 'invalid';
                $statusLabel = 'Tidak valid';
                $reason      = implode(' ', $validation['errors']);
                $summary['tidak_valid']++;
            } else {
                $namaKunci = $validation['nama_kunci'];
                if (isset($existingMap[$namaKunci])) {
                    $statusCode  = 'exists';
                    $statusLabel = 'Sudah ada';
                    $reason      = 'Sudah ada: ' . $existingMap[$namaKunci];
                    $summary['sudah_ada']++;
                } elseif (isset($seenInPaste[$namaKunci])) {
                    $statusCode  = 'duplicate';
                    $statusLabel = 'Ganda di data yang ditempel';
                    $reason      = 'Sama dengan baris ' . $seenInPaste[$namaKunci]['row_number'] . ' (' . $seenInPaste[$namaKunci]['nama_perusahaan'] . ')';
                    $summary['ganda']++;
                } else {
                    $seenInPaste[$namaKunci] = [
                        'row_number'      => $entry['line_no'],
                        'nama_perusahaan' => $fields['nama_perusahaan'],
                    ];
                    $statusCode  = 'new';
                    $statusLabel = 'Baru';
                    $reason      = 'Siap disimpan';
                    $summary['baru']++;
                }
            }

            $rows[] = [
                'row_number'      => $entry['line_no'],
                'nama_perusahaan' => $displayName,
                'status_code'     => $statusCode,
                'status_label'    => $statusLabel,
                'reason'          => $reason,
                'record'          => $validation['record'],
            ];
        }

        return [
            'ok'             => true,
            'header_skipped' => $headerSkipped,
            'summary'        => $summary,
            'rows'           => $rows,
        ];
    }

    /**
     * Mendeteksi apakah baris pertama tempelan merupakan judul kolom (header).
     *
     * @param list<string> $cols
     */
    private function isHeaderRow(array $cols): bool
    {
        $c0 = strtolower(str_replace('_', ' ', $cols[0] ?? ''));
        $c1 = strtolower(str_replace('_', ' ', $cols[1] ?? ''));
        $c2 = strtolower(str_replace('_', ' ', $cols[2] ?? ''));

        $exactCol0Headers = [
            'nama perusahaan',
            'company name',
            'nama supplier',
            'supplier name',
        ];
        if (in_array($c0, $exactCol0Headers, true)) {
            return true;
        }

        if (in_array($c0, ['perusahaan', 'company', 'supplier'], true)) {
            $otherHeaders = ['nama kontak', 'kontak', 'pic', 'contact', 'contact person', 'email', 'e-mail', 'no hp', 'hp', 'telepon', 'telp'];
            if (in_array($c1, $otherHeaders, true) || in_array($c2, $otherHeaders, true)) {
                return true;
            }
        }

        return false;
    }
}

