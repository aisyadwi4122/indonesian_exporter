<?php
$decodeArray = function ($raw) {
    if (is_array($raw)) return $raw;
    if (empty($raw)) return [];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : (!empty($raw) ? [$raw] : []);
};

$dipasarkanList = $decodeArray($product['produk_dipasarkan'] ?? '');
$siapEksporList = $decodeArray($product['produk_siap_ekspor'] ?? '');

$dipasarkanText = !empty($dipasarkanList) ? implode(', ', $dipasarkanList) : '-';
$siapEksporText = !empty($siapEksporList) ? implode(', ', $siapEksporList) : '-';

// Penanganan gambar lokal menjadi Base64 untuk kompatibilitas maksimal Dompdf
$imageDataUri = null;
if (!empty($product['gambar_utama'])) {
    $rawPath = $product['gambar_utama'];
    $possiblePaths = [
        FCPATH . ltrim($rawPath, '/\\'),
        FCPATH . 'uploads/products/' . basename($rawPath),
        FCPATH . 'assets/img/produk/' . basename($rawPath),
    ];
    foreach ($possiblePaths as $path) {
        if (file_exists($path) && is_file($path)) {
            $mime = mime_content_type($path) ?: 'image/jpeg';
            $fileData = file_get_contents($path);
            if ($fileData !== false) {
                $imageDataUri = 'data:' . $mime . ';base64,' . base64_encode($fileData);
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Produk - <?= esc($product['nama_produk'] ?? ''); ?></title>
    <style>
        @page {
            margin: 24mm 20mm 20mm 20mm;
            size: A4 portrait;
        }
        body {
            font-family: sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f766e;
            margin: 0;
        }
        .header-subtitle {
            font-size: 14px;
            font-weight: bold;
            color: #334155;
            margin-top: 4px;
        }
        .header-meta {
            font-size: 11px;
            color: #64748b;
            text-align: right;
        }
        .section-title {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 12px;
            padding: 6px 10px;
            border-left: 4px solid #0d9488;
            margin-top: 14px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 12px;
        }
        .info-table .label {
            width: 32%;
            color: #475569;
            font-weight: bold;
        }
        .info-table .separator {
            width: 3%;
            text-align: center;
            color: #475569;
        }
        .info-table .value {
            width: 65%;
            color: #0f172a;
        }
        .product-image-container {
            text-align: center;
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .product-image {
            max-height: 180px;
            max-width: 260px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- Header: Judul "Detail Produk" + Nama Produk + Tanggal Cetak -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="header-title">DETAIL PRODUK</div>
                <div class="header-subtitle"><?= esc($product['nama_produk'] ?? ''); ?></div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    Kategori: <strong><?= esc($product['kategori'] ?? 'Umum'); ?></strong>
                </div>
            </td>
            <td class="header-meta" style="vertical-align: middle;">
                <div>Tanggal Cetak:</div>
                <div style="font-weight: bold; color: #1e293b;"><?= esc($printed_at ?? date('d-m-Y H:i')); ?></div>
                <div style="margin-top: 3px; font-size: 10px; color: #0d9488;">Indonesian Exporter - Sales Module</div>
            </td>
        </tr>
    </table>

    <!-- Gambar Produk (Kalau ada) -->
    <?php if (!empty($imageDataUri)) : ?>
        <div class="product-image-container">
            <img src="<?= $imageDataUri; ?>" class="product-image" alt="Foto Produk">
        </div>
    <?php endif; ?>

    <!-- Deskripsi Ringkas -->
    <?php if (!empty($product['deskripsi'])) : ?>
        <div style="margin-bottom: 12px; padding: 8px 10px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-size: 11.5px; color: #334155;">
            <strong>Deskripsi:</strong> <?= nl2br(esc($product['deskripsi'])); ?>
        </div>
    <?php endif; ?>

    <!-- 🏢 INFORMASI PERUSAHAAN -->
    <div class="section-title">🏢 INFORMASI PERUSAHAAN</div>
    <table class="info-table">
        <tr>
            <td class="label">• Domisili Usaha</td>
            <td class="separator">:</td>
            <td class="value"><?= esc($product['domisili_usaha'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label">• Jenis Perusahaan</td>
            <td class="separator">:</td>
            <td class="value"><?= esc($product['jenis_perusahaan'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label">• Jumlah Pegawai</td>
            <td class="separator">:</td>
            <td class="value"><?= esc($product['jumlah_pegawai'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label">• Kapasitas Produksi</td>
            <td class="separator">:</td>
            <td class="value"><?= esc($product['kapasitas_produksi'] ?? '-'); ?></td>
        </tr>
    </table>

    <!-- 🛒 INFORMASI PRODUK -->
    <div class="section-title">🛒 INFORMASI PRODUK</div>
    <table class="info-table">
        <tr>
            <td class="label">• Produk yang Dipasarkan</td>
            <td class="separator">:</td>
            <td class="value"><?= esc($dipasarkanText); ?></td>
        </tr>
        <tr>
            <td class="label">• Kapasitas Produksi</td>
            <td class="separator">:</td>
            <td class="value"><?= esc($product['kapasitas_produksi'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label">• Produk Siap Ekspor</td>
            <td class="separator">:</td>
            <td class="value"><?= esc($siapEksporText); ?></td>
        </tr>
    </table>

    <!-- 🌏 INFORMASI EKSPOR -->
    <div class="section-title">🌏 INFORMASI EKSPOR</div>
    <table class="info-table">
        <tr>
            <td class="label">• Frekuensi Ekspor</td>
            <td class="separator">:</td>
            <td class="value"><?= esc($product['frekuensi_ekspor'] ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label">• Produk Siap Ekspor</td>
            <td class="separator">:</td>
            <td class="value"><?= esc($siapEksporText); ?></td>
        </tr>
    </table>

    <!-- Footer PDF -->
    <div class="footer">
        Dokumen ini diterbitkan oleh Modul Sales - Indonesian Exporter secara otomatis • Halaman 1
    </div>

</body>
</html>
