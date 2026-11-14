<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportExcelSeeder extends Seeder
{
    public function run()
    {
        $importDir = WRITEPATH . 'import' . DIRECTORY_SEPARATOR;

        $file2024 = $importDir . 'ECP 2024 - EKSPOR DI ATAS 1000 USD.xlsx';
        $file2025 = $importDir . '_ECP 2025 - EKSPOR DI ATAS 1000 USD.xlsx';
        $fileSupplier = $importDir . 'List Produk Supplier IE (Before TEI 2026)  (Responses).xlsx';

        // 1. Kosongkan tabel tujuan agar aman dijalankan ulang (idempotent)
        $this->db->table('tb_ecp_ekspor')->truncate();
        $this->db->table('tb_supplier_produk')->truncate();

        // Kosongkan juga tabel supplier relasional jika ada
        $this->db->disableForeignKeyChecks();
        $this->db->table('tb_history_export_supplier')->truncate();
        $this->db->table('tb_produk_supplier')->truncate();
        $this->db->table('tb_supplier')->truncate();
        $this->db->enableForeignKeyChecks();

        $stats = [
            'ecp_2024' => ['imported' => 0, 'skipped' => 0, 'reasons' => []],
            'ecp_2025' => ['imported' => 0, 'skipped' => 0, 'reasons' => []],
            'supplier' => ['imported' => 0, 'skipped' => 0, 'reasons' => []],
        ];

        // 2. Impor ECP 2024
        if (file_exists($file2024)) {
            $this->importEcpFile($file2024, 2024, $stats['ecp_2024']);
        } else {
            echo "File ECP 2024 tidak ditemukan di $file2024\n";
        }

        // 3. Impor ECP 2025
        if (file_exists($file2025)) {
            $this->importEcpFile($file2025, 2025, $stats['ecp_2025']);
        } else {
            echo "File ECP 2025 tidak ditemukan di $file2025\n";
        }

        // 4. Impor List Produk Supplier
        if (file_exists($fileSupplier)) {
            $this->importSupplierFile($fileSupplier, $stats['supplier']);
        } else {
            echo "File Supplier tidak ditemukan di $fileSupplier\n";
        }

        // Cetak Laporan Impor
        echo "\n================ HASIL IMPOR EXCEL ================\n";
        echo "ECP 2024: {$stats['ecp_2024']['imported']} baris berhasil diimpor, {$stats['ecp_2024']['skipped']} baris kosong/dilewati.\n";
        echo "ECP 2025: {$stats['ecp_2025']['imported']} baris berhasil diimpor, {$stats['ecp_2025']['skipped']} baris kosong/dilewati.\n";
        echo "Supplier: {$stats['supplier']['imported']} baris berhasil diimpor, {$stats['supplier']['skipped']} baris kosong/dilewati.\n";
        echo "====================================================\n\n";
    }

    private function importEcpFile(string $filePath, int $year, array &$stat): void
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($filePath)->getSheet(0);

        $highestRow = $sheet->getHighestRow();

        $currentCompany = [
            'nama_peserta'      => '',
            'nama_perusahaan'   => '',
            'email'             => '',
            'no_hp'             => '',
            'domisili'          => '',
            'wilayah_ecp'       => '',
            'kategori_produk'   => '',
            'produk_dipasarkan' => '',
            'merk_dagang'       => '',
            'jenis_perusahaan'  => '',
            'kapasitas_produksi'=> '',
            'omset'             => '',
            'website'           => '',
            'instagram'         => '',
        ];

        $now = date('Y-m-d H:i:s');

        for ($r = 3; $r <= $highestRow; $r++) {
            $hVal = trim((string)$sheet->getCell("H$r")->getValue());

            // Periksa apakah seluruh baris kosong
            $hasData = false;
            foreach (['B', 'C', 'D', 'E', 'F', 'H', 'M', 'AS', 'AT', 'AU', 'AV', 'AW'] as $col) {
                if (trim((string)$sheet->getCell("$col$r")->getValue()) !== '') {
                    $hasData = true;
                    break;
                }
            }

            if (!$hasData) {
                $stat['skipped']++;
                continue;
            }

            // Jika ada Nama Perusahaan di kolom H, perbarui data perusahaan saat ini
            if ($hVal !== '') {
                $currentCompany = [
                    'nama_peserta'      => trim((string)$sheet->getCell("C$r")->getValue()),
                    'nama_perusahaan'   => $hVal,
                    'email'             => trim((string)$sheet->getCell("B$r")->getValue()),
                    'no_hp'             => $this->cleanPhone((string)$sheet->getCell("F$r")->getValue()),
                    'domisili'          => trim((string)$sheet->getCell("E$r")->getValue()),
                    'wilayah_ecp'       => trim((string)$sheet->getCell("D$r")->getValue()),
                    'kategori_produk'   => trim((string)$sheet->getCell("L$r")->getValue()),
                    'produk_dipasarkan' => trim((string)$sheet->getCell("M$r")->getValue()),
                    'merk_dagang'       => trim((string)$sheet->getCell("N$r")->getValue()),
                    'jenis_perusahaan'  => trim((string)$sheet->getCell("O$r")->getValue()),
                    'kapasitas_produksi'=> trim((string)$sheet->getCell("P$r")->getValue()),
                    'omset'             => trim((string)$sheet->getCell("Q$r")->getValue()),
                    'website'           => trim((string)$sheet->getCell("S$r")->getValue()),
                    'instagram'         => trim((string)$sheet->getCell("V$r")->getValue()),
                ];
            }

            // Data ekspor
            $produkEkspor   = trim((string)$sheet->getCell("AS$r")->getValue());
            $negaraTujuan   = trim((string)$sheet->getCell("AT$r")->getValue());
            $jenisEkspor    = trim((string)$sheet->getCell("AU$r")->getValue());
            $kuantitasEkspor= trim((string)$sheet->getCell("AV$r")->getValue());
            $rawNilai       = trim((string)$sheet->getCell("AW$r")->getValue());
            $nilaiUsd       = $this->parseNumeric($rawNilai);

            // Jika produk ekspor kosong, gunakan produk yang dipasarkan
            if ($produkEkspor === '' && !empty($currentCompany['produk_dipasarkan'])) {
                $produkEkspor = $currentCompany['produk_dipasarkan'];
            }

            $record = array_merge($currentCompany, [
                'tahun'            => $year,
                'produk_ekspor'    => $produkEkspor,
                'negara_tujuan'    => $negaraTujuan,
                'jenis_ekspor'     => $jenisEkspor,
                'kuantitas_ekspor' => $kuantitasEkspor,
                'nilai_ekspor_usd' => $nilaiUsd,
                'nilai_ekspor_raw' => $rawNilai !== '' ? $rawNilai : null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            $this->db->table('tb_ecp_ekspor')->insert($record);
            $stat['imported']++;

            // Sinkronkan juga ke tb_supplier dan tb_history_export_supplier
            $this->syncToLegacySupplier($currentCompany, $year, $produkEkspor, $negaraTujuan, $jenisEkspor, $kuantitasEkspor, $nilaiUsd, $now);
        }
    }

    private function importSupplierFile(string $filePath, array &$stat): void
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($filePath)->getSheet(0);

        $highestRow = $sheet->getHighestRow();
        $now = date('Y-m-d H:i:s');

        for ($r = 2; $r <= $highestRow; $r++) {
            $namaPerusahaan = trim((string)$sheet->getCell("D$r")->getValue());
            $namaProduk     = trim((string)$sheet->getCell("K$r")->getValue());

            if ($namaPerusahaan === '' && $namaProduk === '') {
                $stat['skipped']++;
                continue;
            }

            $record = [
                'timestamp_form'       => trim((string)$sheet->getCell("A$r")->getValue()),
                'email_pengirim'       => trim((string)$sheet->getCell("B$r")->getValue()),
                'nama_pic'             => trim((string)$sheet->getCell("C$r")->getValue()),
                'nama_perusahaan'      => $namaPerusahaan !== '' ? $namaPerusahaan : 'Supplier Tanpa Nama',
                'no_hp'                => $this->cleanPhone((string)$sheet->getCell("E$r")->getValue()),
                'email_perusahaan'     => trim((string)$sheet->getCell("F$r")->getValue()),
                'website'              => trim((string)$sheet->getCell("G$r")->getValue()),
                'instagram'            => trim((string)$sheet->getCell("H$r")->getValue()),
                'lokasi'               => trim((string)$sheet->getCell("I$r")->getValue()),
                'jenis_usaha'          => trim((string)$sheet->getCell("J$r")->getValue()),
                'nama_produk'          => $namaProduk,
                'spesifikasi_kapasitas'=> trim((string)$sheet->getCell("L$r")->getValue()),
                'legalitas'            => trim((string)$sheet->getCell("M$r")->getValue()),
                'sertifikasi'          => trim((string)$sheet->getCell("N$r")->getValue()),
                'foto_produk'          => trim((string)$sheet->getCell("O$r")->getValue()),
                'harga_moq'            => trim((string)$sheet->getCell("Q$r")->getValue()),
                'komisi'               => trim((string)$sheet->getCell("R$r")->getValue()),
                'created_at'           => $now,
                'updated_at'           => $now,
            ];

            $this->db->table('tb_supplier_produk')->insert($record);
            $stat['imported']++;

            // Sinkronkan juga ke tb_supplier & tb_produk_supplier
            $this->syncFormToLegacySupplier($record, $now);
        }
    }

    private function syncToLegacySupplier(array $c, int $year, string $produk, string $negara, string $jenis, string $qty, ?float $nilaiUsd, string $now): void
    {
        $namaPerusahaan = $c['nama_perusahaan'];
        if (empty($namaPerusahaan)) {
            return;
        }

        // Normalisasi nama kunci untuk unique key tb_supplier
        $namaKunci = strtolower(trim(preg_replace('/[^a-z0-9]/i', '', $namaPerusahaan)));
        if (empty($namaKunci)) {
            $namaKunci = 'supplier_' . substr(md5($namaPerusahaan), 0, 10);
        }

        $existing = $this->db->table('tb_supplier')->where('nama_kunci', $namaKunci)->get()->getRowArray();
        if ($existing) {
            $idSupplier = (int)$existing['id_supplier'];
        } else {
            $supplierData = [
                'nama_perusahaan'    => $namaPerusahaan,
                'nama_kunci'         => $namaKunci,
                'nama_kontak'        => $c['nama_peserta'] ?: null,
                'email'              => $c['email'] ?: null,
                'no_hp'              => $c['no_hp'] ?: null,
                'website'            => $c['website'] ?: null,
                'instagram'          => $c['instagram'] ?: null,
                'domisili_usaha'     => $c['domisili'] ?: null,
                'produk_dipasarkan'  => $c['produk_dipasarkan'] ?: null,
                'jenis_perusahaan'   => $c['jenis_perusahaan'] ?: null,
                'kapasitas_produksi' => $c['kapasitas_produksi'] ?: null,
                'sumber_tahun'       => (string)$year,
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
            $this->db->table('tb_supplier')->insert($supplierData);
            $idSupplier = (int)$this->db->insertID();
        }

        // Insert ke tb_history_export_supplier jika ada produk ekspor
        if (!empty($produk) || !empty($negara) || $nilaiUsd !== null) {
            $this->db->table('tb_history_export_supplier')->insert([
                'id_supplier'      => $idSupplier,
                'tahun'            => $year,
                'produk'           => $produk ?: ($c['produk_dipasarkan'] ?: 'Produk Ekspor'),
                'negara_tujuan'    => $negara ?: null,
                'jenis_ekspor'     => $jenis ?: null,
                'kuantitas'        => $qty ?: null,
                'nilai_ekspor_usd' => $nilaiUsd,
                'created_at'       => $now,
            ]);
        }
    }

    private function syncFormToLegacySupplier(array $r, string $now): void
    {
        $namaPerusahaan = $r['nama_perusahaan'];
        $namaKunci = strtolower(trim(preg_replace('/[^a-z0-9]/i', '', $namaPerusahaan)));
        if (empty($namaKunci)) {
            $namaKunci = 'supplier_form_' . substr(md5($namaPerusahaan), 0, 10);
        }

        $existing = $this->db->table('tb_supplier')->where('nama_kunci', $namaKunci)->get()->getRowArray();
        if ($existing) {
            $idSupplier = (int)$existing['id_supplier'];
        } else {
            $supplierData = [
                'nama_perusahaan'    => $namaPerusahaan,
                'nama_kunci'         => $namaKunci,
                'nama_kontak'        => $r['nama_pic'] ?: null,
                'email'              => $r['email_perusahaan'] ?: ($r['email_pengirim'] ?: null),
                'no_hp'              => $r['no_hp'] ?: null,
                'website'            => $r['website'] ?: null,
                'instagram'          => $r['instagram'] ?: null,
                'domisili_usaha'     => $r['lokasi'] ?: null,
                'jenis_perusahaan'   => $r['jenis_usaha'] ?: null,
                'kapasitas_produksi' => $r['spesifikasi_kapasitas'] ?: null,
                'legalitas'          => $r['legalitas'] ?: null,
                'sertifikasi'        => $r['sertifikasi'] ?: null,
                'sumber_tahun'       => '2026',
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
            $this->db->table('tb_supplier')->insert($supplierData);
            $idSupplier = (int)$this->db->insertID();
        }

        // Ambil foto link (Google Drive) jika ada
        $fotos = array_filter(array_map('trim', explode(',', $r['foto_produk'] ?? '')));
        $foto1 = $fotos[0] ?? null;
        $foto2 = $fotos[1] ?? null;
        $foto3 = $fotos[2] ?? null;

        // Insert ke tb_produk_supplier
        $this->db->table('tb_produk_supplier')->insert([
            'id_supplier'        => $idSupplier,
            'nama_produk'        => $r['nama_produk'],
            'spesifikasi'        => $r['spesifikasi_kapasitas'] ?: null,
            'kapasitas_produksi' => $r['spesifikasi_kapasitas'] ?: null,
            'legalitas'          => $r['legalitas'] ?: null,
            'sertifikasi'        => $r['sertifikasi'] ?: null,
            'lokasi_barang'      => $r['lokasi'] ?: null,
            'foto_1'             => $foto1,
            'foto_2'             => $foto2,
            'foto_3'             => $foto3,
            'moq'                => $r['harga_moq'] ?: null,
            'harga_exw'          => $r['harga_moq'] ?: null,
            'komisi'             => $r['komisi'] ?: null,
            'sudah_ekspor'       => null,
            'views'              => 0,
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);
    }

    private function parseNumeric(?string $raw): ?float
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $str = trim($raw);
        if (preg_match('/^\d{1,3}(\.\d{3})+,\d+$/', $str)) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (preg_match('/^\d+,\d+$/', $str)) {
            $str = str_replace(',', '.', $str);
        } else {
            $cleaned = preg_replace('/[^0-9.,]/', '', $str);
            if (strpos($cleaned, ',') !== false && strpos($cleaned, '.') === false) {
                $cleaned = str_replace(',', '.', $cleaned);
            } else {
                $cleaned = str_replace(',', '', $cleaned);
            }
            $str = $cleaned;
        }
        return is_numeric($str) ? (float)$str : null;
    }

    private function cleanPhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }
        $p = trim($phone);
        $p = ltrim($p, '=+');
        return $p;
    }
}
