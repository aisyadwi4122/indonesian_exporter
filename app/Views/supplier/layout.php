<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Area Supplier - Indonesian Exporter') ?></title>
    <link rel="icon" href="<?= base_url('favicon.ico'); ?>" type="image/x-icon">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        :root {
            --primary-green: #16a34a;
            --primary-green-hover: #15803d;
            --primary-green-light: #dcfce7;
            --bg-page: #f8fafc;
            --border-color: #e5e7eb;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --sidebar-width: 250px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        /* Sidebar Styling (Desktop) */
        .sales-sidebar {
            width: var(--sidebar-width);
            background-color: #ffffff;
            border-right: 1px solid var(--border-color);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 1020;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            text-decoration: none;
        }

        .sidebar-brand img {
            height: 38px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
        }

        .sidebar-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            color: var(--primary-green);
            background-color: var(--primary-green-light);
            padding: 2px 8px;
            border-radius: 9999px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .sidebar-nav {
            padding: 16px 12px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 4px;
            transition: all 0.15s ease-in-out;
        }

        .nav-item-link:hover {
            color: var(--primary-green);
            background-color: #f1f5f9;
        }

        .nav-item-link.active {
            color: #ffffff;
            background-color: var(--primary-green);
            font-weight: 600;
        }

        .nav-item-link i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid var(--border-color);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 8px;
            color: #dc2626;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.15s ease;
            width: 100%;
            border: none;
            background: transparent;
        }

        .logout-btn:hover {
            background-color: #fef2f2;
            color: #b91c1c;
        }

        /* Topbar & Main Wrapper */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        .sales-topbar {
            height: 64px;
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1010;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
        }

        .topbar-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .user-avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-green-light);
            color: var(--primary-green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        /* Content Area */
        .content-area {
            padding: 28px;
            flex-grow: 1;
        }

        /* Standard Cards */
        .sales-card {
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03);
            margin-bottom: 24px;
        }

        /* Buttons */
        .btn-sales-primary {
            background-color: var(--primary-green);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 16px;
            transition: background-color 0.15s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-sales-primary:hover {
            background-color: var(--primary-green-hover);
            color: #ffffff;
        }

        .btn-sales-secondary {
            background-color: #ffffff;
            color: var(--text-dark);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.15s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-sales-secondary:hover {
            background-color: #f8fafc;
            color: var(--text-dark);
            border-color: #cbd5e1;
        }

        /* Mobile Adjustments */
        @media (max-width: 767.98px) {
            .sales-sidebar {
                display: none;
            }

            .main-wrapper {
                margin-left: 0;
            }

            .sales-topbar {
                padding: 0 16px;
            }

            .content-area {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

    <!-- Desktop Sidebar -->
    <aside class="sales-sidebar d-none d-md-flex">
        <a href="<?= base_url('supplier/dashboard'); ?>" class="sidebar-brand">
            <img src="<?= base_url('assets/img/logo.png'); ?>" alt="Indonesian Exporter"
                onerror="this.onerror=null; this.src='<?= base_url('assets/admin/images/logo.png'); ?>';">
            <span class="sidebar-badge">Supplier</span>
        </a>

        <nav class="sidebar-nav">
            <a href="<?= base_url('supplier/dashboard'); ?>"
                class="nav-item-link <?= (isset($activeMenu) && $activeMenu === 'dashboard') ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= base_url('supplier/logout'); ?>" class="logout-btn">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Mobile Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="supplierMobileSidebar" aria-labelledby="supplierMobileSidebarLabel" style="width: 270px;">
        <div class="offcanvas-header border-bottom">
            <div class="d-flex align-items-center gap-2">
                <img src="<?= base_url('assets/img/logo.png'); ?>" alt="Indonesian Exporter" style="height: 32px;"
                    onerror="this.onerror=null; this.src='<?= base_url('assets/admin/images/logo.png'); ?>';">
                <span class="sidebar-badge">Supplier</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <nav class="sidebar-nav">
                <a href="<?= base_url('supplier/dashboard'); ?>"
                    class="nav-item-link <?= (isset($activeMenu) && $activeMenu === 'dashboard') ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </nav>
            <div class="sidebar-footer mt-auto">
                <a href="<?= base_url('supplier/logout'); ?>" class="logout-btn">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="sales-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light border d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#supplierMobileSidebar" aria-controls="supplierMobileSidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="topbar-title"><?= esc($page_title ?? 'Dashboard') ?></h1>
            </div>

            <?php
            $displayUserName = session()->get('supplier_name') ?? session()->get('supplier_username') ?? 'Supplier User';
            $firstLetter = strtoupper(substr($displayUserName, 0, 1));
            ?>
            <div class="topbar-user">
                <div class="user-avatar-circle">
                    <?= esc($firstLetter) ?>
                </div>
                <div class="d-none d-sm-block text-end">
                    <span class="d-block fw-semibold" style="line-height: 1.2; font-size: 13.5px;"><?= esc($displayUserName) ?></span>
                    <small class="text-muted" style="font-size: 11px;">Supplier Area</small>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <main class="content-area">
            <?php if (!empty(session()->getFlashdata('success'))) : ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 border-0 shadow-sm mb-4" role="alert" style="background-color: #f0fdf4; color: #166534; border-left: 4px solid #16a34a !important;">
                    <i class="fa-solid fa-circle-check fs-5"></i>
                    <div><?= esc(session()->getFlashdata('success')); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty(session()->getFlashdata('error'))) : ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 border-0 shadow-sm mb-4" role="alert" style="background-color: #fef2f2; color: #991b1b; border-left: 4px solid #dc2626 !important;">
                    <i class="fa-solid fa-circle-exclamation fs-5"></i>
                    <div><?= esc(session()->getFlashdata('error')); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>
