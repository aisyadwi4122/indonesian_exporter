<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Detail Produk'); ?></title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            color: #334155;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
        }
        .navbar-sales {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .section-header {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            padding: 12px 20px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-row {
            padding: 10px 20px;
            display: flex;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            width: 240px;
            font-weight: 600;
            color: #64748b;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        .info-value {
            flex: 1;
            color: #1e293b;
            font-size: 14px;
        }
        .product-hero-img {
            max-height: 380px;
            width: 100%;
            object-fit: cover;
            border-radius: 10px;
        }
        @media (max-width: 768px) {
            .info-row {
                flex-direction: column;
                gap: 4px;
            }
            .info-label {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-sales sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-dark d-flex align-items-center gap-2" href="<?= base_url('sales/products'); ?>">
                <i class="fa-solid fa-briefcase text-success"></i>
                <span>Sales Area</span>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1" style="font-size: 11px;">READ ONLY</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="<?= base_url('sales/products'); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">

                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb" style="font-size: 13.5px;">
                        <li class="breadcrumb-item"><a href="<?= base_url('sales/products'); ?>" class="text-decoration-none text-muted">Katalog Produk</a></li>
                        <li class="breadcrumb-item active text-dark fw-medium" aria-current="page"><?= esc($product['nama_produk'] ?? ''); ?></li>
                    </ol>
                </nav>

                <?php
                    // Helper decode JSON string safely
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

                    $imgPath = !empty($product['gambar_utama']) 
                        ? (filter_var($product['gambar_utama'], FILTER_VALIDATE_URL) ? $product['gambar_utama'] : base_url($product['gambar_utama']))
                        : 'https://placehold.co/800x500?text=' . urlencode($product['nama_produk'] ?? 'Produk');
                ?>

                <!-- Detail Card -->
                <div class="detail-card">
                    
                    <!-- Header Section: Gambar Produk & Info Dasar -->
                    <div class="p-4 bg-white">
                        <div class="row g-4 align-items-center">
                            <div class="col-12 col-md-5">
                                <div class="bg-light rounded p-2 text-center" style="overflow: hidden;">
                                    <img src="<?= esc($imgPath); ?>" 
                                         alt="<?= esc($product['nama_produk'] ?? ''); ?>" 
                                         class="product-hero-img img-fluid"
                                         onerror="this.onerror=null; this.src='https://placehold.co/800x500?text=No+Image';">
                                </div>
                            </div>
                            <div class="col-12 col-md-7">
                                <div class="mb-2">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1" style="font-size: 13px;">
                                        <i class="fa-solid fa-tag me-1"></i> Kategori: <?= esc($product['kategori'] ?? 'Umum'); ?>
                                    </span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 ms-1" style="font-size: 13px;">
                                        <i class="fa-solid fa-circle-check me-1"></i> Status: Published
                                    </span>
                                </div>
                                <h1 class="h3 fw-bold text-dark mb-3"><?= esc($product['nama_produk'] ?? ''); ?></h1>
                                <?php if (!empty($product['deskripsi'])) : ?>
                                    <p class="text-muted mb-0" style="font-size: 14.5px; line-height: 1.6;">
                                        <?= nl2br(esc($product['deskripsi'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 🏢 INFORMASI PERUSAHAAN -->
                    <div class="section-header">
                        <i class="fa-solid fa-building text-primary"></i>
                        <span>INFORMASI PERUSAHAAN</span>
                    </div>
                    <div>
                        <div class="info-row">
                            <div class="info-label">• Domisili Usaha</div>
                            <div class="info-value">: <?= esc($product['domisili_usaha'] ?? '-'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">• Jenis Perusahaan</div>
                            <div class="info-value">: <?= esc($product['jenis_perusahaan'] ?? '-'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">• Jumlah Pegawai</div>
                            <div class="info-value">: <?= esc($product['jumlah_pegawai'] ?? '-'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">• Kapasitas Produksi</div>
                            <div class="info-value">: <?= esc($product['kapasitas_produksi'] ?? '-'); ?></div>
                        </div>
                    </div>

                    <!-- 🛒 INFORMASI PRODUK -->
                    <div class="section-header">
                        <i class="fa-solid fa-cart-shopping text-success"></i>
                        <span>INFORMASI PRODUK</span>
                    </div>
                    <div>
                        <div class="info-row">
                            <div class="info-label">• Produk yang Dipasarkan</div>
                            <div class="info-value">: <?= esc($dipasarkanText); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">• Kapasitas Produksi</div>
                            <div class="info-value">: <?= esc($product['kapasitas_produksi'] ?? '-'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">• Produk Siap Ekspor</div>
                            <div class="info-value">: <?= esc($siapEksporText); ?></div>
                        </div>
                    </div>

                    <!-- 🌏 INFORMASI EKSPOR -->
                    <div class="section-header">
                        <i class="fa-solid fa-globe text-info"></i>
                        <span>INFORMASI EKSPOR</span>
                    </div>
                    <div>
                        <div class="info-row">
                            <div class="info-label">• Frekuensi Ekspor</div>
                            <div class="info-value">: <?= esc($product['frekuensi_ekspor'] ?? '-'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">• Produk Siap Ekspor</div>
                            <div class="info-value">: <?= esc($siapEksporText); ?></div>
                        </div>
                    </div>

                    <!-- Action Footer: [📄 Download PDF] [← Kembali] -->
                    <div class="p-3 bg-light border-top d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <a href="<?= base_url('sales/products'); ?>" class="btn btn-secondary px-4">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                        </a>

                        <a href="<?= base_url('sales/products/pdf/' . $product['id']); ?>" 
                           class="btn btn-danger px-4 d-flex align-items-center gap-2"
                           target="_blank">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span>Download PDF</span>
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
