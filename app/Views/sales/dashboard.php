<?= $this->extend('sales/layout') ?>

<?= $this->section('content') ?>

<style>
    /* Styling Header Dashboard */
    .dashboard-header {
        margin-bottom: 24px;
    }
    .dashboard-title {
        font-size: 22px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .dashboard-subtitle {
        font-size: 13.5px;
        color: #64748b;
        margin-top: 4px;
        margin-bottom: 0;
    }

    /* Kartu Statistik Total Produk */
    .stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
    }
    .stat-card-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }
    .stat-card-title {
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
        margin: 0;
    }
    .stat-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .stat-icon-emerald {
        background-color: #dcfce7;
        color: #16a34a;
    }

    /* Nilai Statistik */
    .stat-card-value {
        font-size: 38px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.1;
        margin-bottom: 10px;
    }
    .stat-card-sub {
        font-size: 14px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .stat-card-sub a {
        color: #16a34a;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease;
    }
    .stat-card-sub a:hover {
        color: #15803d;
        text-decoration: underline;
    }

    @media (max-width: 576px) {
        .stat-card {
            padding: 18px;
        }
        .stat-card-value {
            font-size: 32px;
        }
    }
</style>

<!-- Header Dashboard -->
<div class="dashboard-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
        <h2 class="dashboard-title">Dashboard Sales</h2>
        <p class="dashboard-subtitle">Ringkasan data produk dan operasional area Sales</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-muted border px-3 py-2 fw-medium">
            <i class="fa-regular fa-clock me-1 text-success"></i> <?= (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))->format('d M Y, H:i') ?> WIB
        </span>
    </div>
</div>

<!-- ================= KARTU TOTAL PRODUK ================= -->
<div class="row g-3">
    <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
        <div class="stat-card">
            <!-- Header Kartu: Judul Kecil & Ikon -->
            <div class="stat-card-header">
                <div>
                    <span class="stat-card-title">TOTAL PRODUK</span>
                </div>
                <div class="stat-card-icon stat-icon-emerald" title="Total Produk">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>

            <!-- Angka Besar Total Produk -->
            <div class="stat-card-value">
                <?= number_format($totalProduk ?? 0) ?>
            </div>

            <!-- Teks Bawah dengan Ikon & Link ke Halaman Produk -->
            <div class="stat-card-sub">
                <i class="fa-solid fa-box text-success fs-6"></i>
                <a href="<?= base_url('sales/produk-supplier') ?>">
                    Total Produk Supplier <i class="fa-solid fa-arrow-right ms-1 small"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
