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

    /* Kartu Statistik Total Kunjungan */
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
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .stat-icon-blue {
        background-color: #e0f2fe;
        color: #0284c7;
    }

    /* Form Pencarian Periode */
    .period-search-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 20px;
    }
    .period-search-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .mode-select-wrap {
        flex: 0 0 auto;
        min-width: 95px;
    }
    .mode-select {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 6px 28px 6px 10px;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .mode-select:focus {
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
        outline: none;
    }
    .period-input-container {
        flex: 1 1 150px;
        min-width: 135px;
    }
    .input-mode-wrapper {
        width: 100%;
    }
    .period-input {
        font-size: 13px;
        font-weight: 500;
        color: #1e293b;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 6px 10px;
        width: 100%;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .period-input:focus {
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
        outline: none;
    }
    .search-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex: 0 0 auto;
    }
    .btn-cari {
        background-color: #16a34a;
        border: 1px solid #16a34a;
        color: #ffffff;
        font-size: 13px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }
    .btn-cari:hover {
        background-color: #15803d;
        border-color: #15803d;
        color: #ffffff;
    }
    .btn-reset {
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 6px 12px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s ease;
    }
    .btn-reset:hover {
        color: #1e293b;
        background-color: #f1f5f9;
        border-color: #94a3b8;
    }

    /* Pesan Error / Validasi Kecil */
    .period-invalid-msg {
        font-size: 12.5px;
        font-weight: 500;
        color: #b91c1c;
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 7px 12px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 6px;
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

    @media (max-width: 576px) {
        .stat-card {
            padding: 18px;
        }
        .period-search-bar {
            gap: 10px;
        }
        .mode-select-wrap {
            flex: 1 1 100px;
        }
        .period-input-container {
            flex: 2 1 150px;
        }
        .search-actions {
            width: 100%;
            display: flex;
        }
        .btn-cari, .btn-reset {
            flex: 1 1 0;
            justify-content: center;
        }
        .stat-card-value {
            font-size: 32px;
        }
    }
</style>

<!-- Header Dashboard -->
<div class="dashboard-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
        <h2 class="dashboard-title">Dashboard Statistik Pengunjung</h2>
        <p class="dashboard-subtitle">Statistik lalu lintas pengunjung publik website Indonesian Exporter (WIB / Asia/Jakarta)</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-muted border px-3 py-2 fw-medium">
            <i class="fa-regular fa-clock me-1 text-success"></i> <?= (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))->format('d M Y, H:i') ?> WIB
        </span>
    </div>
</div>

<!-- ================= KARTU TOTAL KUNJUNGAN DENGAN FORM PENCARIAN PERIODE ================= -->
<div class="row g-3">
    <div class="col-12 col-md-10 col-lg-8 col-xl-7">
        <div class="stat-card">
            <!-- Header Kartu: Judul Kecil (dinamis) & Ikon -->
            <div class="stat-card-header">
                <div>
                    <span class="stat-card-title"><?= esc($cardTitle) ?></span>
                </div>
                <div class="stat-card-icon stat-icon-blue" title="Statistik Kunjungan">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>

            <!-- Form Pencarian Periode (GET ke /sales/dashboard) -->
            <form action="<?= base_url('sales/dashboard') ?>" method="GET" class="period-search-box" id="periodSearchForm">
                <div class="period-search-bar">
                    <!-- 1. Pilihan Mode: Hari, Bulan, Tahun -->
                    <div class="mode-select-wrap">
                        <select name="mode" id="modeSelect" class="form-select form-select-sm mode-select" aria-label="Pilih Mode Periode">
                            <option value="hari" <?= ($activeMode === 'hari') ? 'selected' : '' ?>>Hari</option>
                            <option value="bulan" <?= ($activeMode === 'bulan') ? 'selected' : '' ?>>Bulan</option>
                            <option value="tahun" <?= ($activeMode === 'tahun') ? 'selected' : '' ?>>Tahun</option>
                        </select>
                    </div>

                    <!-- 2. Input Sesuai Mode -->
                    <div class="period-input-container">
                        <!-- Mode Hari: date input -->
                        <div id="wrapper-hari" class="input-mode-wrapper">
                            <input type="date" name="tanggal" id="inputTanggal" class="form-control form-control-sm period-input"
                                   value="<?= esc($activeTanggal) ?>" max="<?= esc($todayStr) ?>" min="2020-01-01" aria-label="Pilih Tanggal">
                        </div>

                        <!-- Mode Bulan: month input -->
                        <div id="wrapper-bulan" class="input-mode-wrapper">
                            <input type="month" name="bulan" id="inputBulan" class="form-control form-control-sm period-input"
                                   value="<?= esc($activeBulan) ?>" max="<?= esc($currentYearMonth) ?>" min="2020-01" aria-label="Pilih Bulan">
                        </div>

                        <!-- Mode Tahun: number input 4 digit -->
                        <div id="wrapper-tahun" class="input-mode-wrapper">
                            <input type="number" name="tahun" id="inputTahun" class="form-control form-control-sm period-input"
                                   value="<?= esc($activeTahun) ?>" min="2020" max="<?= esc($currentYear) ?>" placeholder="Tahun (min 2020)" aria-label="Pilih Tahun">
                        </div>
                    </div>

                    <!-- 3. Tombol Cari dan Reset -->
                    <div class="search-actions">
                        <button type="submit" class="btn btn-sm btn-cari" id="btnCari">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Cari
                        </button>
                        <a href="<?= base_url('sales/dashboard') ?>" class="btn btn-sm btn-reset" id="btnReset" title="Kembali ke hari ini">
                            <i class="fa-solid fa-rotate-left me-1"></i> Reset
                        </a>
                    </div>
                </div>

                <noscript>
                    <div class="text-muted small mt-2" style="font-size: 11px;">
                        <i class="fa-solid fa-info-circle me-1"></i> JavaScript nonaktif: semua input ditampilkan. Server akan memproses input sesuai mode yang dipilih.
                    </div>
                </noscript>
            </form>

            <!-- Pesan Peringatan Input Tidak Valid -->
            <?php if (!empty($hasError) && !empty($errorMessage)): ?>
                <div class="period-invalid-msg">
                    <i class="fa-solid fa-circle-exclamation text-danger"></i>
                    <span><?= esc($errorMessage) ?></span>
                </div>
            <?php endif; ?>

            <!-- Angka Besar Total Kunjungan (0 jika tidak ada data) -->
            <div class="stat-card-value">
                <?= number_format($totalVisits ?? 0) ?>
            </div>

            <!-- Teks Bawah dengan Ikon Mata Tetap -->
            <div class="stat-card-sub">
                <i class="fa-regular fa-eye text-primary fs-6"></i>
                <span class="fw-medium">Total Kunjungan</span>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Sederhana Tanpa Library untuk Mengontrol Tampilan Input Berdasarkan Mode -->
<script>
    (function() {
        var modeSelect = document.getElementById('modeSelect');
        if (!modeSelect) return;

        var wrapHari = document.getElementById('wrapper-hari');
        var wrapBulan = document.getElementById('wrapper-bulan');
        var wrapTahun = document.getElementById('wrapper-tahun');

        var inputHari = document.getElementById('inputTanggal');
        var inputBulan = document.getElementById('inputBulan');
        var inputTahun = document.getElementById('inputTahun');

        function syncInputs() {
            var mode = modeSelect.value;

            // Tampilkan hanya input yang sesuai mode
            if (wrapHari) wrapHari.style.display = (mode === 'hari') ? '' : 'none';
            if (wrapBulan) wrapBulan.style.display = (mode === 'bulan') ? '' : 'none';
            if (wrapTahun) wrapTahun.style.display = (mode === 'tahun') ? '' : 'none';

            // Nonaktifkan input yang tersembunyi agar parameter URL GET bersih
            if (inputHari) inputHari.disabled = (mode !== 'hari');
            if (inputBulan) inputBulan.disabled = (mode !== 'bulan');
            if (inputTahun) inputTahun.disabled = (mode !== 'tahun');
        }

        modeSelect.addEventListener('change', syncInputs);
        syncInputs();
    })();
</script>

<?= $this->endSection() ?>
