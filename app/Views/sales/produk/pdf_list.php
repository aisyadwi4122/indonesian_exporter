<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Produk Sales</title>
    <style>
        @page {
            margin: 25px 25px 35px 25px;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1e293b;
            font-size: 9.5px;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #16a34a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .logo-cell {
            width: 180px;
            vertical-align: middle;
        }
        .logo-img {
            max-width: 160px;
            max-height: 40px;
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
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }

        .meta-summary {
            margin-bottom: 10px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 9px;
            color: #475569;
        }

        .list-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .list-table th {
            background-color: #15803d;
            color: #ffffff;
            padding: 6px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #166534;
        }
        .list-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-word;
        }
        .list-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
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
                <div class="header-title">KATALOG DAFTAR PRODUK</div>
                <div class="header-subtitle">
                    Dicetak pada: <?= esc($tanggal) ?> | Dokumen Area Sales (Read-Only)
                </div>
            </td>
        </tr>
    </table>

    <div class="meta-summary">
        <strong>Total Produk:</strong> <?= count($items) ?> item
        <?php if (!empty($q)): ?>
            | <strong>Kata Kunci Pencarian:</strong> "<?= esc($q) ?>"
        <?php endif; ?>
        | <strong>Area:</strong> Sales (Read-Only)
    </div>

    <table class="list-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th style="width: 200px;">Nama Produk</th>
                <th style="width: 180px;">Nama Perusahaan</th>
                <th style="width: 130px;">Lokasi / Domisili</th>
                <th style="width: 100px;">Kategori</th>
                <th>Kapasitas / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($items as $it): ?>
            <tr>
                <td class="text-center" style="color: #64748b;"><?= $no++ ?></td>
                <td><strong style="color: #0f172a;"><?= esc($it['nama_produk']) ?></strong></td>
                <td><?= esc($it['nama_perusahaan']) ?></td>
                <td><?= esc($it['lokasi']) ?></td>
                <td><?= esc($it['kategori']) ?></td>
                <td>
                    <?= esc($it['kapasitas']) ?>
                    <?php if (!empty($it['negara_tujuan'])): ?>
                        <br><span style="color: #0369a1; font-size: 8px;">Tujuan: <?= esc($it['negara_tujuan']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($it['nilai_ekspor_usd'])): ?>
                        <br><strong style="color: #15803d; font-size: 8.5px;">$<?= number_format($it['nilai_ekspor_usd'], 0, ',', '.') ?> USD</strong>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
