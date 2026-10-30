<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Produk</title>
    <style>
        @page {
            margin: 18mm 15mm 18mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #16a34a;
            padding-bottom: 6px;
            margin-bottom: 14px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #166534;
            border-bottom: 1px solid #bbf7d0;
            padding-bottom: 4px;
            margin-top: 14px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 10.5px;
        }
        .info-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .info-label {
            width: 32%;
            font-weight: bold;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-value {
            width: 68%;
            color: #0f172a;
            border-bottom: 1px solid #f1f5f9;
        }
        .desc-box {
            font-size: 10.5px;
            line-height: 1.5;
            color: #334155;
            margin-bottom: 8px;
            text-align: justify;
        }
        .desc-box table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }
        .desc-box th, .desc-box td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 10px;
        }
        .desc-box th {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 10px;
        }
        .history-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-weight: bold;
            color: #1e293b;
        }
        .history-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 6px;
            color: #334155;
        }
        .badge-kategori {
            background-color: #dcfce7;
            color: #15803d;
            font-size: 10px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        .img-placeholder {
            width: 180px;
            height: 140px;
            background-color: #f1f5f9;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            text-align: center;
            line-height: 140px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: bold;
        }
        .img-container {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px;
            text-align: center;
            background: #ffffff;
            width: 180px;
        }
    </style>
</head>
<body>
<?php 
$totalItems = count($items);
foreach ($items as $index => $item): 
    $product = $item['product'] ?? [];
    $produkSupplier = $item['produkSupplier'] ?? null;
    $supplier = $item['supplier'] ?? null;
    $histories = $item['histories'] ?? [];
    $totalNilai = $item['totalNilai'] ?? 0.0;
    $imageData = $item['imageData'] ?? null;
    $descIdClean = $item['deskripsi_id_sanitized'] ?? '';
    $descEnClean = $item['deskripsi_en_sanitized'] ?? '';

    $namaId = !empty($product['nama_produk_id']) ? $product['nama_produk_id'] : ($product['nama_produk'] ?? 'Produk');
    $namaEn = !empty($product['nama_produk_en']) ? $product['nama_produk_en'] : '';
    $kategori = !empty($product['nama_kategori_id']) ? $product['nama_kategori_id'] : ($product['nama_kategori_en'] ?? '');

    $isLast = ($index === $totalItems - 1);
?>
<div class="<?= $isLast ? '' : 'page-break' ?>">
    <!-- Header Kecil -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <?php if (!empty($logoData)): ?>
                    <img src="<?= $logoData ?>" style="height: 28px; vertical-align: middle; margin-right: 6px;" alt="Logo">
                <?php endif; ?>
                <span style="font-size: 13px; font-weight: bold; color: #16a34a; vertical-align: middle;">
                    Indonesian Exporter
                </span>
                <span style="font-size: 11px; color: #64748b; vertical-align: middle;">
                    &mdash; Katalog Produk
                </span>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: middle; font-size: 10px; color: #64748b;">
                Tanggal Cetak: <?= esc($tanggal) ?>
            </td>
        </tr>
    </table>

    <!-- Foto & Judul Produk -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
        <tr>
            <td style="width: 200px; vertical-align: top; padding-right: 14px;">
                <?php if (!empty($imageData)): ?>
                    <div class="img-container">
                        <img src="<?= $imageData ?>" style="max-width: 180px; max-height: 160px;" alt="Foto Produk">
                    </div>
                <?php else: ?>
                    <div class="img-placeholder">
                        Foto tidak tersedia
                    </div>
                <?php endif; ?>
            </td>
            <td style="vertical-align: top;">
                <h1 style="font-size: 18px; font-weight: bold; color: #0f172a; margin: 0 0 4px 0; line-height: 1.3;">
                    <?= esc($namaId) ?>
                </h1>
                <?php if (!empty($namaEn) && $namaEn !== $namaId): ?>
                    <?php if (!empty($is_internal)): ?>
                        <div style="font-size: 13px; font-style: italic; color: #64748b; margin-bottom: 6px;">
                            <?= esc($namaEn) ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <div style="margin-top: 4px;">
                    <?php if (!empty($kategori)): ?>
                        <span class="badge-kategori">
                            Kategori: <?= esc($kategori) ?>
                        </span>
                    <?php endif; ?>
                    <?php $codeToDisplay = $item['kode_produk'] ?? ($product['kode_produk'] ?? ''); ?>
                    <?php if (!empty($codeToDisplay)): ?>
                        <span class="badge-kategori" style="background-color: #f1f5f9; color: #334155; margin-left: 4px;">
                            Kode: <?= esc($codeToDisplay) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- Bagian Deskripsi -->
    <?php if (!empty($descIdClean) || !empty($descEnClean)): ?>
    <div>
        <div class="section-title">Deskripsi</div>
        <?php if (!empty($descIdClean)): ?>
            <div class="desc-box">
                <?= $descIdClean ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($descEnClean) && $descEnClean !== $descIdClean): ?>
            <div style="font-size: 11px; font-weight: bold; color: #64748b; margin-top: 6px; margin-bottom: 3px;">
                Deskripsi (EN):
            </div>
            <div class="desc-box">
                <?= $descEnClean ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Bagian Informasi Ekspor Produk -->
    <?php
        $exportInfo = [];
        if (!empty($produkSupplier)) {
            $fields = [
                'Spesifikasi'        => $produkSupplier['spesifikasi'] ?? null,
                'Kapasitas Produksi' => $produkSupplier['kapasitas_produksi'] ?? null,
                'Legalitas'          => (!empty($is_internal)) ? ($produkSupplier['legalitas'] ?? null) : null,
                'Sertifikasi'        => $produkSupplier['sertifikasi'] ?? null,
                'Lokasi Barang'      => $produkSupplier['lokasi_barang'] ?? null,
                'MOQ'                => $produkSupplier['moq'] ?? null,
                'Harga EXW'          => $produkSupplier['harga_exw'] ?? null,
                'Komisi'             => (!empty($is_internal)) ? ($produkSupplier['komisi'] ?? null) : null,
                'Sudah Ekspor'       => $produkSupplier['sudah_ekspor'] ?? null,
            ];
            foreach ($fields as $label => $val) {
                if ($val !== null) {
                    $valStr = trim((string)$val);
                    if ($valStr !== '' && $valStr !== '-' && stripos($valStr, '$0 USD') === false && $valStr !== '$0' && $valStr !== '0') {
                        $exportInfo[$label] = $valStr;
                    }
                }
            }
        }
    ?>
    <?php if (!empty($exportInfo)): ?>
    <div>
        <div class="section-title">Informasi Ekspor Produk</div>
        <table class="info-table">
            <?php foreach ($exportInfo as $label => $val): ?>
            <tr>
                <td class="info-label"><?= esc($label) ?></td>
                <td class="info-value"><?= nl2br(esc($val)) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- Bagian Data Perusahaan / Profil Usaha -->
    <?php
        $companyInfo = [];
        $contactInfo = [];
        $companyTitle = !empty($is_internal) ? 'Data Perusahaan' : 'Profil Usaha';
        if (!empty($supplier)) {
            if (!empty($is_internal)) {
                $contactFields = [
                    'No. HP'      => $supplier['no_hp'] ?? null,
                    'Email'       => $supplier['email'] ?? null,
                    'Website'     => $supplier['website'] ?? null,
                    'Instagram'   => $supplier['instagram'] ?? null,
                ];
                foreach ($contactFields as $label => $val) {
                    if ($val !== null && trim((string)$val) !== '') {
                        $contactInfo[$label] = trim((string)$val);
                    }
                }

                $fields = [
                    'Nama Perusahaan'        => $supplier['nama_perusahaan'] ?? null,
                    'Domisili Usaha'         => $supplier['domisili_usaha'] ?? null,
                    'Produk yang Dipasarkan' => $supplier['produk_dipasarkan'] ?? null,
                    'Jenis Perusahaan'       => $supplier['jenis_perusahaan'] ?? null,
                    'Kapasitas Produksi'     => $supplier['kapasitas_produksi'] ?? null,
                    'Jumlah Pegawai'         => $supplier['jumlah_pegawai'] ?? null,
                    'Frekuensi Ekspor'       => $supplier['frekuensi_ekspor'] ?? null,
                    'Pameran Internasional'  => $supplier['pameran_internasional'] ?? null,
                    'Keunggulan Produk'      => $supplier['keunggulan_produk'] ?? null,
                    'Legalitas Usaha'        => $supplier['legalitas'] ?? null,
                    'Sertifikasi Usaha'      => $supplier['sertifikasi'] ?? null,
                    'Pernah Ekspor'          => $supplier['pernah_ekspor'] ?? null,
                    'Kategori Ekspor'        => $supplier['kategori_ekspor'] ?? null,
                ];
            } else {
                // Mode eksternal: HANYA field non-identitas
                $fields = [
                    'Jenis Perusahaan'       => $supplier['jenis_perusahaan'] ?? null,
                    'Domisili Usaha'         => $supplier['domisili_usaha'] ?? null,
                    'Produk yang Dipasarkan' => $supplier['produk_dipasarkan'] ?? null,
                    'Kapasitas Produksi'     => $supplier['kapasitas_produksi'] ?? null,
                    'Jumlah Pegawai'         => $supplier['jumlah_pegawai'] ?? null,
                    'Frekuensi Ekspor'       => $supplier['frekuensi_ekspor'] ?? null,
                    'Keunggulan Produk'      => $supplier['keunggulan_produk'] ?? null,
                    'Sertifikasi Usaha'      => $supplier['sertifikasi'] ?? null,
                    'Pernah Ekspor'          => $supplier['pernah_ekspor'] ?? null,
                    'Kategori Ekspor'        => $supplier['kategori_ekspor'] ?? null,
                ];
            }
            foreach ($fields as $label => $val) {
                if ($val !== null && trim((string)$val) !== '') {
                    $companyInfo[$label] = trim((string)$val);
                }
            }
        }
    ?>
    <?php if (!empty($companyInfo)): ?>
    <div>
        <div class="section-title"><?= esc($companyTitle) ?></div>
        <?php if (!empty($is_internal) && !empty($contactInfo)): ?>
            <div style="font-size: 10.5px; font-weight: bold; color: #166534; margin-top: 4px; margin-bottom: 2px;">
                Informasi Kontak:
            </div>
            <table class="info-table" style="margin-bottom: 8px;">
                <?php foreach ($contactInfo as $label => $val): ?>
                <tr>
                    <td class="info-label"><?= esc($label) ?></td>
                    <td class="info-value"><?= esc($val) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        <table class="info-table">
            <?php foreach ($companyInfo as $label => $val): ?>
            <tr>
                <td class="info-label"><?= esc($label) ?></td>
                <td class="info-value"><?= nl2br(esc($val)) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- Bagian Data History Ekspor (sembunyikan jika histories kosong) -->
    <?php if (!empty($histories)): ?>
    <div>
        <div class="section-title">Data History Ekspor</div>
        <table class="history-table">
            <thead>
                <tr>
                    <th style="width: 12%; text-align: center;">Tahun</th>
                    <th style="width: 32%; text-align: left;">Produk</th>
                    <th style="width: 24%; text-align: left;">Negara Tujuan</th>
                    <th style="width: 18%; text-align: left;">Jenis Ekspor</th>
                    <th style="width: 14%; text-align: left;">Kuantitas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($histories as $h): ?>
                <tr>
                    <td style="text-align: center;"><?= esc($h['tahun'] ?? '-') ?></td>
                    <td><?= esc($h['produk'] ?? '-') ?></td>
                    <td><?= esc($h['negara_tujuan'] ?? '-') ?></td>
                    <td><?= esc($h['jenis_ekspor'] ?? '-') ?></td>
                    <td><?= esc($h['kuantitas'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>
<?php endforeach; ?>
</body>
</html>
