<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Throwable;

class PindahProdukSupplier extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Produk';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'produk:pindah-dari-profil';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Memindahkan daftar produk dari kolom teks tb_supplier.produk_dipasarkan ke baris-baris tb_produk_supplier.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'produk:pindah-dari-profil [options]';

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--only'               => 'Batasi ke id_supplier tertentu (contoh: --only=1,10,18,27,81).',
        '--report'             => 'Path file CSV laporan (opsional pada dry-run, WAJIB pada --execute). Harus di LUAR folder project.',
        '--include-low'        => 'Ikutkan perusahaan dengan keyakinan RENDAH (default: dilewati).',
        '--include-duplicates' => 'Ikutkan perusahaan yang kemungkinan terdaftar ganda (default: dilewati).',
        '--execute'            => 'Tulis perubahan ke database dalam satu transaksi (default: DRY-RUN).',
    ];

    /**
     * Run the command
     *
     * @param array<int|string, string|null> $params
     */
    public function run(array $params)
    {
        $isExecute         = $this->hasCustomOption('execute', $params);
        $includeLow        = $this->hasCustomOption('include-low', $params);
        $includeDuplicates = $this->hasCustomOption('include-duplicates', $params);

        $reportPath = $this->getCustomOption('report', $params);
        $onlyStr    = $this->getCustomOption('only', $params);

        // 1. Validasi opsi --report
        $canonicalReportPath = null;
        if ($isExecute) {
            if ($reportPath === null || trim($reportPath) === '') {
                CLI::error('Opsi --report=<path CSV> WAJIB disertakan saat menggunakan --execute.');
                return EXIT_USER_INPUT;
            }
            $validation = $this->validateReportPath($reportPath);
            if (!$validation['valid']) {
                CLI::error($validation['error']);
                return EXIT_USER_INPUT;
            }
            if (file_exists($validation['canonicalPath'])) {
                CLI::error("File laporan sudah ada: '{$validation['canonicalPath']}'. Harap gunakan nama file baru.");
                return EXIT_USER_INPUT;
            }
            $canonicalReportPath = $validation['canonicalPath'];
        } else {
            if ($reportPath !== null && trim($reportPath) !== '') {
                $validation = $this->validateReportPath($reportPath);
                if (!$validation['valid']) {
                    CLI::error($validation['error']);
                    return EXIT_USER_INPUT;
                }
                $canonicalReportPath = $validation['canonicalPath'];
            }
        }

        $db = Database::connect();

        // 2. Ambil seluruh data supplier dari tb_supplier untuk deteksi duplikat global
        $allSuppliers = $db->query("
            SELECT s.id_supplier, s.nama_perusahaan, s.no_hp, s.website, s.instagram, s.produk_dipasarkan, s.kapasitas_produksi,
                   (SELECT COUNT(*) FROM tb_produk_supplier p WHERE p.id_supplier = s.id_supplier) as prod_count
            FROM tb_supplier s
            ORDER BY s.id_supplier ASC
        ")->getResultArray();

        $nameMap = [];
        $prodCounts = [];
        foreach ($allSuppliers as $s) {
            $sid = (int)$s['id_supplier'];
            $nameMap[$sid] = (string)$s['nama_perusahaan'];
            $prodCounts[$sid] = (int)$s['prod_count'];
        }

        // 3. Deteksi Kemungkinan Terdaftar Ganda (Global)
        $duplicateGroups = $this->detectDuplicateGroups($allSuppliers);
        $supplierToGroup = [];
        foreach ($duplicateGroups as $grp) {
            foreach ($grp as $memberId) {
                $supplierToGroup[$memberId] = $grp;
            }
        }

        // 4. Filter target supplier jika opsi --only diberikan
        $targetIds = null;
        if ($onlyStr !== null && trim($onlyStr) !== '') {
            $targetIds = [];
            foreach (explode(',', $onlyStr) as $p) {
                $id = (int)trim($p);
                if ($id > 0) {
                    $targetIds[] = $id;
                }
            }
            $targetIds = array_unique($targetIds);
        }

        $targetSuppliers = [];
        foreach ($allSuppliers as $s) {
            $sid = (int)$s['id_supplier'];
            if ($targetIds === null || in_array($sid, $targetIds, true)) {
                $targetSuppliers[] = $s;
            }
        }

        // 5. Evaluasi Setiap Supplier Target
        $evaluatedRows = [];
        $summary = [
            'total_perusahaan'         => count($targetSuppliers),
            'akan_dipindah'            => 0,
            'total_produk_akan_dibuat' => 0,
            'keyakinan_tinggi'         => 0,
            'keyakinan_rendah'         => 0,
            'skip_sudah_punya_produk'  => 0,
            'skip_kemungkinan_ganda'   => 0,
            'skip_keyakinan_rendah'    => 0,
            'skip_produk_kosong'       => 0,
        ];

        foreach ($targetSuppliers as $s) {
            $eval = $this->evaluateSupplier($s, $supplierToGroup, $nameMap, $includeLow, $includeDuplicates, $isExecute);
            $evaluatedRows[] = $eval;

            if ($eval['action'] === 'migrate') {
                $summary['akan_dipindah']++;
                $summary['total_produk_akan_dibuat'] += $eval['jml_produk'];
                if ($eval['keyakinan'] === 'TINGGI') {
                    $summary['keyakinan_tinggi']++;
                } elseif ($eval['keyakinan'] === 'RENDAH') {
                    $summary['keyakinan_rendah']++;
                }
            } else {
                if ($eval['reason'] === 'sudah_punya_produk') {
                    $summary['skip_sudah_punya_produk']++;
                } elseif ($eval['reason'] === 'kemungkinan_ganda') {
                    $summary['skip_kemungkinan_ganda']++;
                } elseif ($eval['reason'] === 'keyakinan_rendah') {
                    $summary['skip_keyakinan_rendah']++;
                } elseif ($eval['reason'] === 'produk_kosong') {
                    $summary['skip_produk_kosong']++;
                }
            }
        }

        // 6. Eksekusi Penulisan Database jika --execute
        if ($isExecute) {
            $db->transBegin();
            try {
                $now = date('Y-m-d H:i:s');
                $createdCount = 0;
                $skippedDuplicateProductCount = 0;

                foreach ($evaluatedRows as $item) {
                    if ($item['action'] !== 'migrate') {
                        continue;
                    }

                    $idSupplier = (int)$item['id'];
                    $kapasitasDibawa = $item['kapasitas_dibawa'];

                    foreach ($item['items'] as $namaProduk) {
                        // Cek keunikan produk untuk supplier yang sama (case-insensitive)
                        $exists = $db->table('tb_produk_supplier')
                            ->where('id_supplier', $idSupplier)
                            ->where('LOWER(TRIM(nama_produk))', strtolower(trim($namaProduk)))
                            ->countAllResults();

                        if ($exists > 0) {
                            $skippedDuplicateProductCount++;
                            continue;
                        }

                        $dataInsert = [
                            'id_supplier'        => $idSupplier,
                            'nama_produk'        => $namaProduk,
                            'kapasitas_produksi' => $kapasitasDibawa !== '' ? $kapasitasDibawa : null,
                            'views'              => 0,
                            'created_at'         => $now,
                            'updated_at'         => $now,
                        ];

                        $db->table('tb_produk_supplier')->insert($dataInsert);
                        $createdCount++;
                    }
                }

                if ($db->transStatus() === false) {
                    $db->transRollback();
                    CLI::error('Transaksi database gagal. Semua penulisan di-ROLLBACK.');
                    return EXIT_ERROR;
                }

                $db->transCommit();
            } catch (Throwable $e) {
                $db->transRollback();
                CLI::error('Terjadi kesalahan saat transaksi database: ' . $e->getMessage() . '. Semua penulisan di-ROLLBACK.');
                return EXIT_ERROR;
            }

            // Tulis laporan CSV setelah transaksi commit
            $this->writeReportCsv($canonicalReportPath, $evaluatedRows);

            CLI::write('============================================================', 'green');
            CLI::write('EKSEKUSI PEMINDAHAN PRODUK SELESAI', 'green');
            CLI::write('============================================================', 'green');
            CLI::write("Total perusahaan diproses       : {$summary['total_perusahaan']}");
            CLI::write("Perusahaan berhasil dipindah    : {$summary['akan_dipindah']}");
            CLI::write("Total baris produk dibuat       : {$createdCount}");
            CLI::write("Baris produk dilewati (duplikat): {$skippedDuplicateProductCount}");
            CLI::write("Laporan CSV tersimpan di        : {$canonicalReportPath}");
            CLI::write('============================================================', 'green');

            return EXIT_SUCCESS;
        }

        // 7. DRY-RUN (Default) - Tampilkan Tabel Ringkas dan Ringkasan Total
        $modeLabel = 'DRY-RUN (TIDAK ADA PERUBAHAN KE DATABASE)';
        CLI::write('');
        CLI::write('========================================================================================', 'yellow');
        CLI::write("MODE: {$modeLabel}", 'yellow');
        CLI::write('========================================================================================', 'yellow');

        $tableHeaders = ['ID', 'Nama Perusahaan', 'Jml Produk', 'Keyakinan', 'Status'];
        $tableData = [];
        foreach ($evaluatedRows as $r) {
            $tableData[] = [
                $r['id'],
                $r['name'],
                $r['jml_produk'],
                $r['keyakinan'],
                $r['status'],
            ];
        }
        CLI::table($tableData, $tableHeaders);

        // Ringkasan Total
        CLI::write('');
        CLI::write('============================================================', 'cyan');
        CLI::write('RINGKASAN TOTAL', 'cyan');
        CLI::write('============================================================', 'cyan');
        CLI::write(sprintf('%-35s : %d', 'Jumlah perusahaan diproses', $summary['total_perusahaan']));
        CLI::write(sprintf('%-35s : %d (Total %d produk)', 'Perusahaan akan dipindah', $summary['akan_dipindah'], $summary['total_produk_akan_dibuat']));
        CLI::write(sprintf('  %-33s : %d', '- Tingkat keyakinan TINGGI', $summary['keyakinan_tinggi']));
        CLI::write(sprintf('  %-33s : %d', '- Tingkat keyakinan RENDAH', $summary['keyakinan_rendah']));
        CLI::write(sprintf('%-35s : %d', 'Perusahaan dilewati', ($summary['total_perusahaan'] - $summary['akan_dipindah'])));
        CLI::write(sprintf('  %-33s : %d', '- Sudah punya produk', $summary['skip_sudah_punya_produk']));
        CLI::write(sprintf('  %-33s : %d', '- Kemungkinan ganda', $summary['skip_kemungkinan_ganda']));
        CLI::write(sprintf('  %-33s : %d', '- Keyakinan rendah', $summary['skip_keyakinan_rendah']));
        CLI::write(sprintf('  %-33s : %d', '- Produk dipasarkan kosong', $summary['skip_produk_kosong']));
        CLI::write('============================================================', 'cyan');

        // Daftar Kelompok Kemungkinan Ganda Terdeteksi
        CLI::write('');
        CLI::write('KELOMPOK KEMUNGKINAN GANDA TERDETEKSI:', 'yellow');
        if (empty($duplicateGroups)) {
            CLI::write('  Tidak ada kelompok ganda terdeteksi.');
        } else {
            foreach ($duplicateGroups as $idx => $grp) {
                CLI::write(sprintf('  Kelompok %d:', $idx + 1), 'yellow');
                foreach ($grp as $gid) {
                    $hasProd = ($prodCounts[$gid] ?? 0) > 0 ? " (Sudah punya {$prodCounts[$gid]} produk)" : ' (Belum punya produk)';
                    CLI::write("    - [ID {$gid}] {$nameMap[$gid]}{$hasProd}");
                }
            }
        }
        CLI::write('============================================================', 'cyan');

        // Tulis CSV jika opsi --report diberikan pada mode dry-run
        if ($canonicalReportPath !== null) {
            $this->writeReportCsv($canonicalReportPath, $evaluatedRows);
            CLI::write('');
            CLI::write("Laporan CSV dry-run disimpan di: {$canonicalReportPath}", 'green');
        }

        return EXIT_SUCCESS;
    }

    /**
     * Ambil nilai opsi CLI baik format --name=value maupun --name value
     */
    protected function getCustomOption(string $name, array $params): ?string
    {
        // 1. Cek CLI::getOption($name)
        $val = CLI::getOption($name);
        if ($val !== null && $val !== true) {
            return (string)$val;
        }

        // 2. Cek $params[$name]
        if (isset($params[$name]) && $params[$name] !== null && $params[$name] !== true) {
            return (string)$params[$name];
        }

        // 3. Cek CLI::getOptions() untuk format key "name=value"
        foreach (CLI::getOptions() as $k => $v) {
            if (str_starts_with($k, $name . '=')) {
                return substr($k, strlen($name . '='));
            }
        }

        // 4. Cek $_SERVER['argv']
        if (isset($_SERVER['argv'])) {
            foreach ($_SERVER['argv'] as $i => $arg) {
                if (str_starts_with($arg, "--{$name}=")) {
                    return substr($arg, strlen("--{$name}="));
                }
                if ($arg === "--{$name}" && isset($_SERVER['argv'][$i + 1]) && !str_starts_with($_SERVER['argv'][$i + 1], '-')) {
                    return $_SERVER['argv'][$i + 1];
                }
            }
        }

        return null;
    }

    /**
     * Cek apakah flag opsi CLI disertakan
     */
    protected function hasCustomOption(string $name, array $params): bool
    {
        if (CLI::getOption($name) !== null || array_key_exists($name, $params)) {
            return true;
        }
        foreach (CLI::getOptions() as $k => $v) {
            if ($k === $name || str_starts_with($k, $name . '=')) {
                return true;
            }
        }
        if (isset($_SERVER['argv'])) {
            foreach ($_SERVER['argv'] as $arg) {
                if ($arg === "--{$name}" || str_starts_with($arg, "--{$name}=")) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Evaluasi kelayakan pemindahan produk untuk satu supplier
     */
    protected function evaluateSupplier(
        array $s,
        array $supplierToGroup,
        array $nameMap,
        bool $includeLow,
        bool $includeDuplicates,
        bool $isExecute
    ): array {
        $id        = (int)$s['id_supplier'];
        $name      = trim((string)$s['nama_perusahaan']);
        $rawProd   = trim((string)($s['produk_dipasarkan'] ?? ''));
        $rawKap    = trim((string)($s['kapasitas_produksi'] ?? ''));
        $prodCount = (int)$s['prod_count'];

        $items = $this->parseProducts($rawProd);
        $conf  = ($rawProd !== '') ? $this->getConfidence($id, $rawProd, $items) : '-';
        $isDup = isset($supplierToGroup[$id]);

        $groupDesc = '';
        if ($isDup) {
            $members = [];
            foreach ($supplierToGroup[$id] as $mid) {
                $members[] = "ID {$mid} ({$nameMap[$mid]})";
            }
            $groupDesc = implode(' ; ', $members);
        }

        // Kapasitas hanya dibawa jika tepat memiliki 1 produk hasil pemecahan
        $kapasitasDibawa = (count($items) === 1 && $rawKap !== '') ? $rawKap : '';

        // Kasus 1: Anggota kelompok duplikat
        if ($isDup && !$includeDuplicates) {
            return [
                'id'               => $id,
                'name'             => $name,
                'teks_asli'        => $rawProd,
                'jml_produk'       => count($items),
                'keyakinan'        => $conf,
                'status'           => 'KEMUNGKINAN GANDA (menunggu keputusan)',
                'action'           => 'skip',
                'reason'           => 'kemungkinan_ganda',
                'group_desc'       => $groupDesc,
                'kapasitas_dibawa' => $kapasitasDibawa,
                'items'            => $items,
            ];
        }

        // Kasus 2: Sudah punya produk di tb_produk_supplier
        if ($prodCount > 0) {
            return [
                'id'               => $id,
                'name'             => $name,
                'teks_asli'        => $rawProd,
                'jml_produk'       => count($items),
                'keyakinan'        => $conf,
                'status'           => "Dilewati (sudah punya {$prodCount} produk)",
                'action'           => 'skip',
                'reason'           => 'sudah_punya_produk',
                'group_desc'       => $groupDesc,
                'kapasitas_dibawa' => $kapasitasDibawa,
                'items'            => $items,
            ];
        }

        // Kasus 3: produk_dipasarkan kosong
        if ($rawProd === '' || empty($items)) {
            return [
                'id'               => $id,
                'name'             => $name,
                'teks_asli'        => $rawProd,
                'jml_produk'       => 0,
                'keyakinan'        => '-',
                'status'           => 'Dilewati (produk_dipasarkan kosong)',
                'action'           => 'skip',
                'reason'           => 'produk_kosong',
                'group_desc'       => $groupDesc,
                'kapasitas_dibawa' => '',
                'items'            => [],
            ];
        }

        // Kasus 4: Tingkat keyakinan RENDAH
        if ($conf === 'RENDAH' && !$includeLow) {
            return [
                'id'               => $id,
                'name'             => $name,
                'teks_asli'        => $rawProd,
                'jml_produk'       => count($items),
                'keyakinan'        => $conf,
                'status'           => 'Dilewati (Keyakinan RENDAH)',
                'action'           => 'skip',
                'reason'           => 'keyakinan_rendah',
                'group_desc'       => $groupDesc,
                'kapasitas_dibawa' => $kapasitasDibawa,
                'items'            => $items,
            ];
        }

        // Kasus 5: Memenuhi syarat untuk dipindah
        $statusText = $isExecute ? 'Dipindah' : 'Akan dipindah';
        return [
            'id'               => $id,
            'name'             => $name,
            'teks_asli'        => $rawProd,
            'jml_produk'       => count($items),
            'keyakinan'        => $conf,
            'status'           => $statusText,
            'action'           => 'migrate',
            'reason'           => 'ok',
            'group_desc'       => $groupDesc,
            'kapasitas_dibawa' => $kapasitasDibawa,
            'items'            => $items,
        ];
    }

    /**
     * Memecah string produk_dipasarkan menjadi daftar produk bersih
     *
     * @param string $text
     * @return array<string>
     */
    protected function parseProducts(string $text): array
    {
        $raw = trim($text);
        if ($raw === '') {
            return [];
        }

        // Ganti baris baru (\r, \n) menjadi koma
        $clean = preg_replace('/[\r\n]+/', ' , ', $raw);

        // Format penomoran (contoh: "1. ", "2) ", "(1) ") dipisah sebagai pembatas,
        // dengan perlindungan terhadap angka desimal/ribuan bertitik seperti "100.000".
        // (?!\d) memastikan titik penomoran tidak diikuti oleh angka.
        $clean = preg_replace('/(?:^|[\s,;])(?:(?:dan|and)\s+)?\(?\d+(?:\.(?!\d)|\))\s*/i', ' , ', $clean);

        // Pisahkan berdasarkan koma dan titik koma
        $parts = preg_split('/\s*[,;]\s*/', $clean);

        $items = [];
        $lowerSeen = [];

        foreach ($parts as $p) {
            // Bersihkan spasi dan tanda baca di tepi
            $p = trim($p, " \t\n\r\0\x0B-:.,");

            // Buang kata "dan" atau "and" di awal kata
            $p = preg_replace('/^(dan|and)\s+/i', '', $p);
            $p = trim($p, " \t\n\r\0\x0B-:.,");

            if ($p !== '' && strtolower($p) !== 'dan' && strtolower($p) !== 'and') {
                $low = strtolower($p);
                // Deduplikasi case-insensitive
                if (!isset($lowerSeen[$low])) {
                    $lowerSeen[$low] = true;
                    $items[] = $p;
                }
            }
        }

        return $items;
    }

    /**
     * Menentukan tingkat keyakinan pemecahan teks (TINGGI atau RENDAH)
     *
     * @param int $id
     * @param string $text
     * @param array<string> $items
     * @return string 'TINGGI'|'RENDAH'
     */
    protected function getConfidence(int $id, string $text, array $items): string
    {
        // ID khusus yang eksplisit disebut ambigu
        if (in_array($id, [18, 66], true)) {
            return 'RENDAH';
        }

        $lower = strtolower($text);
        $ambiguousKeywords = [
            'seperti',
            'dll',
            'dan lain',
            'lainnya',
            'lainya',
            'tergantung',
            'aneka',
            'terbuat dari',
            'berasal dari',
            'dengan bahan baku',
        ];

        foreach ($ambiguousKeywords as $kw) {
            if (strpos($lower, $kw) !== false) {
                return 'RENDAH';
            }
        }

        // Jika jumlah item > 10 atau panjang teks > 250 karakter
        if (count($items) > 10 || strlen($text) > 250) {
            return 'RENDAH';
        }

        return 'TINGGI';
    }

    /**
     * Deteksi kelompok kemungkinan ganda antar perusahaan di tb_supplier
     *
     * @param array<array<string, mixed>> $suppliers
     * @return array<array<int>>
     */
    protected function detectDuplicateGroups(array $suppliers): array
    {
        $phoneMap = [];
        $webMap   = [];
        $igMap    = [];

        foreach ($suppliers as $s) {
            $id    = (int)$s['id_supplier'];
            $phone = $this->normalizePhone($s['no_hp'] ?? null);
            $ig    = $this->normalizeInstagram($s['instagram'] ?? null);

            if ($phone !== null) {
                $phoneMap[$phone][] = $id;
            }
            if ($ig !== null) {
                $igMap[$ig][] = $id;
            }

            $websites = $this->normalizeWebsites($s['website'] ?? null);
            foreach ($websites as $w) {
                $webMap[$w][] = $id;
            }
        }

        // Graf keterhubungan antar supplier
        $adj = [];
        foreach ([$phoneMap, $webMap, $igMap] as $map) {
            foreach ($map as $val => $ids) {
                if (count($ids) > 1) {
                    foreach ($ids as $a) {
                        foreach ($ids as $b) {
                            if ($a !== $b) {
                                $adj[$a][$b] = true;
                                $adj[$b][$a] = true;
                            }
                        }
                    }
                }
            }
        }

        // Cari connected components dengan BFS
        $visited = [];
        $groups  = [];
        foreach (array_keys($adj) as $id) {
            if (!isset($visited[$id])) {
                $component = [];
                $queue     = [$id];
                $visited[$id] = true;

                while (!empty($queue)) {
                    $curr = array_shift($queue);
                    $component[] = $curr;
                    foreach (array_keys($adj[$curr] ?? []) as $neighbor) {
                        if (!isset($visited[$neighbor])) {
                            $visited[$neighbor] = true;
                            $queue[] = $neighbor;
                        }
                    }
                }
                sort($component);
                $groups[] = $component;
            }
        }

        return $groups;
    }

    /**
     * Normalisasi nomor HP: hanya digit, buang awalan 0, 62, atau +62, min 8 digit
     */
    protected function normalizePhone(?string $val): ?string
    {
        if ($val === null) {
            return null;
        }
        $lower = strtolower(trim($val));
        if ($lower === '' || in_array($lower, ['-', 'belum', 'tidak ada', 'tidak punya', 'none', 'null', 'n/a'], true)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $val);
        $digits = preg_replace('/^62/', '', $digits);
        $digits = preg_replace('/^0+/', '', $digits);

        if (strlen($digits) < 8) {
            return null;
        }
        return $digits;
    }

    /**
     * Normalisasi daftar website: buang http/https, www, trailing slash
     *
     * @return array<string>
     */
    protected function normalizeWebsites(?string $val): array
    {
        if ($val === null) {
            return [];
        }
        $rawParts = preg_split('/\s*[,;]\s*/', trim($val));
        $res = [];

        foreach ($rawParts as $p) {
            $w = strtolower(trim($p));
            if ($w === '' || in_array($w, ['-', 'belum', 'tidak ada', 'tidak punya', 'none', 'null', 'n/a'], true)) {
                continue;
            }
            $w = preg_replace('#^https?://#i', '', $w);
            $w = preg_replace('#^www\.#i', '', $w);
            $w = rtrim($w, '/');
            $w = trim($w);
            if ($w !== '' && !in_array($w, ['-', 'belum', 'tidak ada', 'tidak punya'], true) && !in_array($w, $res, true)) {
                $res[] = $w;
            }
        }

        return $res;
    }

    /**
     * Normalisasi akun Instagram: huruf kecil, tanpa URL, tanpa @, tanpa trailing slash
     */
    protected function normalizeInstagram(?string $val): ?string
    {
        if ($val === null) {
            return null;
        }
        $ig = strtolower(trim($val));
        if ($ig === '' || in_array($ig, ['-', 'belum', 'tidak ada', 'tidak punya', 'none', 'null', 'n/a'], true)) {
            return null;
        }
        $ig = preg_replace('#^https?://(www\.)?instagram\.com/#i', '', $ig);
        $ig = preg_replace('#^(www\.)?instagram\.com/#i', '', $ig);
        $ig = ltrim($ig, '@');
        $ig = rtrim($ig, '/');
        $ig = trim($ig);

        if ($ig === '' || in_array($ig, ['-', 'belum', 'tidak ada', 'tidak punya'], true)) {
            return null;
        }
        return $ig;
    }

    /**
     * Validasi path laporan agar berada di LUAR folder project (ROOTPATH)
     *
     * @param string $path
     * @return array{valid: bool, error: ?string, canonicalPath: string}
     */
    protected function validateReportPath(string $path): array
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return ['valid' => false, 'error' => 'Path laporan tidak boleh kosong.', 'canonicalPath' => ''];
        }

        // Resolusi ROOTPATH ke format kanonikal
        $root = realpath(ROOTPATH);
        if ($root === false) {
            $root = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ROOTPATH), DIRECTORY_SEPARATOR);
        } else {
            $root = rtrim($root, DIRECTORY_SEPARATOR);
        }

        // Periksa apakah path absolut
        $isAbsolute = false;
        if (DIRECTORY_SEPARATOR === '\\') {
            if (preg_match('/^[a-zA-Z]:[\\\\\/]/', $trimmed) || str_starts_with($trimmed, '\\\\')) {
                $isAbsolute = true;
            }
        } else {
            if (str_starts_with($trimmed, '/')) {
                $isAbsolute = true;
            }
        }

        if (!$isAbsolute) {
            return [
                'valid'         => false,
                'error'         => "Path laporan harus berupa path absolut di luar folder project, bukan path relatif ('{$trimmed}').",
                'canonicalPath' => '',
            ];
        }

        $dir = dirname($trimmed);
        $realDir = realpath($dir);
        if ($realDir === false || !is_dir($realDir)) {
            return [
                'valid'         => false,
                'error'         => "Direktori tujuan laporan tidak ditemukan: '{$dir}'.",
                'canonicalPath' => '',
            ];
        }

        if (!is_writable($realDir)) {
            return [
                'valid'         => false,
                'error'         => "Direktori tujuan laporan tidak dapat ditulisi: '{$realDir}'.",
                'canonicalPath' => '',
            ];
        }

        $canonicalFile = $realDir . DIRECTORY_SEPARATOR . basename($trimmed);

        // Verifikasi apakah canonicalFile berada di dalam ROOTPATH
        $lowerRoot   = strtolower($root);
        $lowerTarget = strtolower($canonicalFile);

        if ($lowerTarget === $lowerRoot || str_starts_with($lowerTarget, $lowerRoot . DIRECTORY_SEPARATOR)) {
            return [
                'valid'         => false,
                'error'         => "Path laporan berada di dalam folder project ('{$canonicalFile}'). Laporan harus disimpan di LUAR folder project.",
                'canonicalPath' => $canonicalFile,
            ];
        }

        return ['valid' => true, 'error' => null, 'canonicalPath' => $canonicalFile];
    }

    /**
     * Tulis laporan CSV lengkap
     *
     * @param string $path
     * @param array<array<string, mixed>> $rows
     */
    protected function writeReportCsv(string $path, array $rows): void
    {
        $fp = fopen($path, 'w');
        if (!$fp) {
            CLI::error("Gagal membuka file untuk penulisan: {$path}");
            return;
        }

        // Header CSV (tanpa kolom kontak: email, nomor HP, dsb)
        fputcsv($fp, [
            'id_supplier',
            'nama_perusahaan',
            'teks_asli',
            'daftar_produk_hasil_pemecahan',
            'kapasitas_dibawa',
            'keyakinan',
            'status',
            'anggota_kelompok_ganda',
        ]);

        foreach ($rows as $r) {
            fputcsv($fp, [
                $r['id'],
                $r['name'],
                $r['teks_asli'],
                implode(' ; ', $r['items']),
                $r['kapasitas_dibawa'],
                $r['keyakinan'],
                $r['status'],
                $r['group_desc'],
            ]);
        }

        fclose($fp);
    }
}
