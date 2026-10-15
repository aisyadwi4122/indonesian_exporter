<?php

namespace App\Commands;

use App\Models\SupplierModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

class ImportSupplier extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Supplier';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'supplier:import';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Mengimpor data supplier, produk, dan history ekspor dari file Excel.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'supplier:import [--dry-run]';

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--dry-run' => 'Jalankan semua proses di dalam transaksi lalu ROLLBACK, tetap cetak laporan lengkap.',
    ];

    /**
     * List of recorded warnings during import
     *
     * @var array<string>
     */
    protected array $warnings = [];

    /**
     * Form companies created as new suppliers
     *
     * @var array<string>
     */
    protected array $formNewSuppliers = [];

    /**
     * Stats tracking
     *
     * @var array<string, mixed>
     */
    protected array $stats = [
        'files' => [
            '2024' => ['blocks' => 0, 'history' => 0],
            '2025' => ['blocks' => 0, 'history' => 0],
            'form' => ['products' => 0],
        ],
        'suppliers' => [
            'new' => 0,
            'merged' => 0,
            'total_unique' => 0,
        ],
        'products' => [
            'added' => 0,
            'updated' => 0,
            'total' => 0,
        ],
        'history_total' => 0,
    ];

    /**
     * Track cleared history pairs ($idSupplier-$year) to ensure idempotency within the same run
     *
     * @var array<string, bool>
     */
    protected array $clearedHistory = [];

    /**
     * Actually run the command.
     */
    public function run(array $params)
    {
        $dryRun = array_key_exists('dry-run', $params) || CLI::getOption('dry-run') !== null;

        CLI::newLine();
        CLI::write('=============================================================', 'cyan');
        CLI::write('               IMPORT DATA SUPPLIER & PRODUK                 ', 'cyan');
        CLI::write('=============================================================', 'cyan');

        if ($dryRun) {
            CLI::write('MODE: --dry-run (Semua operasi database akan di-ROLLBACK)', 'yellow');
        } else {
            CLI::write('MODE: LIVE IMPORT (Data akan disimpan secara permanen)', 'green');
        }
        CLI::newLine();

        // 1. Cari file sumber di writable/import/
        $file2024   = $this->findImportFile('*ECP_2024*.xlsx');
        $file2025   = $this->findImportFile('*ECP_2025*.xlsx');
        $fileProduk = $this->findImportFile('*List_Produk_Supplier*.xlsx');

        if (! $file2024) {
            CLI::error("File sumber ECP 2024 tidak ditemukan untuk pola '*ECP_2024*.xlsx' di writable/import/");
            return EXIT_ERROR;
        }
        if (! $file2025) {
            CLI::error("File sumber ECP 2025 tidak ditemukan untuk pola '*ECP_2025*.xlsx' di writable/import/");
            return EXIT_ERROR;
        }
        if (! $fileProduk) {
            CLI::error("File sumber List Produk tidak ditemukan untuk pola '*List_Produk_Supplier*.xlsx' di writable/import/");
            return EXIT_ERROR;
        }

        CLI::write('File sumber ditemukan:', 'green');
        CLI::write(' - ECP 2024   : ' . basename($file2024));
        CLI::write(' - ECP 2025   : ' . basename($file2025));
        CLI::write(' - List Produk: ' . basename($fileProduk));
        CLI::newLine();

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // 2. Proses File 2024
            CLI::write('Memproses file ECP 2024...', 'yellow');
            $this->processEcpFile($file2024, 2024, $db);

            // 3. Proses File 2025
            CLI::write('Memproses file ECP 2025...', 'yellow');
            $this->processEcpFile($file2025, 2025, $db);

            // 4. Proses File List Produk Supplier
            CLI::write('Memproses file List Produk Supplier...', 'yellow');
            $this->processProductFormFile($fileProduk, $db);

            // Hitung total unik supplier saat ini di transaksi
            $this->stats['suppliers']['total_unique'] = $db->table('tb_supplier')->countAllResults();
            $this->stats['products']['total']        = $db->table('tb_produk_supplier')->countAllResults();
            $this->stats['history_total']            = $db->table('tb_history_export_supplier')->countAllResults();

            if ($dryRun) {
                $db->transRollback();
                CLI::newLine();
                CLI::write('[DRY-RUN] Transaksi berhasil di-ROLLBACK (database tidak berubah).', 'yellow');
            } else {
                if ($db->transStatus() === false) {
                    $db->transRollback();
                    CLI::error('Transaksi gagal dan di-ROLLBACK.');
                    return EXIT_ERROR;
                }
                $db->transCommit();
                CLI::newLine();
                CLI::write('[SUCCESS] Transaksi berhasil di-COMMIT ke database.', 'green');
            }

            // 5. Cetak Laporan Akhir
            $this->printReport($dryRun);

            return EXIT_SUCCESS;

        } catch (Throwable $e) {
            $db->transRollback();
            CLI::newLine();
            CLI::error('Terjadi kesalahan selama impor: ' . $e->getMessage());
            CLI::write($e->getTraceAsString(), 'red');
            return EXIT_ERROR;
        }
    }

    /**
     * Cari file di writable/import berdasarkan pola glob (mendukung spasi/underscore fleksibel)
     */
    protected function findImportFile(string $pattern): ?string
    {
        $dir = rtrim(WRITEPATH, '\\/') . DIRECTORY_SEPARATOR . 'import';

        // Cek pola langsung
        $matches = glob($dir . DIRECTORY_SEPARATOR . $pattern);
        if (! empty($matches)) {
            return $matches[0];
        }

        // Cek pola fleksibel dengan mengganti '_' menjadi '*'
        $flexPattern = str_replace('_', '*', $pattern);
        $matches     = glob($dir . DIRECTORY_SEPARATOR . $flexPattern);
        if (! empty($matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Memproses file ECP 2024 atau ECP 2025 (HANYA sheet pertama / Sheet1)
     */
    protected function processEcpFile(string $filePath, int $year, $db): void
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(false);
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getSheet(0);

        $highestRow         = $sheet->getHighestRow();
        $highestCol         = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestCol);

        // Cari baris terakhir yang benar-benar memiliki data
        $lastDataRow = 2;
        for ($r = 3; $r <= $highestRow; $r++) {
            $hasData = false;
            // Periksa beberapa kolom representatif
            foreach (['H', 'AS', 'AT', 'AU', 'AV', 'AW', 'C', 'B', 'F', 'E', 'M'] as $col) {
                $val = trim((string) $sheet->getCell($col . $r)->getCalculatedValue());
                if ($val !== '') {
                    $hasData = true;
                    break;
                }
            }
            if ($hasData) {
                $lastDataRow = $r;
            }
        }

        // Cek orphaned rows setelah lastDataRow
        for ($r = $lastDataRow + 1; $r <= min($highestRow, $lastDataRow + 50); $r++) {
            $hasOrphanData = false;
            for ($c = 1; $c <= min($highestColumnIndex, 60); $c++) {
                $colLetter = Coordinate::stringFromColumnIndex($c);
                $val       = trim((string) $sheet->getCell($colLetter . $r)->getCalculatedValue());
                if ($val !== '') {
                    $hasOrphanData = true;
                    break;
                }
            }
            if ($hasOrphanData) {
                $this->warnings[] = "[{$year}] Baris {$r} memiliki data tetapi berada di luar blok supplier (dilewati).";
            }
        }

        // Temukan titik mulai tiap blok supplier (kolom H terisi)
        $blockStartRows = [];
        for ($r = 3; $r <= $lastDataRow; $r++) {
            $hVal = trim((string) $sheet->getCell('H' . $r)->getCalculatedValue());
            if ($hVal !== '') {
                $blockStartRows[] = $r;
            }
        }

        // Cek apakah ada data di antara baris 3 dan blok pertama
        if (! empty($blockStartRows) && $blockStartRows[0] > 3) {
            for ($r = 3; $r < $blockStartRows[0]; $r++) {
                $hasPreData = false;
                foreach (['AS', 'AT', 'AU', 'AV', 'AW', 'C', 'B', 'F'] as $col) {
                    if (trim((string) $sheet->getCell($col . $r)->getCalculatedValue()) !== '') {
                        $hasPreData = true;
                        break;
                    }
                }
                if ($hasPreData) {
                    $this->warnings[] = "[{$year}] Baris {$r} memiliki data tetapi tidak punya blok supplier (sebelum blok pertama).";
                }
            }
        }

        $now = date('Y-m-d H:i:s');

        // Iterasi tiap blok supplier
        $numBlocks = count($blockStartRows);
        $this->stats['files'][(string) $year]['blocks'] = $numBlocks;

        for ($i = 0; $i < $numBlocks; $i++) {
            $startRow = $blockStartRows[$i];
            $endRow   = isset($blockStartRows[$i + 1]) ? ($blockStartRows[$i + 1] - 1) : $lastDataRow;

            // Kolom H (Nama Badan Usaha) pada startRow
            $rawH = trim((string) $sheet->getCell('H' . $startRow)->getCalculatedValue());

            // Deteksi nama generik ("Koperasi", "UD", "CV", "PT")
            $hLettersOnly = trim(preg_replace('/[^a-zA-Z]/', '', $rawH));
            $isGeneric    = in_array(strtolower($hLettersOnly), ['koperasi', 'ud', 'cv', 'pt'], true);

            $namaPerusahaan = $rawH;
            if ($isGeneric) {
                // Ambil Merk Dagang dari kolom N pertama yang terisi di dalam blok
                $merkDagang = $this->getFirstFilledValue($sheet, 'N', $startRow, $endRow);
                if (! empty($merkDagang)) {
                    $namaPerusahaan   = $merkDagang;
                    $this->warnings[] = "[{$year} Baris {$startRow}] Nama perusahaan generik '{$rawH}' diganti dengan Merk Dagang '{$merkDagang}'.";
                }
            }

            $namaKunci = SupplierModel::makeKey($namaPerusahaan);

            // Ambil kolom identitas (ambil nilai terisi PERTAMA di dalam blok)
            $namaKontak = $this->normalizeValue($this->getFirstFilledValue($sheet, 'C', $startRow, $endRow));
            $emailR     = $this->getFirstFilledValue($sheet, 'R', $startRow, $endRow);
            $emailB     = $this->getFirstFilledValue($sheet, 'B', $startRow, $endRow);
            $email      = $this->normalizeValue(! empty($emailR) ? $emailR : $emailB);
            $noHp       = $this->cleanPhone($this->getFirstFilledValue($sheet, 'F', $startRow, $endRow));
            $website    = $this->normalizeValue($this->getFirstFilledValue($sheet, 'S', $startRow, $endRow));
            $instagram  = $this->normalizeValue($this->getFirstFilledValue($sheet, 'V', $startRow, $endRow));

            // Kolom kuning umum (2024 dan 2025)
            $domisiliUsaha     = $this->normalizeValue($this->getFirstFilledValue($sheet, 'E', $startRow, $endRow));
            $produkDipasarkan  = $this->normalizeValue($this->getFirstFilledValue($sheet, 'M', $startRow, $endRow));
            $jenisPerusahaan   = $this->normalizeValue($this->getFirstFilledValue($sheet, 'O', $startRow, $endRow));
            $kapasitasProduksi = $this->normalizeValue($this->getFirstFilledValue($sheet, 'P', $startRow, $endRow));
            $jumlahPegawai     = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AB', $startRow, $endRow));
            $frekuensiEkspor   = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AQ', $startRow, $endRow), 'frekuensi_ekspor');

            // Cek apakah supplier sudah ada di database
            $existingSupplier = $db->table('tb_supplier')
                ->where('nama_kunci', $namaKunci)
                ->get()
                ->getRowArray();

            $idSupplier = null;

            if ($year === 2024) {
                // Kolom kuning khusus 2024
                $pameranInternasional    = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AC', $startRow, $endRow));
                $keunggulanProduk        = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AD', $startRow, $endRow));
                $linkFotoUsaha           = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AE', $startRow, $endRow));
                $legalitas               = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AF', $startRow, $endRow));
                $linkDokumenLegalitas    = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AG', $startRow, $endRow));
                $sertifikasi             = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AH', $startRow, $endRow));
                $linkDokumenSertifikat   = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AI', $startRow, $endRow));
                $pernahEkspor            = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AJ', $startRow, $endRow), 'pernah_ekspor');
                $kategoriEkspor          = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AK', $startRow, $endRow));
                $ringkasanEksporTanggal  = $this->parseExcelDate($this->getFirstFilledValue($sheet, 'AL', $startRow, $endRow));
                $ringkasanEksporProduk   = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AM', $startRow, $endRow));
                $ringkasanEksporKapasitas = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AN', $startRow, $endRow));
                $ringkasanEksporNegara   = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AO', $startRow, $endRow));
                $ringkasanEksporNilai    = $this->normalizeValue($this->getFirstFilledValue($sheet, 'AP', $startRow, $endRow));

                $supplierData2024 = [
                    'nama_perusahaan'            => $namaPerusahaan,
                    'nama_kunci'                 => $namaKunci,
                    'nama_kontak'                => $namaKontak,
                    'email'                      => $email,
                    'no_hp'                      => $noHp,
                    'website'                    => $website,
                    'instagram'                  => $instagram,
                    'domisili_usaha'             => $domisiliUsaha,
                    'produk_dipasarkan'          => $produkDipasarkan,
                    'jenis_perusahaan'           => $jenisPerusahaan,
                    'kapasitas_produksi'         => $kapasitasProduksi,
                    'jumlah_pegawai'             => $jumlahPegawai,
                    'frekuensi_ekspor'           => $frekuensiEkspor,
                    'pameran_internasional'      => $pameranInternasional,
                    'keunggulan_produk'          => $keunggulanProduk,
                    'link_foto_usaha'            => $linkFotoUsaha,
                    'legalitas'                  => $legalitas,
                    'link_dokumen_legalitas'     => $linkDokumenLegalitas,
                    'sertifikasi'                => $sertifikasi,
                    'link_dokumen_sertifikat'    => $linkDokumenSertifikat,
                    'pernah_ekspor'              => $pernahEkspor,
                    'kategori_ekspor'            => $kategoriEkspor,
                    'ringkasan_ekspor_tanggal'   => $ringkasanEksporTanggal,
                    'ringkasan_ekspor_produk'    => $ringkasanEksporProduk,
                    'ringkasan_ekspor_kapasitas' => $ringkasanEksporKapasitas,
                    'ringkasan_ekspor_negara'    => $ringkasanEksporNegara,
                    'ringkasan_ekspor_nilai'     => $ringkasanEksporNilai,
                    'sumber_tahun'               => '2024',
                    'updated_at'                 => $now,
                ];

                if ($existingSupplier) {
                    $idSupplier = (int) $existingSupplier['id_supplier'];
                    // Update field-field 2024 yang tidak kosong jika ada duplikat dalam 2024
                    $updateFields = array_filter($supplierData2024, fn ($v) => $v !== null);
                    $db->table('tb_supplier')->where('id_supplier', $idSupplier)->update($updateFields);
                    $this->stats['suppliers']['merged']++;
                } else {
                    $supplierData2024['created_at'] = $now;
                    $db->table('tb_supplier')->insert($supplierData2024);
                    $idSupplier = (int) $db->insertID();
                    $this->stats['suppliers']['new']++;
                }
            } else {
                // Year 2025: HANYA 6 kolom kuning + identitas. KOLOM LAIN JANGAN DIIMPOR.
                $supplierData2025 = [
                    'nama_perusahaan'    => $namaPerusahaan,
                    'nama_kunci'         => $namaKunci,
                    'nama_kontak'        => $namaKontak,
                    'email'              => $email,
                    'no_hp'              => $noHp,
                    'website'            => $website,
                    'instagram'          => $instagram,
                    'domisili_usaha'     => $domisiliUsaha,
                    'produk_dipasarkan'  => $produkDipasarkan,
                    'jenis_perusahaan'   => $jenisPerusahaan,
                    'kapasitas_produksi' => $kapasitasProduksi,
                    'jumlah_pegawai'     => $jumlahPegawai,
                    'frekuensi_ekspor'   => $frekuensiEkspor,
                    'updated_at'         => $now,
                ];

                if ($existingSupplier) {
                    $idSupplier = (int) $existingSupplier['id_supplier'];
                    // Nilai file 2025 menang jika tidak kosong; kolom khusus 2024 tetap dari 2024
                    $updateFields = array_filter($supplierData2025, fn ($v) => $v !== null);

                    // Kelola sumber_tahun
                    $oldSumber = $existingSupplier['sumber_tahun'] ?? '';
                    if ($oldSumber === '2024') {
                        $updateFields['sumber_tahun'] = '2024,2025';
                    } elseif (empty($oldSumber)) {
                        $updateFields['sumber_tahun'] = '2025';
                    }

                    $db->table('tb_supplier')->where('id_supplier', $idSupplier)->update($updateFields);
                    $this->stats['suppliers']['merged']++;
                } else {
                    $supplierData2025['sumber_tahun'] = '2025';
                    $supplierData2025['created_at']   = $now;
                    $db->table('tb_supplier')->insert($supplierData2025);
                    $idSupplier = (int) $db->insertID();
                    $this->stats['suppliers']['new']++;
                }
            }

            // Proses produk dari blok supplier (2024 & 2025):
            // Ambil produk dari kolom M (produk_dipasarkan). Jika ada lebih dari 1 produk dalam 1 baris, ambil yang paling depan saja.
            if (! empty($produkDipasarkan)) {
                $namaProduk = $this->extractFirstProduct($produkDipasarkan);
                if (! empty($namaProduk)) {
                    $defaultImage = 'assets/img/produk/pro1.png';

                    $existingProduct = $db->table('tb_produk_supplier')
                        ->where('id_supplier', $idSupplier)
                        ->where('nama_produk', $namaProduk)
                        ->get()
                        ->getRowArray();

                    $productData = [
                        'id_supplier'        => $idSupplier,
                        'nama_produk'        => $namaProduk,
                        'spesifikasi'        => $produkDipasarkan,
                        'kapasitas_produksi' => $kapasitasProduksi,
                        'legalitas'          => ($year === 2024) ? ($legalitas ?? null) : null,
                        'sertifikasi'        => ($year === 2024) ? ($sertifikasi ?? null) : null,
                        'lokasi_barang'      => $domisiliUsaha,
                        'foto_1'             => $defaultImage,
                        'foto_2'             => null,
                        'foto_3'             => null,
                        'foto_4'             => null,
                        'foto_5'             => null,
                        'moq'                => null,
                        'harga_exw'          => null,
                        'komisi'             => null,
                        'sudah_ekspor'       => ($year === 2024) ? ($pernahEkspor ?? null) : null,
                        'updated_at'         => $now,
                    ];

                    if ($existingProduct) {
                        // Jangan reset views saat update
                        $db->table('tb_produk_supplier')
                            ->where('id_produk_supplier', $existingProduct['id_produk_supplier'])
                            ->update($productData);
                        $this->stats['products']['updated']++;
                    } else {
                        $productData['views']      = 0;
                        $productData['created_at'] = $now;
                        $db->table('tb_produk_supplier')->insert($productData);
                        $this->stats['products']['added']++;
                    }
                }
            }

            // Proses baris tb_history_export_supplier di dalam blok
            for ($subRow = $startRow; $subRow <= $endRow; $subRow++) {
                $rawAS = trim((string) $sheet->getCell('AS' . $subRow)->getCalculatedValue());
                $rawAT = trim((string) $sheet->getCell('AT' . $subRow)->getCalculatedValue());
                $rawAU = trim((string) $sheet->getCell('AU' . $subRow)->getCalculatedValue());
                $rawAV = trim((string) $sheet->getCell('AV' . $subRow)->getCalculatedValue());
                $rawAW = $sheet->getCell('AW' . $subRow)->getCalculatedValue();
                $strAW = trim((string) $rawAW);

                // Setiap baris dalam blok yang salah satu kolom AS-AW terisi menjadi satu baris history
                if ($rawAS !== '' || $rawAT !== '' || $rawAU !== '' || $rawAV !== '' || $strAW !== '') {
                    // Idempoten: untuk setiap supplier dan tahun yang diproses, hapus dulu baris history lama
                    $clearKey = "{$idSupplier}-{$year}";
                    if (! isset($this->clearedHistory[$clearKey])) {
                        $db->table('tb_history_export_supplier')
                            ->where('id_supplier', $idSupplier)
                            ->where('tahun', $year)
                            ->delete();
                        $this->clearedHistory[$clearKey] = true;
                    }

                    // Pembersihan nilai ekspor AW
                    $parsedExport = $this->parseExportValue($rawAW);
                    if ($parsedExport['error']) {
                        $this->warnings[] = "[{$year} Baris {$subRow}] {$parsedExport['error']} (Perusahaan: '{$namaPerusahaan}').";
                    }
                    if ($parsedExport['catatan']) {
                        $this->warnings[] = "[{$year} Baris {$subRow}] Nilai ekspor tidak wajar: USD {$parsedExport['usd']} (Perusahaan: '{$namaPerusahaan}').";
                    }

                    // Pembersihan negara AT
                    $cleanedCountry = $this->cleanCountry($rawAT);

                    $historyData = [
                        'id_supplier'      => $idSupplier,
                        'tahun'            => $year,
                        'produk'           => $this->normalizeValue($rawAS),
                        'negara_tujuan'    => $this->normalizeValue($cleanedCountry),
                        'jenis_ekspor'     => $this->normalizeValue($rawAU),
                        'kuantitas'        => $this->normalizeValue($rawAV),
                        'nilai_ekspor_usd' => $parsedExport['usd'],
                        'nilai_ekspor_asli' => mb_substr((string) $parsedExport['asli'], 0, 100),
                        'catatan'          => $parsedExport['catatan'],
                        'created_at'       => $now,
                    ];

                    $db->table('tb_history_export_supplier')->insert($historyData);
                    $this->stats['files'][(string) $year]['history']++;
                }
            }
        }
    }

    /**
     * Memproses file List Produk Supplier IE (sheet Form Responses 1)
     */
    protected function processProductFormFile(string $filePath, $db): void
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(false);
        $spreadsheet = $reader->load($filePath);

        $sheet = $spreadsheet->getSheetByName('Form Responses 1');
        if (! $sheet) {
            $sheet = $spreadsheet->getSheet(0);
        }

        $highestRow = $sheet->getHighestRow();
        $now        = date('Y-m-d H:i:s');

        // Header baris 1, data baris 2 dst.
        for ($r = 2; $r <= $highestRow; $r++) {
            $rawD = trim((string) $sheet->getCell('D' . $r)->getCalculatedValue());
            if ($rawD === '') {
                continue;
            }

            $namaPerusahaan = $rawD;
            $namaKunci      = SupplierModel::makeKey($namaPerusahaan);

            $namaKontakRaw = $sheet->getCell('C' . $r)->getCalculatedValue();
            $namaKontak    = $this->normalizeValue($namaKontakRaw);

            $noHpRaw = $sheet->getCell('E' . $r)->getCalculatedValue();
            $noHp    = $this->cleanPhone($noHpRaw);

            // Kolom F: jika tidak mengandung "@", simpan NULL dan catat di laporan
            $rawF  = trim((string) $sheet->getCell('F' . $r)->getCalculatedValue());
            $email = null;
            if ($rawF !== '') {
                if (str_contains($rawF, '@')) {
                    $email = $this->normalizeValue($rawF);
                } else {
                    $this->warnings[] = "[Form Baris {$r}] Kolom F berisi alamat, bukan email valid: '{$rawF}' (disimpan NULL).";
                }
            }

            $website           = $this->normalizeValue($sheet->getCell('G' . $r)->getCalculatedValue());
            $instagram         = $this->normalizeValue($sheet->getCell('H' . $r)->getCalculatedValue());
            $lokasiPabrikGudang = $this->normalizeValue($sheet->getCell('I' . $r)->getCalculatedValue());
            $jenisPerusahaan   = $this->normalizeValue($sheet->getCell('J' . $r)->getCalculatedValue());

            // 1. Cocokkan ke supplier lewat nama_kunci
            $existingSupplier = $db->table('tb_supplier')
                ->where('nama_kunci', $namaKunci)
                ->get()
                ->getRowArray();

            $idSupplier = null;

            if ($existingSupplier) {
                $idSupplier = (int) $existingSupplier['id_supplier'];

                // Hanya ISI kolom supplier yang masih kosong (jangan menimpa yang sudah terisi)
                $updateCandidate = [
                    'email'                => $email,
                    'no_hp'                => $noHp,
                    'website'              => $website,
                    'instagram'            => $instagram,
                    'lokasi_pabrik_gudang' => $lokasiPabrikGudang,
                    'jenis_perusahaan'     => $jenisPerusahaan,
                    'nama_kontak'          => $namaKontak,
                ];

                $fieldsToUpdate = [];
                foreach ($updateCandidate as $col => $val) {
                    if ($val !== null && ($existingSupplier[$col] === null || trim((string) $existingSupplier[$col]) === '')) {
                        $fieldsToUpdate[$col] = $val;
                    }
                }

                if (! empty($fieldsToUpdate)) {
                    $fieldsToUpdate['updated_at'] = $now;
                    $db->table('tb_supplier')->where('id_supplier', $idSupplier)->update($fieldsToUpdate);
                }
            } else {
                // Buat supplier baru dari data form (sumber_tahun NULL)
                $newSupplierData = [
                    'nama_perusahaan'      => $namaPerusahaan,
                    'nama_kunci'           => $namaKunci,
                    'nama_kontak'          => $namaKontak,
                    'email'                => $email,
                    'no_hp'                => $noHp,
                    'website'              => $website,
                    'instagram'            => $instagram,
                    'lokasi_pabrik_gudang' => $lokasiPabrikGudang,
                    'jenis_perusahaan'     => $jenisPerusahaan,
                    'sumber_tahun'         => null,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];

                $db->table('tb_supplier')->insert($newSupplierData);
                $idSupplier = (int) $db->insertID();

                $this->formNewSuppliers[] = $namaPerusahaan;
                $this->stats['suppliers']['new']++;
            }

            // 2. Satu baris form = satu baris tb_produk_supplier
            $rawK = trim((string) $sheet->getCell('K' . $r)->getCalculatedValue());
            if ($rawK === '') {
                continue;
            }

            $namaProduk = $rawK;
            $spesifikasi = $this->normalizeValue($sheet->getCell('L' . $r)->getCalculatedValue());
            $legalitas   = $this->normalizeValue($sheet->getCell('M' . $r)->getCalculatedValue());
            $sertifikasi = $this->normalizeValue($sheet->getCell('N' . $r)->getCalculatedValue());
            $lokasiBarang = $this->normalizeValue($sheet->getCell('I' . $r)->getCalculatedValue());
            $sudahEkspor = $this->normalizeValue($sheet->getCell('P' . $r)->getCalculatedValue(), 'sudah_ekspor');
            $hargaExw    = $this->normalizeValue($sheet->getCell('Q' . $r)->getCalculatedValue());
            $komisi      = $this->normalizeValue($sheet->getCell('R' . $r)->getCalculatedValue());

            // Pemisahan foto O (1-5 link Google Drive)
            $rawO = $sheet->getCell('O' . $r)->getCalculatedValue();
            [$photoUrls, $photoWarning] = $this->splitPhotos($rawO);
            if ($photoWarning) {
                $this->warnings[] = "[Form Baris {$r}] {$photoWarning} (Produk: '{$namaProduk}').";
            }

            $foto1 = $photoUrls[0] ?? null;
            $foto2 = $photoUrls[1] ?? null;
            $foto3 = $photoUrls[2] ?? null;
            $foto4 = $photoUrls[3] ?? null;
            $foto5 = $photoUrls[4] ?? null;

            // Idempoten produk: upsert berdasarkan (id_supplier, nama_produk)
            $existingProduct = $db->table('tb_produk_supplier')
                ->where('id_supplier', $idSupplier)
                ->where('nama_produk', $namaProduk)
                ->get()
                ->getRowArray();

            $productData = [
                'id_supplier'        => $idSupplier,
                'nama_produk'        => $namaProduk,
                'spesifikasi'        => $spesifikasi,
                'kapasitas_produksi' => null,
                'legalitas'          => $legalitas,
                'sertifikasi'        => $sertifikasi,
                'lokasi_barang'      => $lokasiBarang,
                'foto_1'             => $foto1,
                'foto_2'             => $foto2,
                'foto_3'             => $foto3,
                'foto_4'             => $foto4,
                'foto_5'             => $foto5,
                'moq'                => null,
                'harga_exw'          => $hargaExw,
                'komisi'             => $komisi,
                'sudah_ekspor'       => $sudahEkspor,
                'updated_at'         => $now,
            ];

            if ($existingProduct) {
                // Kolom views TIDAK BOLEH di-reset saat update!
                $db->table('tb_produk_supplier')
                    ->where('id_produk_supplier', $existingProduct['id_produk_supplier'])
                    ->update($productData);
                $this->stats['products']['updated']++;
            } else {
                $productData['views']      = 0;
                $productData['created_at'] = $now;
                $db->table('tb_produk_supplier')->insert($productData);
                $this->stats['products']['added']++;
            }

            $this->stats['files']['form']['products']++;
        }
    }

    /**
     * Ambil nilai terisi pertama dalam rentang baris di kolom tertentu
     */
    protected function getFirstFilledValue($sheet, string $col, int $startRow, int $endRow)
    {
        for ($r = $startRow; $r <= $endRow; $r++) {
            $val = $sheet->getCell($col . $r)->getCalculatedValue();
            if ($val !== null) {
                $str = trim((string) $val);
                if ($str !== '') {
                    return $val;
                }
            }
        }
        return null;
    }

    /**
     * Ekstrak nama produk pertama jika dalam satu baris terdapat lebih dari satu produk
     */
    protected function extractFirstProduct(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        // Hapus penjelasan dalam tanda kurung jika di depannya sudah ada nama produk
        // Contoh: "Sarung Bantal Anti Jerawat (...)" -> "Sarung Bantal Anti Jerawat"
        $cleaned = preg_replace('/\s*\([^)]*\)/u', '', $raw);
        $cleaned = trim((string) $cleaned);
        if ($cleaned === '') {
            // Jika seluruh teks berada dalam tanda kurung
            $cleaned = trim($raw, "() \t\n\r\0\x0B");
        }

        // Jika terdapat lebih dari 1 produk dalam 1 baris, pisahkan berdasarkan koma, titik koma, baris baru, atau " dan "
        $parts = preg_split('/[\r\n,;]+|\s+dan\s+/ui', $cleaned);
        $first = trim($parts[0] ?? '');

        // Rapikan tanda kutip, strip, titik di ujung teks
        $first = trim($first, " .\t\n\r\0\x0B-\"'/");

        return $first !== '' ? $first : null;
    }

    /**
     * Normalisasi nilai umum
     * - Nilai kosong dianggap NULL: "", "-", "tidak ada", "n/a", "belum pernah" (case-insensitive)
     * - PENGECUALIAN: kolom frekuensi_ekspor dan pernah_ekspor, nilai "Tidak Pernah" / "Tidak" harus disimpan apa adanya.
     * - Angka float tanpa pecahan jadi teks tanpa ".0" (18.0 -> "18"). Trim spasi di semua teks.
     */
    protected function normalizeValue($raw, string $field = ''): ?string
    {
        if ($raw === null) {
            return null;
        }

        // Float tanpa pecahan jadi teks tanpa ".0"
        if (is_float($raw) || (is_numeric($raw) && str_contains((string) $raw, '.'))) {
            if ((float) $raw == (int) $raw) {
                $raw = (string) (int) $raw;
            }
        }

        $str = trim((string) $raw);
        if ($str === '') {
            return null;
        }

        // Pengecualian frekuensi_ekspor, pernah_ekspor, dan sudah_ekspor
        if (in_array($field, ['frekuensi_ekspor', 'pernah_ekspor', 'sudah_ekspor'], true)) {
            $lower = strtolower($str);
            if ($lower === 'tidak pernah' || $lower === 'tidak') {
                return $str;
            }
        }

        $lower = strtolower($str);
        if (in_array($lower, ['-', 'tidak ada', 'n/a', 'belum pernah'], true)) {
            return null;
        }

        return $str;
    }

    /**
     * Pembersihan nomor HP:
     * - ambil digit saja
     * - jika diawali "62" ganti dengan "0"
     * - jika diawali "8" tambahkan "0" di depan
     */
    protected function cleanPhone($raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $str    = trim((string) $raw);
        $digits = preg_replace('/\D/', '', $str);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '0' . $digits;
        }

        return $digits;
    }

    /**
     * Pembersihan Tanggal Excel (angka seri) jadi Y-m-d; jika teks simpan apa adanya.
     */
    protected function parseExcelDate($raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $str = trim((string) $raw);
        if ($str === '') {
            return null;
        }

        if (is_numeric($raw)) {
            try {
                return Date::excelToDateTimeObject((float) $raw)->format('Y-m-d');
            } catch (Throwable $e) {
                return $str;
            }
        }

        return $str;
    }

    /**
     * Pembersihan Nilai Ekspor (kolom AW):
     * - Buang "USD", "$", spasi. Simpan teks asli di nilai_ekspor_asli.
     * - Koma = desimal, titik = pemisah ribuan ("1.021,55" -> 1021.55).
     * - Jika hanya titik dan ^\d{1,3}(\.\d{3})+$ anggap pemisah ribuan; selain itu titik = desimal.
     * - Angka numerik dipakai langsung. Bulatkan 2 desimal.
     * - Jika nilai > 1.000.000.000: catatan = 'Perlu verifikasi (nilai tidak wajar)'.
     */
    protected function parseExportValue($raw): array
    {
        if ($raw === null || $raw === '') {
            return ['usd' => null, 'asli' => null, 'catatan' => null, 'error' => null];
        }

        $asli = trim((string) $raw);
        if ($asli === '') {
            return ['usd' => null, 'asli' => null, 'catatan' => null, 'error' => null];
        }

        if (is_int($raw) || is_float($raw)) {
            $val     = round((float) $raw, 2);
            $catatan = ($val > 1000000000) ? 'Perlu verifikasi (nilai tidak wajar)' : null;
            return ['usd' => $val, 'asli' => $asli, 'catatan' => $catatan, 'error' => null];
        }

        $cleaned = trim(str_ireplace(['USD', '$', ' '], '', $asli));
        if ($cleaned === '') {
            return ['usd' => null, 'asli' => $asli, 'catatan' => null, 'error' => null];
        }

        $val = null;
        if (str_contains($cleaned, ',')) {
            $norm = str_replace('.', '', $cleaned);
            $norm = str_replace(',', '.', $norm);
            if (is_numeric($norm)) {
                $val = (float) $norm;
            }
        } else {
            if (preg_match('/^\d{1,3}(\.\d{3})+$/', $cleaned)) {
                $norm = str_replace('.', '', $cleaned);
                if (is_numeric($norm)) {
                    $val = (float) $norm;
                }
            } elseif (is_numeric($cleaned)) {
                $val = (float) $cleaned;
            }
        }

        if ($val === null) {
            return [
                'usd'     => null,
                'asli'    => $asli,
                'catatan' => null,
                'error'   => "Gagal parse nilai ekspor: '{$asli}'",
            ];
        }

        $val     = round($val, 2);
        $catatan = ($val > 1000000000) ? 'Perlu verifikasi (nilai tidak wajar)' : null;

        return [
            'usd'     => $val,
            'asli'    => $asli,
            'catatan' => $catatan,
            'error'   => null,
        ];
    }

    /**
     * Pembersihan nama negara:
     * - rapikan kapitalisasi (Title Case)
     * - sinonim jelas: india -> India, Hongkong -> Hong Kong, Perancis -> France, Jepang -> Japan, Scotlandia -> Scotland
     */
    protected function cleanCountry(string $country): string
    {
        $country = trim($country);
        if ($country === '') {
            return '';
        }

        // Rapikan kapitalisasi kata, pertahankan singkatan umum (USA, UEA, UK, UAE)
        $country = preg_replace_callback('/\b[a-zA-Z]+\b/u', function ($m) {
            $word = $m[0];
            if (in_array(strtoupper($word), ['USA', 'UEA', 'UK', 'UAE'], true)) {
                return strtoupper($word);
            }
            if (ctype_upper($word) || ctype_lower($word)) {
                return ucfirst(strtolower($word));
            }
            return $word;
        }, $country);

        // Sinonim khusus
        $synonyms = [
            '/\b(scotlandia)\b/i' => 'Scotland',
            '/\b(perancis|prancis)\b/i' => 'France',
            '/\b(jepang)\b/i'     => 'Japan',
            '/\b(hongkong)\b/i'   => 'Hong Kong',
            '/\b(hong\s+kong)\b/i' => 'Hong Kong',
            '/\b(india)\b/i'       => 'India',
        ];

        foreach ($synonyms as $pattern => $replacement) {
            $country = preg_replace($pattern, $replacement, $country);
        }

        return $country;
    }

    /**
     * Pemisahan link foto Google Drive di kolom O
     */
    protected function splitPhotos($raw): array
    {
        if ($raw === null) {
            return [[], null];
        }

        $str = trim((string) $raw);
        if ($str === '') {
            return [[], null];
        }

        // Pisahkan berdasarkan koma, baris, atau spasi antar-URL
        $parts = preg_split('/[\r\n,]+|\s+(?=https?:\/\/)/u', $str);
        $urls  = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') {
                $urls[] = $p;
            }
        }

        $warning = null;
        if (count($urls) > 5) {
            $count          = count($urls);
            $discardedCount = $count - 5;
            $warning        = "Foto lebih dari 5 ({$count} ditemukan, {$discardedCount} dibuang)";
            $urls           = array_slice($urls, 0, 5);
        }

        return [$urls, $warning];
    }

    /**
     * Cetak Laporan Lengkap di Terminal
     */
    protected function printReport(bool $dryRun): void
    {
        CLI::newLine();
        CLI::write('=============================================================', 'cyan');
        CLI::write('                      LAPORAN HASIL IMPOR                    ', 'cyan');
        CLI::write('=============================================================', 'cyan');

        if ($dryRun) {
            CLI::write('Status Eksekusi : DRY-RUN (Semua perubahan telah di-ROLLBACK)', 'yellow');
        } else {
            CLI::write('Status Eksekusi : LIVE IMPORT (Data telah tersimpan di database)', 'green');
        }
        CLI::newLine();

        // 1. Per File
        CLI::write('1. RINGKASAN PEMBACAAN FILE:', 'yellow');
        CLI::write(sprintf('   - ECP 2024    : %d blok supplier, %d baris history', $this->stats['files']['2024']['blocks'], $this->stats['files']['2024']['history']));
        CLI::write(sprintf('   - ECP 2025    : %d blok supplier, %d baris history', $this->stats['files']['2025']['blocks'], $this->stats['files']['2025']['history']));
        CLI::write(sprintf('   - List Produk : %d produk terbaca', $this->stats['files']['form']['products']));
        CLI::newLine();

        // 2. Data Supplier
        CLI::write('2. DATA SUPPLIER (tb_supplier):', 'yellow');
        CLI::write(sprintf('   - Supplier Baru Dibuat : %d', $this->stats['suppliers']['new']));
        CLI::write(sprintf('   - Supplier Digabung    : %d', $this->stats['suppliers']['merged']));
        CLI::write(sprintf('   - Total Supplier Unik  : %d', $this->stats['suppliers']['total_unique']));
        CLI::newLine();

        // 3. Data Produk
        CLI::write('3. DATA PRODUK (tb_produk_supplier):', 'yellow');
        CLI::write(sprintf('   - Produk Ditambah      : %d', $this->stats['products']['added']));
        CLI::write(sprintf('   - Produk Diperbarui    : %d', $this->stats['products']['updated']));
        CLI::write(sprintf('   - Total Produk         : %d', $this->stats['products']['total']));
        CLI::newLine();

        // 4. Data History
        CLI::write('4. DATA HISTORY EKSPOR (tb_history_export_supplier):', 'yellow');
        CLI::write(sprintf('   - Total Baris History  : %d', $this->stats['history_total']));
        CLI::newLine();

        // 5. Perusahaan Form yang Dibuat Sebagai Supplier Baru
        CLI::write('5. PERUSAHAAN DARI FORM SEBAGAI SUPPLIER BARU (' . count($this->formNewSuppliers) . '):', 'yellow');
        if (empty($this->formNewSuppliers)) {
            CLI::write('   (Tidak ada)');
        } else {
            foreach ($this->formNewSuppliers as $idx => $comp) {
                CLI::write(sprintf('   %d. %s', $idx + 1, $comp));
            }
        }
        CLI::newLine();

        // 6. Daftar Peringatan
        CLI::write('6. DAFTAR PERINGATAN / CATATAN KHUSUS (' . count($this->warnings) . '):', 'yellow');
        if (empty($this->warnings)) {
            CLI::write('   (Tidak ada peringatan)');
        } else {
            foreach ($this->warnings as $idx => $warn) {
                CLI::write(sprintf('   [%d] %s', $idx + 1, $warn), 'light_gray');
            }
        }
        CLI::newLine();

        // 7. Sanity Check Status
        CLI::write('7. SANITY CHECK EVALUATION:', 'yellow');
        $s2024Ok = ($this->stats['files']['2024']['blocks'] === 52 && $this->stats['files']['2024']['history'] === 59);
        $s2025Ok = ($this->stats['files']['2025']['blocks'] === 29 && $this->stats['files']['2025']['history'] === 57);
        $formOk  = ($this->stats['files']['form']['products'] === 10 && count($this->formNewSuppliers) === 5);

        CLI::write(sprintf('   - 2024 (Harapan: ~52 blok, ~59 history) : %s (Aktual: %d blok, %d history)',
            $s2024Ok ? '[COCOK]' : '[PERHATIAN]',
            $this->stats['files']['2024']['blocks'],
            $this->stats['files']['2024']['history']
        ), $s2024Ok ? 'green' : 'yellow');

        CLI::write(sprintf('   - 2025 (Harapan: ~29 blok, ~57 history) : %s (Aktual: %d blok, %d history)',
            $s2025Ok ? '[COCOK]' : '[PERHATIAN]',
            $this->stats['files']['2025']['blocks'],
            $this->stats['files']['2025']['history']
        ), $s2025Ok ? 'green' : 'yellow');

        CLI::write(sprintf('   - Form (Harapan: 10 produk, 5 supplier)  : %s (Aktual: %d produk, %d supplier)',
            $formOk ? '[COCOK]' : '[PERHATIAN]',
            $this->stats['files']['form']['products'],
            count($this->formNewSuppliers)
        ), $formOk ? 'green' : 'yellow');

        CLI::newLine();
        CLI::write('=============================================================', 'cyan');
    }
}
