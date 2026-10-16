<?php

namespace App\Commands;

use App\Models\SupplierProductModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class FetchSupplierPhotos extends BaseCommand
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
    protected $name = 'supplier:fetch-photos';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Mengunduh foto produk supplier dari Google Drive ke server lokal.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'supplier:fetch-photos [--id=ID] [--force]';

    /**
     * The Command's Options
     *
     * @var array<string, string>
     */
    protected $options = [
        '--id'    => 'ID produk supplier tertentu (mis. --id=181).',
        '--force' => 'Paksa unduh ulang meskipun file lokal sudah ada.',
    ];

    public function run(array $params)
    {
        $idParam = CLI::getOption('id');
        $force   = CLI::getOption('force') !== null;

        // Fallback parsing jika opsi dilewatkan via $params
        if ($idParam === null) {
            foreach ($params as $param) {
                if (is_string($param) && str_starts_with($param, '--id=')) {
                    $idParam = substr($param, 5);
                } elseif (is_string($param) && $param === '--force') {
                    $force = true;
                }
            }
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'supplier' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $model = new SupplierProductModel();
        if (!empty($idParam)) {
            $products = $model->where('id_produk_supplier', (int)$idParam)->findAll();
        } else {
            $products = $model->orderBy('id_produk_supplier', 'ASC')->findAll();
        }

        if (empty($products)) {
            CLI::write("Tidak ada data produk supplier yang ditemukan.", 'yellow');
            return;
        }

        $totalProducts = count($products);
        CLI::write("Memproses pengunduhan foto untuk {$totalProducts} produk supplier...", 'cyan');

        $successCount = 0;
        $skippedCount = 0;
        $failedCount  = 0;
        $failedList   = [];

        foreach ($products as $p) {
            $id = (int)$p['id_produk_supplier'];

            for ($n = 1; $n <= 5; $n++) {
                $colVal = $p['foto_' . $n] ?? '';
                if (empty($colVal) || !is_string($colVal)) {
                    continue;
                }

                $links = SupplierProductModel::splitLinks($colVal);
                if (empty($links)) {
                    continue;
                }

                // Cari ID Google Drive yang valid pertama
                $driveId = null;
                foreach ($links as $link) {
                    $foundId = SupplierProductModel::driveFileId($link);
                    if ($foundId !== null) {
                        $driveId = $foundId;
                        break;
                    }
                }

                // Lewati jika kolom bukan berisi link Google Drive yang valid
                if ($driveId === null) {
                    continue;
                }

                $targetFile = $uploadDir . $id . '_' . $n . '.jpg';

                // Idempotent: lewati jika file lokal sudah ada kecuali opsi --force
                if (is_file($targetFile) && !$force) {
                    $skippedCount++;
                    CLI::write("Produk #{$id} [foto_{$n}]: dilewati (file lokal sudah ada)", 'light_gray');
                    continue;
                }

                // Unduh dari Google Drive menggunakan cURL
                $downloadUrl = 'https://drive.google.com/uc?export=download&id=' . urlencode($driveId);
                $ch = curl_init($downloadUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 10,
                    CURLOPT_TIMEOUT        => 30,
                    CURLOPT_CONNECTTIMEOUT => 15,
                    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    CURLOPT_SSL_VERIFYPEER => true,
                ]);

                $body        = curl_exec($ch);
                $httpCode    = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $contentType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $curlErr     = curl_error($ch);
                curl_close($ch);

                if ($body === false || empty($body) || $httpCode < 200 || $httpCode >= 400) {
                    $reason = "gagal download (HTTP {$httpCode}" . ($curlErr ? " - {$curlErr}" : "") . ")";
                    $failedList[] = [
                        'id'     => $id,
                        'foto'   => $n,
                        'reason' => $reason,
                    ];
                    $failedCount++;
                    CLI::write("Produk #{$id} [foto_{$n}]: GAGAL - {$reason}", 'red');
                    continue;
                }

                // Validasi Content-Type harus berupa image
                if (!str_starts_with(strtolower($contentType), 'image/')) {
                    $reason = "bukan publik / bukan gambar";
                    $failedList[] = [
                        'id'     => $id,
                        'foto'   => $n,
                        'reason' => $reason,
                    ];
                    $failedCount++;
                    CLI::write("Produk #{$id} [foto_{$n}]: GAGAL - {$reason}", 'red');
                    continue;
                }

                // Proses dengan GD
                $src = @imagecreatefromstring($body);
                if (!$src) {
                    $reason = "gagal decode gambar dengan GD";
                    $failedList[] = [
                        'id'     => $id,
                        'foto'   => $n,
                        'reason' => $reason,
                    ];
                    $failedCount++;
                    CLI::write("Produk #{$id} [foto_{$n}]: GAGAL - {$reason}", 'red');
                    continue;
                }

                $origW = imagesx($src);
                $origH = imagesy($src);

                if ($origW <= 0 || $origH <= 0) {
                    imagedestroy($src);
                    $reason = "dimensi gambar tidak valid";
                    $failedList[] = [
                        'id'     => $id,
                        'foto'   => $n,
                        'reason' => $reason,
                    ];
                    $failedCount++;
                    CLI::write("Produk #{$id} [foto_{$n}]: GAGAL - {$reason}", 'red');
                    continue;
                }

                $maxW = 1200;
                if ($origW > $maxW) {
                    $newW = $maxW;
                    $newH = (int)round(($origH / $origW) * $maxW);
                } else {
                    $newW = $origW;
                    $newH = $origH;
                }

                $dst = imagecreatetruecolor($newW, $newH);
                $white = imagecolorallocate($dst, 255, 255, 255);
                imagefill($dst, 0, 0, $white);

                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

                $saved = imagejpeg($dst, $targetFile, 82);
                imagedestroy($src);
                imagedestroy($dst);

                if (!$saved) {
                    $reason = "gagal menyimpan file JPEG";
                    $failedList[] = [
                        'id'     => $id,
                        'foto'   => $n,
                        'reason' => $reason,
                    ];
                    $failedCount++;
                    CLI::write("Produk #{$id} [foto_{$n}]: GAGAL - {$reason}", 'red');
                } else {
                    $successCount++;
                    CLI::write("Produk #{$id} [foto_{$n}]: BERHASIL disimpan ke uploads/supplier/" . basename($targetFile), 'green');
                }
            }
        }

        // Laporan Akhir
        CLI::newLine();
        CLI::write("========================================", 'yellow');
        CLI::write("         LAPORAN PENGUNDUHAN FOTO       ", 'yellow');
        CLI::write("========================================", 'yellow');
        CLI::write("Jumlah Berhasil : " . $successCount, 'green');
        CLI::write("Jumlah Dilewati : " . $skippedCount, 'light_gray');
        CLI::write("Jumlah Gagal    : " . $failedCount, $failedCount > 0 ? 'red' : 'green');

        if (!empty($failedList)) {
            CLI::newLine();
            CLI::write("Daftar Foto Gagal:", 'red');
            CLI::table(
                array_map(function ($f) {
                    return [
                        'ID Produk'  => $f['id'],
                        'Nomor Foto' => 'foto_' . $f['foto'],
                        'Alasan'     => $f['reason'],
                    ];
                }, $failedList),
                ['ID Produk', 'Nomor Foto', 'Alasan']
            );
        }
    }
}
