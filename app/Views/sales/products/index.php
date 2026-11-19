<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Katalog Produk Sales'); ?></title>
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
        .card-product {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background: #ffffff;
            overflow: hidden;
        }
        .card-product:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
        }
        .product-img-wrapper {
            height: 220px;
            background-color: #f1f5f9;
            position: relative;
            overflow: hidden;
        }
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .btn-detail {
            background-color: #0d9488;
            border-color: #0d9488;
            color: #ffffff;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-detail:hover {
            background-color: #0f766e;
            border-color: #0f766e;
            color: #ffffff;
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
                <span class="text-muted small d-none d-sm-inline">
                    <i class="fa-regular fa-user me-1"></i>
                    <?= esc(session()->get('name') ?? session()->get('username') ?? 'Sales User'); ?>
                </span>
                <a href="<?= base_url('sales/logout'); ?>" class="btn btn-outline-danger btn-sm" style="border-radius: 6px;">
                    <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container py-4">

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <?= esc(session()->getFlashdata('error')); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?= esc(session()->getFlashdata('success')); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold text-slate-800 mb-1" style="color: #0f172a;">Katalog Produk Siap Ekspor</h1>
                <p class="text-muted mb-0">Daftar produk yang telah dipublikasikan dan siap ditawarkan ke calon pembeli luar negeri.</p>
            </div>
            <div class="text-muted small">
                Total: <span class="fw-semibold text-dark"><?= count($products ?? []); ?></span> Produk Published
            </div>
        </div>

        <!-- Products Grid (3 cols desktop, 2 cols tablet, 1 col mobile) -->
        <?php if (!empty($products)) : ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($products as $p) : ?>
                    <?php
                        $imgPath = !empty($p['gambar_utama']) 
                            ? (filter_var($p['gambar_utama'], FILTER_VALIDATE_URL) ? $p['gambar_utama'] : base_url($p['gambar_utama']))
                            : 'https://placehold.co/600x400?text=' . urlencode($p['nama_produk'] ?? 'Produk');
                    ?>
                    <div class="col">
                        <div class="card card-product h-100">
                            <!-- Gambar Utama -->
                            <div class="product-img-wrapper">
                                <img src="<?= esc($imgPath); ?>" 
                                     alt="<?= esc($p['nama_produk'] ?? 'Produk'); ?>" 
                                     class="product-img"
                                     loading="lazy"
                                     onerror="this.onerror=null; this.src='https://placehold.co/600x400?text=No+Image';">
                            </div>

                            <!-- Konten Card -->
                            <div class="card-body d-flex flex-column p-4">
                                <!-- Kategori -->
                                <div class="mb-2">
                                    <span class="badge bg-light text-primary border border-primary-subtle fw-medium px-2 py-1" style="font-size: 12px;">
                                        <i class="fa-solid fa-tag me-1"></i><?= esc($p['kategori'] ?? 'Umum'); ?>
                                    </span>
                                </div>

                                <!-- Nama Produk -->
                                <h5 class="card-title fw-bold text-dark mb-2" style="font-size: 18px; line-height: 1.35;">
                                    <?= esc($p['nama_produk'] ?? ''); ?>
                                </h5>

                                <!-- Cuplikan Singkat Perusahaan / Domisili -->
                                <p class="card-text text-muted small mb-3 flex-grow-1" style="line-height: 1.5;">
                                    <i class="fa-solid fa-location-dot me-1 text-danger"></i> <?= esc(\App\Libraries\LocationHelper::formatDisplayLocation($p['domisili_usaha'] ?? '-')); ?>
                                    <span class="mx-1">•</span>
                                    <i class="fa-solid fa-building me-1 text-secondary"></i> <?= esc($p['jenis_perusahaan'] ?? '-'); ?>
                                </p>

                                <!-- Tombol Aksi (Hanya Lihat Detail) -->
                                <div class="pt-2 border-top">
                                    <a href="<?= base_url('sales/products/detail/' . $p['id']); ?>" 
                                       class="btn btn-detail w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i class="fa-regular fa-eye"></i>
                                        <span>Lihat Detail</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <!-- Empty State -->
            <div class="card border-0 shadow-sm p-5 text-center my-4" style="border-radius: 12px;">
                <div class="mb-3">
                    <i class="fa-solid fa-box-open text-muted" style="font-size: 54px;"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Belum Ada Produk Published</h4>
                <p class="text-muted mb-0">Saat ini belum ada produk dengan status "published" di katalog sales.</p>
            </div>
        <?php endif; ?>

    </div>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
