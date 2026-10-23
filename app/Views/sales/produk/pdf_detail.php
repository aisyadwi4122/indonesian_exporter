<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($data['product']['nama_produk'] ?? 'Detail Produk') ?></title>
    <style>
        @page {
            margin: 24px 30px 35px 30px;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1e293b;
            font-size: 10.5px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #16a34a;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .logo-cell {
            width: 160px;
            vertical-align: middle;
        }
        .logo-img {
            max-width: 150px;
            max-height: 45px;
        }
        .header-text {
            text-align: right;
            vertical-align: middle;
        }
        .header-title {
            font-size: 15px;
            font-weight: bold;
            color: #15803d;
            margin: 0;
        }
        .header-subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }

        .section-box {
            margin-bottom: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
        }
        .section-header {
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 11px;
            font-weight: bold;
            padding: 6px 10px;
            border-bottom: 1px solid #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-body {
            padding: 8px 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-table tr:last-child td {
            border-bottom: none;
        }
        .info-table .label {
            width: 150px;
            color: #64748b;
            font-weight: bold;
        }
        .info-table .value {
            color: #0f172a;
            word-wrap: break-word;
            word-break: break-all;
        }

        .product-img-box {
            width: 140px;
            vertical-align: top;
            text-align: center;
            padding-right: 12px;
        }
        .product-img {
            max-width: 130px;
            max-height: 130px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 2px;
            background: #ffffff;
        }

        .hist-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        .hist-table th {
            background-color: #f8fafc;
            color: #334155;
            padding: 5px 6px;
            border-bottom: 1px solid #cbd5e1;
            text-align: left;
            font-weight: bold;
        }
        .hist-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            word-wrap: break-word;
        }
        .hist-table tr:last-child td {
            border-bottom: none;
        }
        .empty-msg {
            text-align: center;
            padding: 15px;
            color: #64748b;
            font-style: italic;
            font-size: 10px;
        }

        .badge-pill {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            background-color: #e0f2fe;
            color: #0369a1;
        }
    </style>
</head>
<body>

    <!-- Header Dokumen PDF -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <?php if (!empty($logoData)): ?>
                    <img src="<?= $logoData ?>" class="logo-img" alt="Logo">
                <?php else: ?>
                    <strong style="color: #15803d; font-size: 14px;">INDONESIAN EXPORTER</strong>
                <?php endif; ?>
            </td>
            <td class="header-text">
                <div class="header-title">LEMBAR INFORMASI PRODUK EKSPOR</div>
                <div class="header-subtitle">
                    Dicetak pada: <?= esc($tanggal) ?> | Dokumen Area Sales (Read-Only)
                </div>
            </td>
        </tr>
    </table>

    <!-- BAGIAN 1: DATA PRODUK (DENGAN GAMBAR PRODUK) -->
    <div class="section-box">
        <div class="section-header">1. Data Produk</div>
        <div class="section-body">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <!-- Gambar Produk -->
                    <td class="product-img-box">
                        <?php if (!empty($productImageData)): ?>
                            <img src="<?= $productImageData ?>" class="product-img" alt="Foto Produk">
                        <?php elseif (!empty($logoData)): ?>
                            <img src="<?= $logoData ?>" class="product-img" alt="Logo Produk">
                        <?php endif; ?>
                        <div style="font-size: 8px; color: #94a3b8; margin-top: 4px;">Foto Produk</div>
                    </td>

                    <!-- Detail Spesifikasi Produk -->
                    <td style="vertical-align: top;">
                        <table class="info-table">
                            <tr>
                                <td class="label" style="width: 120px;">Nama Produk:</td>
                                <td class="value"><strong style="font-size: 11px;"><?= esc($data['product']['nama_produk'] ?? '-') ?></strong></td>
                            </tr>
                            <tr>
                                <td class="label">Kategori:</td>
                                <td class="value"><span class="badge-pill"><?= esc($data['product']['kategori_produk'] ?? 'Umum') ?></span></td>
                            </tr>
                            <?php if (!empty($data['product']['merk_dagang']) && $data['product']['merk_dagang'] !== '-'): ?>
                            <tr>
                                <td class="label">Merk Dagang:</td>
                                <td class="value"><?= esc($data['product']['merk_dagang']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="label">Kapasitas Produksi:</td>
                                <td class="value"><?= esc($data['product']['kapasitas_produksi'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="label">Deskripsi / Detail:</td>
                                <td class="value"><?= nl2br(esc($data['product']['deskripsi'] ?? '-')) ?></td>
                            </tr>
                            <?php if (!empty($data['product']['keunggulan']) && $data['product']['keunggulan'] !== '-'): ?>
                            <tr>
                                <td class="label">Keunggulan Produk:</td>
                                <td class="value"><?= esc($data['product']['keunggulan']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($data['product']['harga_moq']) && $data['product']['harga_moq'] !== '-'): ?>
                            <tr>
                                <td class="label">Harga / MOQ:</td>
                                <td class="value"><?= esc($data['product']['harga_moq']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($data['product']['komisi']) && $data['product']['komisi'] !== '-'): ?>
                            <tr>
                                <td class="label">Komisi Sales:</td>
                                <td class="value"><strong style="color: #15803d;"><?= esc($data['product']['komisi']) ?></strong></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- BAGIAN 2: DATA PERUSAHAAN -->
    <div class="section-box">
        <div class="section-header">2. Data Perusahaan</div>
        <div class="section-body">
            <table class="info-table">
                <tr>
                    <td class="label">Nama Perusahaan:</td>
                    <td class="value"><strong style="color: #15803d; font-size: 10.5px;"><?= esc($data['company']['nama_perusahaan'] ?? '-') ?></strong></td>
                </tr>
                <tr>
                    <td class="label">Alamat / Domisili:</td>
                    <td class="value"><?= esc($data['company']['domisili'] ?? '-') ?></td>
                </tr>
                <?php if (!empty($data['company']['wilayah_ecp']) && $data['company']['wilayah_ecp'] !== '-'): ?>
                <tr>
                    <td class="label">Wilayah Program ECP:</td>
                    <td class="value"><?= esc($data['company']['wilayah_ecp']) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="label">Kontak / PIC:</td>
                    <td class="value"><?= esc($data['company']['nama_pic'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">No. Telepon / WhatsApp:</td>
                    <td class="value"><?= esc($data['company']['no_hp'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td class="value"><?= esc($data['company']['email'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">Website / Instagram:</td>
                    <td class="value">
                        <?= esc($data['company']['website'] ?? '-') ?> 
                        <?= (!empty($data['company']['instagram']) && $data['company']['instagram'] !== '-') ? ' / IG: ' . esc($data['company']['instagram']) : '' ?>
                    </td>
                </tr>
                <tr>
                    <td class="label">Jenis Badan Usaha:</td>
                    <td class="value"><?= esc($data['company']['jenis_perusahaan'] ?? '-') ?></td>
                </tr>
                <?php if (!empty($data['company']['omset']) && $data['company']['omset'] !== '-'): ?>
                <tr>
                    <td class="label">Omset:</td>
                    <td class="value">Rp <?= esc($data['company']['omset']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($data['company']['legalitas']) && $data['company']['legalitas'] !== '-'): ?>
                <tr>
                    <td class="label">Legalitas Usaha:</td>
                    <td class="value"><?= esc($data['company']['legalitas']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($data['company']['sertifikasi']) && $data['company']['sertifikasi'] !== '-'): ?>
                <tr>
                    <td class="label">Sertifikasi:</td>
                    <td class="value"><?= esc($data['company']['sertifikasi']) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- BAGIAN 3: DATA HISTORY EKSPOR -->
    <div class="section-box">
        <div class="section-header">
            3. Data History Ekspor 
            <?php if (!empty($data['histories'])): ?>
                (Total: <?= count($data['histories']) ?> Transaksi, Nilai: $<?= number_format($data['totalNilai'] ?? 0, 0, ',', '.') ?> USD)
            <?php endif; ?>
        </div>
        <div class="section-body" style="padding: 0;">
            <?php if (empty($data['histories'])): ?>
                <div class="empty-msg">Belum ada data history ekspor</div>
            <?php else: ?>
                <table class="hist-table">
                    <thead>
                        <tr>
                            <th style="width: 25px; text-align: center;">No</th>
                            <th style="width: 45px;">Tahun</th>
                            <th>Produk Ekspor</th>
                            <th>Negara Tujuan</th>
                            <th>Jenis Ekspor</th>
                            <th>Kuantitas</th>
                            <th style="text-align: right; width: 100px;">Nilai USD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($data['histories'] as $h): ?>
                        <tr>
                            <td style="text-align: center; color: #64748b;"><?= $no++ ?></td>
                            <td><?= esc($h['tahun'] ?? '-') ?></td>
                            <td><strong><?= esc($h['produk'] ?? '-') ?></strong></td>
                            <td><?= esc($h['negara_tujuan'] ?? '-') ?></td>
                            <td><?= esc($h['jenis_ekspor'] ?? '-') ?></td>
                            <td><?= esc($h['kuantitas'] ?? '-') ?></td>
                            <td style="text-align: right; font-weight: bold; color: #15803d;">
                                <?php if (!empty($h['nilai_ekspor_usd'])): ?>
                                    $<?= number_format($h['nilai_ekspor_usd'], 2, ',', '.') ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
