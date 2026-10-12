<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Login Sales | Indonesia Eksporter'); ?></title>

    <!-- Meta Description & Robots -->
    <meta name="description" content="Portal Login Resmi Tim Sales Indonesia Eksporter. Akses sistem manajemen ekspor, inquiry buyer global, dan katalog komoditas nusantara.">
    <meta name="robots" content="noindex, nofollow">

    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('favicon.ico'); ?>" type="image/x-icon">

    <!-- Google Fonts (Poppins) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --ie-primary-navy: #0a1928;
            --ie-secondary-navy: #162a45;
            --ie-primary-green: #15a362;
            --ie-primary-green-hover: #0f7646;
            --ie-accent-olive: #7b7b1a;
            --ie-accent-gold: #b4b450;
            --ie-bg-light: #edf9d9;
            --ie-bg-soft: #f7faf3;
            --ie-card-bg: #ffffff;
            --ie-text-dark: #0a1928;
            --ie-text-muted: #5e6d7d;
            --ie-border-color: #e2e8f0;
            --ie-radius-sm: 8px;
            --ie-radius-md: 14px;
            --ie-radius-lg: 20px;
            --ie-shadow-card: 0 20px 45px rgba(10, 25, 40, 0.08);
            --ie-shadow-btn: 0 10px 20px rgba(21, 163, 98, 0.25);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--ie-bg-soft);
            color: var(--ie-text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
        }

        /* Background Decorative Glow */
        .bg-decor {
            position: fixed;
            pointer-events: none;
            z-index: 0;
            filter: blur(80px);
            opacity: 0.6;
        }

        .bg-decor-1 {
            top: -100px;
            right: -100px;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, #edf9d9 0%, rgba(21, 163, 98, 0.18) 70%, transparent 100%);
        }

        .bg-decor-2 {
            bottom: -150px;
            left: -150px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(123, 123, 26, 0.15) 0%, rgba(237, 249, 217, 0.25) 60%, transparent 100%);
        }

        /* Top Header Navigation */
        .sales-header {
            position: relative;
            z-index: 10;
            padding: 1.25rem 2rem;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(10, 25, 40, 0.06);
        }

        .sales-header .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .sales-header .brand-logo-img {
            max-height: 44px;
            width: auto;
            object-fit: contain;
        }

        .sales-header .brand-text-block {
            line-height: 1.2;
        }

        .sales-header .brand-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--ie-primary-navy);
            letter-spacing: -0.3px;
        }

        .sales-header .brand-badge {
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--ie-primary-green);
            background-color: rgba(21, 163, 98, 0.1);
            padding: 2px 8px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 2px;
        }

        .btn-back-home {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--ie-primary-navy);
            background: #ffffff;
            border: 1px solid var(--ie-border-color);
            padding: 0.5rem 1.1rem;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
        }

        .btn-back-home:hover {
            color: var(--ie-primary-green);
            border-color: var(--ie-primary-green);
            background: rgba(21, 163, 98, 0.05);
            transform: translateX(-3px);
        }

        /* Main Wrapper */
        .sales-auth-main {
            position: relative;
            z-index: 5;
            flex: 1;
            display: flex;
            align-items: center;
            padding: 2.5rem 1rem;
        }

        .sales-container {
            max-width: 1140px;
            margin: 0 auto;
            width: 100%;
        }

        .auth-card-wrapper {
            background: var(--ie-card-bg);
            border-radius: var(--ie-radius-lg);
            box-shadow: var(--ie-shadow-card);
            border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden;
        }

        /* Left Column: Brand Showcase Banner */
        .auth-banner-col {
            background: linear-gradient(145deg, #0a1928 0%, #162a45 60%, #11382b 100%);
            color: #ffffff;
            padding: 3.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .auth-banner-col::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 85% 15%, rgba(21, 163, 98, 0.25) 0%, transparent 60%);
            pointer-events: none;
        }

        .banner-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #bbf7d0;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(8px);
        }

        .banner-title {
            font-size: 2.1rem;
            font-weight: 700;
            line-height: 1.25;
            margin-bottom: 1.2rem;
            letter-spacing: -0.5px;
        }

        .banner-title span {
            color: #4ade80;
        }

        .banner-subtitle {
            font-size: 0.95rem;
            color: #cbd5e1;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        /* Feature Pills */
        .feature-pills {
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
            margin-bottom: 2.5rem;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 12px 16px;
            border-radius: var(--ie-radius-md);
            backdrop-filter: blur(6px);
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .feature-item:hover {
            transform: translateX(4px);
            background: rgba(255, 255, 255, 0.1);
        }

        .feature-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(21, 163, 98, 0.25);
            color: #4ade80;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .feature-text h6 {
            font-size: 0.92rem;
            font-weight: 600;
            margin-bottom: 2px;
            color: #ffffff;
        }

        .feature-text p {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-bottom: 0;
            line-height: 1.4;
        }

        .banner-footer-security {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.78rem;
            color: #94a3b8;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1.25rem;
        }

        .banner-footer-security i {
            color: #4ade80;
            font-size: 1rem;
        }

        /* Right Column: Login Form */
        .auth-form-col {
            padding: 3.5rem 3.2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .form-header {
            margin-bottom: 2rem;
        }

        .form-portal-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(21, 163, 98, 0.15) 0%, rgba(123, 123, 26, 0.15) 100%);
            color: var(--ie-primary-green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 1.2rem;
            box-shadow: 0 4px 12px rgba(21, 163, 98, 0.12);
        }

        .form-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--ie-primary-navy);
            letter-spacing: -0.4px;
            margin-bottom: 0.4rem;
        }

        .form-subtitle {
            font-size: 0.9rem;
            color: var(--ie-text-muted);
            line-height: 1.5;
        }

        /* Form Controls */
        .form-group-custom {
            margin-bottom: 1.35rem;
        }

        .form-label-custom {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--ie-primary-navy);
            margin-bottom: 0.45rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-lead {
            position: absolute;
            left: 16px;
            color: #94a3b8;
            font-size: 1rem;
            z-index: 4;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .form-control-custom {
            width: 100%;
            height: 50px;
            padding: 0.65rem 1rem 0.65rem 48px;
            font-size: 0.94rem;
            font-family: inherit;
            color: var(--ie-text-dark);
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: var(--ie-radius-md);
            transition: all 0.25s ease;
        }

        .form-control-custom:focus {
            background-color: #ffffff;
            border-color: var(--ie-primary-green);
            outline: none;
            box-shadow: 0 0 0 4px rgba(21, 163, 98, 0.15);
        }

        .input-group-custom:focus-within .input-icon-lead {
            color: var(--ie-primary-green);
        }

        .password-toggle-btn {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            z-index: 4;
            transition: color 0.2s ease;
        }

        .password-toggle-btn:hover {
            color: var(--ie-primary-green);
        }

        .form-options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.8rem;
            font-size: 0.85rem;
        }

        .form-check-input:checked {
            background-color: var(--ie-primary-green);
            border-color: var(--ie-primary-green);
        }

        .form-check-label {
            color: var(--ie-text-muted);
            cursor: pointer;
            user-select: none;
        }

        .link-help {
            color: var(--ie-primary-green);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .link-help:hover {
            color: var(--ie-primary-green-hover);
            text-decoration: underline;
        }

        /* Submit Button */
        .btn-submit-sales {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, var(--ie-primary-green) 0%, #10854f 100%);
            color: #ffffff;
            border: none;
            border-radius: var(--ie-radius-md);
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: var(--ie-shadow-btn);
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .btn-submit-sales:hover {
            background: linear-gradient(135deg, #18b76e 0%, var(--ie-primary-green) 100%);
            transform: translateY(-2px);
            box-shadow: 0 14px 26px rgba(21, 163, 98, 0.35);
            color: #ffffff;
        }

        .btn-submit-sales:active {
            transform: translateY(0);
        }

        .btn-submit-sales:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Footer links */
        .auth-extra-links {
            margin-top: 2rem;
            text-align: center;
            border-top: 1px solid var(--ie-border-color);
            padding-top: 1.5rem;
            font-size: 0.85rem;
            color: var(--ie-text-muted);
        }

        .auth-extra-links a {
            color: var(--ie-secondary-navy);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .auth-extra-links a:hover {
            color: var(--ie-primary-green);
            text-decoration: underline;
        }

        /* Alerts Styling */
        .alert-custom {
            border-radius: var(--ie-radius-md);
            padding: 0.9rem 1.15rem;
            font-size: 0.88rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            animation: fadeInAlert 0.3s ease;
        }

        @keyframes fadeInAlert {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-custom .alert-icon {
            font-size: 1.15rem;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .alert-custom .alert-content {
            flex: 1;
        }

        .alert-custom .alert-close {
            background: none;
            border: none;
            font-size: 1rem;
            color: inherit;
            opacity: 0.6;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .alert-custom .alert-close:hover {
            opacity: 1;
        }

        .alert-danger-custom {
            background-color: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .alert-danger-custom .alert-icon {
            color: #dc2626;
        }

        .alert-success-custom {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .alert-success-custom .alert-icon {
            color: #16a34a;
        }

        .alert-info-custom {
            background-color: #f0f9ff;
            border-color: #bae6fd;
            color: #075985;
        }

        .alert-info-custom .alert-icon {
            color: #0284c7;
        }

        .alert-errors-list {
            margin-bottom: 0;
            padding-left: 1.2rem;
            font-size: 0.84rem;
        }

        /* Modal Customization */
        .modal-content-custom {
            border-radius: var(--ie-radius-lg);
            border: none;
            box-shadow: var(--ie-shadow-card);
            overflow: hidden;
        }

        .modal-header-custom {
            background: var(--ie-primary-navy);
            color: #ffffff;
            padding: 1.25rem 1.75rem;
            border-bottom: none;
        }

        .modal-body-custom {
            padding: 1.75rem;
            color: var(--ie-text-dark);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        /* Simple Footer */
        .sales-footer {
            text-align: center;
            padding: 1.5rem;
            font-size: 0.8rem;
            color: var(--ie-text-muted);
            position: relative;
            z-index: 5;
        }

        /* Responsive Breakpoints */
        @media (max-width: 991.98px) {
            .auth-banner-col {
                padding: 2.5rem 2rem;
            }

            .auth-form-col {
                padding: 2.5rem 2rem;
            }

            .banner-title {
                font-size: 1.7rem;
            }
        }

        @media (max-width: 767.98px) {
            .sales-header {
                padding: 1rem 1.25rem;
            }

            .sales-header .brand-title {
                font-size: 1rem;
            }

            .auth-card-wrapper {
                border-radius: var(--ie-radius-md);
            }

            .auth-banner-col {
                padding: 2rem 1.5rem;
                text-align: center;
                align-items: center;
            }

            .banner-chip {
                margin-bottom: 1rem;
            }

            .feature-pills {
                text-align: left;
                width: 100%;
            }

            .auth-form-col {
                padding: 2rem 1.5rem;
            }

            .form-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>

    <!-- Decorative Background Shapes -->
    <div class="bg-decor bg-decor-1"></div>
    <div class="bg-decor bg-decor-2"></div>

    <!-- Top Header -->
    <header class="sales-header">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <a href="<?= base_url('/'); ?>" class="navbar-brand">
                <?php if (!empty($profil['logo_perusahaan'])) : ?>
                    <img src="<?= base_url('assets/img/logo/' . esc($profil['logo_perusahaan'])); ?>"
                        alt="Logo <?= esc($profil['nama_perusahaan'] ?? 'Indonesia Eksporter'); ?>"
                        class="brand-logo-img">
                <?php else : ?>
                    <!-- Fallback logo jika profil belum memuat gambar -->
                    <img src="<?= base_url('assets/admin/images/logo.png'); ?>"
                        alt="Logo Indonesia Eksporter"
                        class="brand-logo-img"
                        onerror="this.src='https://placehold.co/180x45?text=Indonesia+Eksporter'">
                <?php endif; ?>
                <div class="brand-text-block d-none d-sm-block">
                    <div class="brand-title"><?= esc($profil['nama_perusahaan'] ?? 'Indonesia Eksporter'); ?></div>
                    <span class="brand-badge"><i class="fa-solid fa-earth-asia me-1"></i>Portal Sales & Ekspor</span>
                </div>
            </a>

            <div>
                <a href="<?= base_url('/'); ?>" class="btn-back-home" title="Kembali ke Beranda Website">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span class="d-none d-md-inline">Kembali ke Beranda</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="sales-auth-main">
        <div class="container sales-container">
            <div class="auth-card-wrapper">
                <div class="row g-0">

                    <!-- Left Column: Exporter Branding & Information Banner -->
                    <div class="col-12 col-lg-6 auth-banner-col">
                        <div>
                            <div class="banner-chip">
                                <i class="fa-solid fa-handshake"></i>
                                <span>Sales Management Portal</span>
                            </div>

                            <h1 class="banner-title">
                                Menghubungkan <span>Komoditas Unggulan</span> ke Pasar Global
                            </h1>

                            <p class="banner-subtitle">
                                Portal khusus perwakilan dan tim sales Indonesia Eksporter untuk mengelola prospek buyer mancanegara, katalog komoditas, dan penawaran ekspor resmi.
                            </p>

                            <div class="feature-pills">
                                <div class="feature-item">
                                    <div class="feature-icon-box">
                                        <i class="fa-solid fa-file-invoice-dollar"></i>
                                    </div>
                                    <div class="feature-text">
                                        <h6>Manajemen Inquiry & Penawaran</h6>
                                        <p>Pantau permintaan buyer internasional dan buat surat penawaran (Proforma Invoice) lebih cepat.</p>
                                    </div>
                                </div>

                                <div class="feature-item">
                                    <div class="feature-icon-box">
                                        <i class="fa-solid fa-boxes-packing"></i>
                                    </div>
                                    <div class="feature-text">
                                        <h6>Katalog Komoditas Real-Time</h6>
                                        <p>Akses spesifikasi produk rempah, hasil perkebunan, dan standar sertifikasi ekspor terkini.</p>
                                    </div>
                                </div>

                                <div class="feature-item">
                                    <div class="feature-icon-box">
                                        <i class="fa-solid fa-chart-line"></i>
                                    </div>
                                    <div class="feature-text">
                                        <h6>Pelacakan Target Penjualan</h6>
                                        <p>Dashboard terpadu untuk monitoring progress dealing kontrak dan performa tim pemasaran.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="banner-footer-security">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Akses Terenkripsi & Terlindungi SSL 256-Bit &bull; Khusus Anggota Tim Resmi</span>
                        </div>
                    </div>

                    <!-- Right Column: Login Form -->
                    <div class="col-12 col-lg-6 auth-form-col">
                        <div class="form-header">
                            <div class="form-portal-icon">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <h2 class="form-title">Login Sales</h2>
                            <p class="form-subtitle">
                                Masukkan username dan password Anda untuk masuk ke sistem kerja Sales Indonesia Eksporter.
                            </p>
                        </div>

                        <!-- ========================================== -->
                        <!-- AREA PESAN ALERT & VALIDASI ERROR          -->
                        <!-- ========================================== -->

                        <!-- 1. Pesan Kesalahan Login (Flashdata Error) -->
                        <?php if (!empty(session()->getFlashdata('error'))) : ?>
                            <div class="alert-custom alert-danger-custom" role="alert" id="alertError">
                                <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                                <div class="alert-content">
                                    <strong>Gagal Masuk:</strong> <?= session()->getFlashdata('error'); ?>
                                </div>
                                <button type="button" class="alert-close" onclick="this.parentElement.remove();" aria-label="Tutup">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        <?php endif; ?>

                        <!-- 2. Pesan Berhasil (Flashdata Success) -->
                        <?php if (!empty(session()->getFlashdata('success'))) : ?>
                            <div class="alert-custom alert-success-custom" role="alert" id="alertSuccess">
                                <i class="fa-solid fa-circle-check alert-icon"></i>
                                <div class="alert-content">
                                    <strong>Berhasil:</strong> <?= session()->getFlashdata('success'); ?>
                                </div>
                                <button type="button" class="alert-close" onclick="this.parentElement.remove();" aria-label="Tutup">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        <?php endif; ?>

                        <!-- 3. Pesan Informasi Status (Flashdata Info) -->
                        <?php if (!empty(session()->getFlashdata('info'))) : ?>
                            <div class="alert-custom alert-info-custom" role="alert" id="alertInfo">
                                <i class="fa-solid fa-circle-info alert-icon"></i>
                                <div class="alert-content">
                                    <?= session()->getFlashdata('info'); ?>
                                </div>
                                <button type="button" class="alert-close" onclick="this.parentElement.remove();" aria-label="Tutup">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        <?php endif; ?>

                        <!-- 4. Daftar Pesan Validasi Form (Errors List) -->
                        <?php $validationErrors = session()->getFlashdata('errors') ?? ($errors ?? null); ?>
                        <?php if (!empty($validationErrors) && is_array($validationErrors)) : ?>
                            <div class="alert-custom alert-danger-custom" role="alert" id="alertValidation">
                                <i class="fa-solid fa-triangle-exclamation alert-icon"></i>
                                <div class="alert-content">
                                    <strong>Periksa kembali isian form:</strong>
                                    <ul class="alert-errors-list mt-1">
                                        <?php foreach ($validationErrors as $err) : ?>
                                            <li><?= esc($err); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <button type="button" class="alert-close" onclick="this.parentElement.remove();" aria-label="Tutup">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        <?php endif; ?>

                        <!-- ========================================== -->
                        <!-- FORM LOGIN SALES (METHOD POST)             -->
                        <!-- ========================================== -->
                        <form id="salesLoginForm" action="<?= esc($action_url ?? base_url('sales/login')); ?>" method="post" autocomplete="on">
                            <?= csrf_field(); ?>

                            <!-- Input Username -->
                            <div class="form-group-custom">
                                <label for="username" class="form-label-custom">
                                    <span>Username / ID Sales <span class="text-danger">*</span></span>
                                </label>
                                <div class="input-group-custom">
                                    <i class="fa-solid fa-user input-icon-lead"></i>
                                    <input type="text"
                                        id="username"
                                        name="username"
                                        class="form-control-custom"
                                        placeholder="Masukkan username atau ID sales"
                                        value="<?= esc(old('username')); ?>"
                                        required
                                        autofocus
                                        autocomplete="username">
                                </div>
                            </div>

                            <!-- Input Password -->
                            <div class="form-group-custom">
                                <label for="password" class="form-label-custom">
                                    <span>Password <span class="text-danger">*</span></span>
                                </label>
                                <div class="input-group-custom">
                                    <i class="fa-solid fa-lock input-icon-lead"></i>
                                    <input type="password"
                                        id="password"
                                        name="password"
                                        class="form-control-custom"
                                        placeholder="Masukkan password akun sales"
                                        required
                                        autocomplete="current-password"
                                        style="padding-right: 46px;">
                                    <button type="button"
                                        id="togglePassword"
                                        class="password-toggle-btn"
                                        aria-label="Tampilkan atau sembunyikan password"
                                        title="Tampilkan Password">
                                        <i class="fa-regular fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Opsi Ingat Saya & Lupa Password -->
                            <div class="form-options-row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                                    <label class="form-check-label" for="rememberMe">
                                        Ingat Saya
                                    </label>
                                </div>
                                <div>
                                    <a href="#" class="link-help" data-bs-toggle="modal" data-bs-target="#helpModal">
                                        <i class="fa-regular fa-circle-question me-1"></i>Bantuan Akses?
                                    </a>
                                </div>
                            </div>

                            <!-- Tombol Submit Login -->
                            <button type="submit" id="submitBtn" class="btn-submit-sales">
                                <span id="submitText">Masuk ke Portal Sales</span>
                                <i class="fa-solid fa-arrow-right" id="submitIcon"></i>
                                <span id="submitSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </form>

                        <!-- Navigasi Tambahan / Akses Admin -->
                        <div class="auth-extra-links">
                            <span>Bukan tim sales? </span>
                            <a href="<?= base_url('login'); ?>">
                                <i class="fa-solid fa-user-shield me-1"></i>Masuk sebagai Administrator Web
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Bantuan Akses Sales -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title fs-6 fw-bold" id="helpModalLabel">
                        <i class="fa-solid fa-headset me-2 text-success"></i>Bantuan Akses Portal Sales
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <p class="mb-3">
                        Kredensial akun Sales (Username dan Password) dikelola dan diterbitkan secara resmi oleh Divisi IT & Manajemen Ekspor <strong><?= esc($profil['nama_perusahaan'] ?? 'Indonesia Eksporter'); ?></strong>.
                    </p>
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <h6 class="fw-semibold mb-2 text-dark"><i class="fa-solid fa-circle-info text-primary me-2"></i>Jika Anda mengalami kendala:</h6>
                        <ul class="mb-0 ps-3 small text-secondary">
                            <li>Lupa password atau username akun.</li>
                            <li>Akun baru belum diaktivasi oleh administrator.</li>
                            <li>Terkunci setelah beberapa kali percobaan gagal.</li>
                        </ul>
                    </div>
                    <p class="small text-muted mb-0">
                        Silakan hubungi Super Administrator atau kirimkan konfirmasi melalui email resmi kantor untuk permohonan reset kredensial.
                    </p>
                </div>
                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-secondary btn-sm px-3 rounded-pill" data-bs-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Footer -->
    <footer class="sales-footer">
        <div class="container">
            <p class="mb-0">
                &copy; <?= date('Y'); ?> <strong><?= esc($profil['nama_perusahaan'] ?? 'Indonesia Eksporter'); ?></strong>. All rights reserved.
            </p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS (Includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Interactive Client Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Password Visibility Toggle
            const togglePasswordBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (togglePasswordBtn && passwordInput && toggleIcon) {
                togglePasswordBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                    if (isPassword) {
                        toggleIcon.classList.remove('fa-eye');
                        toggleIcon.classList.add('fa-eye-slash');
                        togglePasswordBtn.setAttribute('title', 'Sembunyikan Password');
                    } else {
                        toggleIcon.classList.remove('fa-eye-slash');
                        toggleIcon.classList.add('fa-eye');
                        togglePasswordBtn.setAttribute('title', 'Tampilkan Password');
                    }
                });
            }

            // 2. Form Submit Animation & State
            const loginForm = document.getElementById('salesLoginForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitIcon = document.getElementById('submitIcon');
            const submitSpinner = document.getElementById('submitSpinner');

            if (loginForm && submitBtn) {
                loginForm.addEventListener('submit', function () {
                    // Cek validitas form sebelum menonaktifkan tombol
                    if (loginForm.checkValidity()) {
                        submitBtn.disabled = true;
                        if (submitText) submitText.textContent = 'Memproses...';
                        if (submitIcon) submitIcon.classList.add('d-none');
                        if (submitSpinner) submitSpinner.classList.remove('d-none');
                    }
                });
            }
        });
    </script>
</body>

</html>
