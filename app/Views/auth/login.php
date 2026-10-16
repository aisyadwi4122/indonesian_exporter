<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Log in to Indonesian Exporter'); ?></title>
    <link rel="icon" href="<?= base_url('favicon.ico'); ?>" type="image/x-icon">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        :root {
            /* Gambar login: Mengambil foto slider dari website resmi Indonesian Exporter */
            <?php
            $actualBgImage = (!empty($bg_image) && file_exists(FCPATH . $bg_image)) ? $bg_image : 'assets/img/slider/slide1.png';
            ?>
            --login-bg-image: url('<?= base_url($actualBgImage); ?>');
            --primary-green: #16a34a;
            --primary-green-hover: #15803d;
            --border-light: #dee2e6;
            --text-dark: #222222;
            --text-placeholder: #9ca3af;
            --bg-fallback: #e5e7eb;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body,
        html {
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #ffffff;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* Container Split Screen (Desktop) */
        .login-split-container {
            display: flex;
            width: 100vw;
            height: 100vh;
            min-height: 100vh;
        }

        /* Kolom Kiri: Form Login (± 47%) */
        .login-col-left {
            flex: 0 0 47%;
            width: 47%;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 8vh 24px 24px;
            overflow-y: auto;
        }

        /* Box Form (lebar maks 300px, rata tengah) */
        .login-form-box {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }

        /* 1. Logo (tinggi ± 45px, rata tengah) */
        .login-logo {
            display: block;
            height: 45px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
            margin: 0 auto 20px;
        }

        /* 2. Judul "Log in to Indonesian Exporter" */
        .login-title {
            font-size: 20px;
            font-weight: 500;
            color: var(--text-dark);
            text-align: center;
            margin-bottom: 24px;
            line-height: 1.3;
        }

        /* Pesan Error Flashdata */
        .login-error-alert {
            color: #dc2626;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 14px;
            text-align: left;
            line-height: 1.4;
            padding: 8px 12px;
            background-color: #fef2f2;
            border-radius: 4px;
            border: 1px solid #fecaca;
        }

        /* Pesan Sukses / Info */
        .login-info-alert {
            color: #15803d;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 14px;
            text-align: left;
            line-height: 1.4;
            padding: 8px 12px;
            background-color: #f0fdf4;
            border-radius: 4px;
            border: 1px solid #bbf7d0;
        }

        /* Form Group & Input */
        .login-input-group {
            margin-bottom: 12px;
        }

        .login-input {
            width: 100%;
            height: 34px;
            padding: 6px 10px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--text-dark);
            background-color: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 4px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .login-input::placeholder {
            color: var(--text-placeholder);
            opacity: 1;
        }

        .login-input:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 0 1px var(--primary-green);
        }

        /* Tombol "Log In" lebar penuh, warna hijau (#16a34a) */
        .login-btn-submit {
            width: 100%;
            height: 36px;
            background-color: var(--primary-green);
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 600;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 6px;
            transition: background-color 0.15s ease-in-out;
        }

        .login-btn-submit:hover {
            background-color: var(--primary-green-hover);
            color: #ffffff;
        }

        .login-btn-submit:active {
            background-color: #166534;
        }

        /* Kolom Kanan: Gambar Full-Height Website Indonesian Exporter (± 53%) */
        .login-col-right {
            flex: 0 0 53%;
            width: 53%;
            height: 100%;
            background-color: var(--bg-fallback);
            background-image: var(--login-bg-image);
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            position: relative;
            display: flex;
            align-items: flex-end;
        }

        /* Overlay gelap halus di bagian bawah gambar */
        .login-right-overlay {
            width: 100%;
            padding: 40px 32px 32px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.75) 0%, rgba(0, 0, 0, 0.4) 60%, transparent 100%);
            color: #ffffff;
        }

        .login-badge-tag {
            display: inline-block;
            background: rgba(22, 163, 74, 0.9);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 3px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .login-caption-title {
            font-size: 20px;
            font-weight: 700;
            line-height: 1.35;
            margin-bottom: 6px;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        .login-caption-subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 0;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        /* Responsive Mobile & Tablet (< 768px) */
        @media (max-width: 767.98px) {
            .login-col-right {
                display: none !important;
            }

            .login-col-left {
                flex: 0 0 100%;
                width: 100%;
                height: 100vh;
                justify-content: center;
                padding: 24px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="login-split-container">

        <!-- Kolom Kiri: Form Login (± 47%) -->
        <div class="login-col-left">
            <div class="login-form-box">

                <!-- 1. Logo (tinggi ± 45px, rata tengah) -->
                <a href="<?= base_url('/'); ?>" title="Indonesian Exporter">
                    <img src="<?= base_url('assets/img/logo.png'); ?>"
                        alt="Logo Indonesian Exporter"
                        class="login-logo"
                        onerror="this.onerror=null; this.src='<?= base_url('assets/admin/images/logo.png'); ?>';">
                </a>

                <!-- 2. Judul "Log in to Indonesian Exporter" -->
                <h1 class="login-title">Log in to Indonesian Exporter</h1>

                <!-- Pesan Error Flashdata -->
                <?php if (!empty(session()->getFlashdata('error'))) : ?>
                    <div class="login-error-alert" role="alert">
                        <?= esc(session()->getFlashdata('error')); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty(session()->getFlashdata('success'))) : ?>
                    <div class="login-info-alert" role="alert">
                        <?= esc(session()->getFlashdata('success')); ?>
                    </div>
                <?php endif; ?>

                <!-- Form Login -->
                <form action="<?= esc($action_url ?? base_url('sales')); ?>" method="post">
                    <?= csrf_field(); ?>

                    <!-- 3. Input Username -->
                    <div class="login-input-group">
                        <input type="text"
                            name="username"
                            id="username"
                            class="login-input"
                            placeholder="username"
                            value="<?= esc(old('username')); ?>"
                            required
                            autofocus
                            autocomplete="username">
                    </div>

                    <!-- 4. Input Password -->
                    <div class="login-input-group">
                        <input type="password"
                            name="password"
                            id="password"
                            class="login-input"
                            placeholder="password"
                            required
                            autocomplete="current-password">
                    </div>

                    <!-- 5. Tombol "Log In" lebar penuh, warna hijau (#16a34a) -->
                    <button type="submit" class="login-btn-submit">
                        Log In
                    </button>
                </form>

            </div>
        </div>

        <!-- Kolom Kanan: Gambar Full-Height Website Indonesian Exporter (± 53%) -->
        <div class="login-col-right" aria-hidden="true">
            <div class="login-right-overlay">
                <span class="login-badge-tag"><?= esc($badge_tag ?? 'Indonesian Exporter'); ?></span>
                <h2 class="login-caption-title"><?= esc($caption_title ?? 'Membawa Produk Terbaik Indonesia ke Dunia'); ?></h2>
                <p class="login-caption-subtitle"><?= esc($caption_subtitle ?? 'Portal Manajemen Sales & Katalog Komoditas Ekspor Nusantara'); ?></p>
            </div>
        </div>

    </div>
</body>

</html>
