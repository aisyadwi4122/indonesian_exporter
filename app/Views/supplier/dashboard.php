<?= $this->extend('supplier/layout') ?>

<?= $this->section('content') ?>

<style>
    .welcome-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }
    .welcome-title {
        font-size: 22px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 6px 0;
    }
    .welcome-company {
        font-size: 14px;
        color: #64748b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }
    .stat-card:hover {
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
    }
    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .stat-card-title {
        font-size: 12.5px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin: 0;
    }
    .stat-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .stat-icon-green {
        background-color: #dcfce7;
        color: #16a34a;
    }
    .stat-icon-blue {
        background-color: #e0f2fe;
        color: #0284c7;
    }
    .stat-card-value {
        font-size: 36px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.1;
        margin-bottom: 8px;
    }
    .stat-card-sub {
        font-size: 13.5px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .notice-card {
        background-color: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 16px 20px;
        margin-top: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #475569;
        font-size: 14px;
    }
</style>

<!-- Welcome Card -->
<div class="welcome-card">
    <h2 class="welcome-title">Selamat datang, <?= esc($nama) ?></h2>
    <p class="welcome-company">
        <i class="fa-solid fa-building text-success"></i>
        <span><?= esc($nama_perusahaan) ?></span>
    </p>
</div>

<!-- Dua Kartu Angka -->
<div class="row g-3">
    <!-- Kartu 1: Jumlah Produk -->
    <div class="col-12 col-md-6">
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-title">Jumlah Produk</span>
                <div class="stat-card-icon stat-icon-green">
                    <i class="fa-solid fa-box-open"></i>
                </div>
            </div>
            <div class="stat-card-value">
                <?= number_format($total_produk) ?>
            </div>
            <div class="stat-card-sub">
                <i class="fa-solid fa-tag text-success"></i>
                <span>Produk terdaftar di tb_produk_supplier</span>
            </div>
        </div>
    </div>

    <!-- Kartu 2: History Ekspor -->
    <div class="col-12 col-md-6">
        <div class="stat-card">
            <div class="stat-card-header">
                <span class="stat-card-title">History Ekspor</span>
                <div class="stat-card-icon stat-icon-blue">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="stat-card-value">
                <?= number_format($total_history) ?>
            </div>
            <div class="stat-card-sub">
                <i class="fa-solid fa-ship text-primary"></i>
                <span>Riwayat ekspor di tb_history_export_supplier</span>
            </div>
        </div>
    </div>
</div>

<!-- Catatan Fitur -->
<div class="notice-card">
    <i class="fa-solid fa-circle-info text-primary fs-5"></i>
    <div>
        <strong>Catatan:</strong> Fitur kelola produk dan history ekspor akan tersedia segera.
    </div>
</div>

<?= $this->endSection() ?>
