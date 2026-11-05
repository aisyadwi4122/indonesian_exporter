<?= $this->extend('admin/template/template'); ?>
<?= $this->Section('content'); ?>

<?php
if (!function_exists('formatTglIndo')) {
    function formatTglIndo(?string $datetime): string
    {
        if (empty($datetime)) return '-';
        $timestamp = strtotime($datetime);
        if (!$timestamp) return '-';
        $bulanIndo = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];
        $d = date('d', $timestamp);
        $m = (int)date('m', $timestamp);
        $y = date('Y', $timestamp);
        $hi = date('H:i', $timestamp);
        return "{$d} {$bulanIndo[$m]} {$y}, {$hi}";
    }
}
?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <!-- Judul Halaman Utama -->
        <h1 class="app-page-title mb-4">Dashboard</h1>

        <!-- 3 KARTU STATISTIK LAMA (DIPERTAHANKAN) -->
        <div class="row g-4 mb-4">
            <div class="col-6 col-lg-4">
                <div class="app-card app-card-stat shadow-sm h-100 border-0" style="border-radius: 12px;">
                    <div class="app-card-body p-3 p-lg-4">
                        <h4 class="stats-type mb-1 text-muted" style="font-size: 0.9rem; font-weight: 600;">Jumlah Slider</h4>
                        <div class="stats-figure fw-bold" style="font-size: 1.85rem; color: #1e293b;"><?= (int) $sliderCount; ?></div>
                    </div>
                    <a class="app-card-link-mask" href="<?= base_url('/admin/slider/index') ?>"></a>
                </div>
            </div>

            <div class="col-6 col-lg-4">
                <div class="app-card app-card-stat shadow-sm h-100 border-0" style="border-radius: 12px;">
                    <div class="app-card-body p-3 p-lg-4">
                        <h4 class="stats-type mb-1 text-muted" style="font-size: 0.9rem; font-weight: 600;">Jumlah Produk</h4>
                        <div class="stats-figure fw-bold" style="font-size: 1.85rem; color: #1e293b;"><?= (int) $productCount; ?></div>
                    </div>
                    <a class="app-card-link-mask" href="<?= base_url('/admin/produk/index') ?>"></a>
                </div>
            </div>

            <div class="col-6 col-lg-4">
                <div class="app-card app-card-stat shadow-sm h-100 border-0" style="border-radius: 12px;">
                    <div class="app-card-body p-3 p-lg-4">
                        <h4 class="stats-type mb-1 text-muted" style="font-size: 0.9rem; font-weight: 600;">Jumlah Artikel</h4>
                        <div class="stats-figure fw-bold" style="font-size: 1.85rem; color: #1e293b;"><?= (int) $artikelCount; ?></div>
                    </div>
                    <a class="app-card-link-mask" href="<?= base_url('/admin/artikel/index') ?>"></a>
                </div>
            </div>
        </div>

        <hr class="my-4 text-muted" style="opacity: 0.15;">

        <!-- BAGIAN BARU: AKTIVITAS PENGGUNA WEBSITE INDONESIA EKSPORTER -->
        <!-- A. Baris Header Bagian Baru & Filter Tanggal -->
        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-12 col-md-auto">
                <h2 class="h5 mb-1 fw-bold text-dark" style="font-size: 1.25rem;">
                    Ringkasan aktivitas penggunaan website Indonesia Eksporter
                </h2>
                <span class="text-muted small">
                    Aktivitas user Sales dalam periode: <strong class="text-dark"><?= date('d M Y', strtotime($startDate)) ?> – <?= date('d M Y', strtotime($endDate)) ?></strong>
                </span>
            </div>
            <div class="col-12 col-md-auto">
                <form method="GET" action="<?= base_url('admin/dashboard') ?>" class="d-flex flex-wrap align-items-center gap-2">
                    <div class="input-group input-group-sm shadow-sm" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary">
                            <i class="fa-regular fa-calendar-days"></i>
                        </span>
                        <input type="date" name="start_date" class="form-control form-control-sm border-start-0 pe-1" 
                               value="<?= esc($startDate) ?>" style="font-size: 12.5px;" title="Tanggal Mulai">
                        <span class="input-group-text bg-light text-muted border-start-0 border-end-0 px-2">–</span>
                        <input type="date" name="end_date" class="form-control form-control-sm border-start-0" 
                               value="<?= esc($endDate) ?>" style="font-size: 12.5px;" title="Tanggal Akhir">
                        <button type="submit" class="btn btn-primary btn-sm px-3" style="font-size: 12.5px;">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- B. Baris 5 Kartu Statistik Aktivitas -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
            <!-- 1. Total Pencarian (Biru) -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm" style="background-color: #f4f8fe; border-radius: 12px; border-left: 4px solid #0d6efd !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px; background-color: #e0edff; color: #0d6efd;">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <?php $trend = $statCards['search']['trend']; ?>
                            <?php if ($trend['direction'] === 'up'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small"><?= $trend['label'] ?></span>
                            <?php elseif ($trend['direction'] === 'down'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small"><?= $trend['label'] ?></span>
                            <?php else: ?>
                                <span class="badge border px-2 py-1 small" style="color: #64748b !important; background-color: #f8fafc;"><?= $trend['label'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="fs-4 fw-bold text-dark mb-1"><?= number_format($statCards['search']['count'], 0, ',', '.') ?></div>
                        <div class="text-muted small fw-semibold">Total Pencarian</div>
                    </div>
                </div>
            </div>

            <!-- 2. Login Sales (Hijau) -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm" style="background-color: #f0fdf4; border-radius: 12px; border-left: 4px solid #16a34a !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px; background-color: #dcfce7; color: #16a34a;">
                                <i class="fa-solid fa-right-to-bracket"></i>
                            </span>
                            <?php $trend = $statCards['login']['trend']; ?>
                            <?php if ($trend['direction'] === 'up'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small"><?= $trend['label'] ?></span>
                            <?php elseif ($trend['direction'] === 'down'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small"><?= $trend['label'] ?></span>
                            <?php else: ?>
                                <span class="badge border px-2 py-1 small" style="color: #64748b !important; background-color: #f8fafc;"><?= $trend['label'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="fs-4 fw-bold text-dark mb-1"><?= number_format($statCards['login']['count'], 0, ',', '.') ?></div>
                        <div class="text-muted small fw-semibold">Login Sales</div>
                    </div>
                </div>
            </div>

            <!-- 3. Klik Detail (Ungu) -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm" style="background-color: #faf5ff; border-radius: 12px; border-left: 4px solid #9333ea !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px; background-color: #f3e8ff; color: #9333ea;">
                                <i class="fa-regular fa-file-lines"></i>
                            </span>
                            <?php $trend = $statCards['detail']['trend']; ?>
                            <?php if ($trend['direction'] === 'up'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small"><?= $trend['label'] ?></span>
                            <?php elseif ($trend['direction'] === 'down'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small"><?= $trend['label'] ?></span>
                            <?php else: ?>
                                <span class="badge border px-2 py-1 small" style="color: #64748b !important; background-color: #f8fafc;"><?= $trend['label'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="fs-4 fw-bold text-dark mb-1"><?= number_format($statCards['detail']['count'], 0, ',', '.') ?></div>
                        <div class="text-muted small fw-semibold">Klik Detail</div>
                    </div>
                </div>
            </div>

            <!-- 4. Klik Info Komisi (Oranye) -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm" style="background-color: #fff7ed; border-radius: 12px; border-left: 4px solid #ea580c !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px; background-color: #ffedd5; color: #ea580c;">
                                <i class="fa-solid fa-circle-info"></i>
                            </span>
                            <?php $trend = $statCards['commission']['trend']; ?>
                            <?php if ($trend['direction'] === 'up'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small"><?= $trend['label'] ?></span>
                            <?php elseif ($trend['direction'] === 'down'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small"><?= $trend['label'] ?></span>
                            <?php else: ?>
                                <span class="badge border px-2 py-1 small" style="color: #64748b !important; background-color: #f8fafc;"><?= $trend['label'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="fs-4 fw-bold text-dark mb-1"><?= number_format($statCards['commission']['count'], 0, ',', '.') ?></div>
                        <div class="text-muted small fw-semibold">Klik Info Komisi</div>
                    </div>
                </div>
            </div>

            <!-- 5. Cetak PDF (Teal) -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm" style="background-color: #f0fdfa; border-radius: 12px; border-left: 4px solid #0d9488 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px; background-color: #ccfbf1; color: #0d9488;">
                                <i class="fa-solid fa-file-pdf"></i>
                            </span>
                            <?php $trend = $statCards['pdf']['trend']; ?>
                            <?php if ($trend['direction'] === 'up'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small"><?= $trend['label'] ?></span>
                            <?php elseif ($trend['direction'] === 'down'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small"><?= $trend['label'] ?></span>
                            <?php else: ?>
                                <span class="badge border px-2 py-1 small" style="color: #64748b !important; background-color: #f8fafc;"><?= $trend['label'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="fs-4 fw-bold text-dark mb-1"><?= number_format($statCards['pdf']['count'], 0, ',', '.') ?></div>
                        <div class="text-muted small fw-semibold">Cetak PDF</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- C. Baris Grafik (Line Chart Kiri, Donut Chart Kanan) -->
        <div class="row g-4 mb-4">
            <!-- Grafik Garis (Aktivitas Harian) -->
            <div class="col-12 col-xl-8">
                <div class="app-card shadow-sm h-100" style="border-radius: 12px;">
                    <div class="app-card-header p-3 border-bottom d-flex align-items-center justify-content-between">
                        <h4 class="app-card-title mb-0 fw-bold" style="font-size: 1.05rem;">
                            <i class="fa-solid fa-chart-line text-primary me-2"></i>Grafik Aktivitas Pengguna
                        </h4>
                        <span class="badge border" style="color: #64748b !important; background-color: #f8fafc; font-size: 11.5px;">Harian</span>
                    </div>
                    <div class="app-card-body p-3">
                        <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                            <canvas id="canvas-activity-line"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik Donut (Ringkasan Proporsi Aktivitas) -->
            <div class="col-12 col-xl-4">
                <div class="app-card shadow-sm h-100" style="border-radius: 12px;">
                    <div class="app-card-header p-3 border-bottom d-flex align-items-center justify-content-between">
                        <h4 class="app-card-title mb-0 fw-bold" style="font-size: 1.05rem;">
                            <i class="fa-solid fa-chart-pie text-success me-2"></i>Ringkasan Aktivitas
                        </h4>
                        <span class="badge border" style="color: #64748b !important; background-color: #f8fafc; font-size: 11.5px;">Proporsi</span>
                    </div>
                    <div class="app-card-body p-3 d-flex flex-column justify-content-between">
                        <div class="chart-container" style="position: relative; height: 210px; width: 100%;">
                            <canvas id="canvas-activity-donut"></canvas>
                        </div>
                        <!-- Legenda Persentase Kustom -->
                        <div class="mt-3 pt-2 border-top">
                            <div class="row g-2 small text-muted">
                                <div class="col-6 d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #0d6efd;"></span>
                                    <span>Pencarian: <strong><?= $donutChartData['percentages']['search'] ?>%</strong></span>
                                </div>
                                <div class="col-6 d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #16a34a;"></span>
                                    <span>Login: <strong><?= $donutChartData['percentages']['login'] ?>%</strong></span>
                                </div>
                                <div class="col-6 d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #9333ea;"></span>
                                    <span>Detail: <strong><?= $donutChartData['percentages']['detail'] ?>%</strong></span>
                                </div>
                                <div class="col-6 d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #ea580c;"></span>
                                    <span>Komisi: <strong><?= $donutChartData['percentages']['commission'] ?>%</strong></span>
                                </div>
                                <div class="col-6 d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #0d9488;"></span>
                                    <span>PDF: <strong><?= $donutChartData['percentages']['pdf'] ?>%</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- D. Baris Tabel 1 (2 Panel: History Pencarian & History Login Sales) -->
        <div class="row g-4 mb-4">
            <!-- Tabel 1: History Pencarian -->
            <div class="col-12 col-lg-6">
                <div class="app-card app-card-orders-table shadow-sm h-100" style="border-radius: 12px;">
                    <div class="app-card-header p-3 border-bottom d-flex align-items-center justify-content-between">
                        <h4 class="app-card-title mb-0 fw-bold" style="font-size: 1rem;">
                            <i class="fa-solid fa-magnifying-glass text-primary me-2"></i>History Pencarian
                        </h4>
                        <a href="#" class="btn btn-sm btn-link text-decoration-none text-muted p-0" style="font-size: 13px;">Lihat Semua &rarr;</a>
                    </div>
                    <div class="app-card-body p-3 pt-0">
                        <div class="table-responsive">
                            <table class="table app-table-hover mb-0 text-left align-middle">
                                <thead>
                                    <tr>
                                        <th class="cell text-center" style="width: 45px;">No</th>
                                        <th class="cell">Kata Kunci</th>
                                        <th class="cell">Tanggal & Waktu</th>
                                        <th class="cell text-center">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($searchHistory)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada data</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $no = 1; foreach ($searchHistory as $row): ?>
                                            <tr>
                                                <td class="cell text-center text-muted"><?= $no++ ?></td>
                                                <td class="cell">
                                                    <span class="badge border font-monospace" style="color: #1e293b !important; background-color: #f8fafc; font-size: 13px; font-weight: 600; padding: 5px 9px;">
                                                        <?= esc($row['keyword']) ?>
                                                    </span>
                                                </td>
                                                <td class="cell text-muted small" style="white-space: nowrap;">
                                                    <?= formatTglIndo($row['last_created_at'] ?? '') ?>
                                                </td>
                                                <td class="cell text-center">
                                                    <span class="badge bg-primary rounded-pill"><?= (int) $row['total_count'] ?>x</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel 2: History Login dari Sales -->
            <div class="col-12 col-lg-6">
                <div class="app-card app-card-orders-table shadow-sm h-100" style="border-radius: 12px;">
                    <div class="app-card-header p-3 border-bottom d-flex align-items-center justify-content-between">
                        <h4 class="app-card-title mb-0 fw-bold" style="font-size: 1rem;">
                            <i class="fa-solid fa-user-check text-success me-2"></i>History Login dari Sales
                        </h4>
                        <a href="#" class="btn btn-sm btn-link text-decoration-none text-muted p-0" style="font-size: 13px;">Lihat Semua &rarr;</a>
                    </div>
                    <div class="app-card-body p-3 pt-0">
                        <div class="table-responsive">
                            <table class="table app-table-hover mb-0 text-left align-middle">
                                <thead>
                                    <tr>
                                        <th class="cell text-center" style="width: 45px;">No</th>
                                        <th class="cell">Nama Sales</th>
                                        <th class="cell">Email</th>
                                        <th class="cell">Waktu Login</th>
                                        <th class="cell">IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($loginHistory)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada data</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $no = 1; foreach ($loginHistory as $row): ?>
                                            <tr>
                                                <td class="cell text-center text-muted"><?= $no++ ?></td>
                                                <td class="cell fw-semibold text-dark"><?= esc($row['nama_sales'] ?? 'Sales') ?></td>
                                                <td class="cell text-muted small"><?= esc($row['sales_email'] ?? '-') ?></td>
                                                <td class="cell text-muted small" style="white-space: nowrap;">
                                                    <?= formatTglIndo($row['created_at'] ?? '') ?>
                                                </td>
                                                <td class="cell text-muted small font-monospace"><?= esc($row['ip_address'] ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- E. Baris Tabel 2 (3 Panel: Klik Detail, Klik Info Komisi, History PDF) -->
        <div class="row g-4 mb-4">
            <!-- Panel 1: History Klik Detail -->
            <div class="col-12 col-lg-4">
                <div class="app-card app-card-orders-table shadow-sm h-100" style="border-radius: 12px;">
                    <div class="app-card-header p-3 border-bottom d-flex align-items-center justify-content-between">
                        <h4 class="app-card-title mb-0 fw-bold" style="font-size: 1rem;">
                            <i class="fa-regular fa-file-lines text-purple me-2" style="color: #9333ea;"></i>History Klik Detail
                        </h4>
                        <a href="#" class="btn btn-sm btn-link text-decoration-none text-muted p-0" style="font-size: 13px;">Lihat Semua &rarr;</a>
                    </div>
                    <div class="app-card-body p-3 pt-0">
                        <div class="table-responsive">
                            <table class="table app-table-hover mb-0 text-left align-middle">
                                <thead>
                                    <tr>
                                        <th class="cell text-center" style="width: 40px;">No</th>
                                        <th class="cell">Produk</th>
                                        <th class="cell text-end">Tanggal & Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($detailClickHistory)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Belum ada data</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $no = 1; foreach ($detailClickHistory as $row): ?>
                                            <tr>
                                                <td class="cell text-center text-muted"><?= $no++ ?></td>
                                                <td class="cell">
                                                    <div class="text-truncate" style="max-width: 170px;" title="<?= esc($row['product_name']) ?>">
                                                        <?= esc($row['product_name']) ?>
                                                    </div>
                                                </td>
                                                <td class="cell text-end text-muted small" style="white-space: nowrap;">
                                                    <?= formatTglIndo($row['created_at'] ?? '') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel 2: History Klik Info Komisi -->
            <div class="col-12 col-lg-4">
                <div class="app-card app-card-orders-table shadow-sm h-100" style="border-radius: 12px;">
                    <div class="app-card-header p-3 border-bottom d-flex align-items-center justify-content-between">
                        <h4 class="app-card-title mb-0 fw-bold" style="font-size: 1rem;">
                            <i class="fa-solid fa-circle-info text-warning me-2" style="color: #ea580c;"></i>History Klik Info Komisi
                        </h4>
                        <a href="#" class="btn btn-sm btn-link text-decoration-none text-muted p-0" style="font-size: 13px;">Lihat Semua &rarr;</a>
                    </div>
                    <div class="app-card-body p-3 pt-0">
                        <div class="table-responsive">
                            <table class="table app-table-hover mb-0 text-left align-middle">
                                <thead>
                                    <tr>
                                        <th class="cell text-center" style="width: 40px;">No</th>
                                        <th class="cell">Produk</th>
                                        <th class="cell text-end">Tanggal & Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($commissionClickHistory)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Belum ada data</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $no = 1; foreach ($commissionClickHistory as $row): ?>
                                            <tr>
                                                <td class="cell text-center text-muted"><?= $no++ ?></td>
                                                <td class="cell">
                                                    <div class="text-truncate" style="max-width: 170px;" title="<?= esc($row['product_name']) ?>">
                                                        <?= esc($row['product_name']) ?>
                                                    </div>
                                                </td>
                                                <td class="cell text-end text-muted small" style="white-space: nowrap;">
                                                    <?= formatTglIndo($row['created_at'] ?? '') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel 3: History PDF -->
            <div class="col-12 col-lg-4">
                <div class="app-card app-card-orders-table shadow-sm h-100" style="border-radius: 12px;">
                    <div class="app-card-header p-3 border-bottom d-flex align-items-center justify-content-between">
                        <h4 class="app-card-title mb-0 fw-bold" style="font-size: 1rem;">
                            <i class="fa-solid fa-file-pdf text-teal me-2" style="color: #0d9488;"></i>History PDF
                        </h4>
                        <a href="#" class="btn btn-sm btn-link text-decoration-none text-muted p-0" style="font-size: 13px;">Lihat Semua &rarr;</a>
                    </div>
                    <div class="app-card-body p-3 pt-0">
                        <div class="table-responsive">
                            <table class="table app-table-hover mb-0 text-left align-middle">
                                <thead>
                                    <tr>
                                        <th class="cell text-center" style="width: 40px;">No</th>
                                        <th class="cell">Jenis PDF</th>
                                        <th class="cell">Tanggal & Waktu</th>
                                        <th class="cell text-center">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pdfHistory)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada data</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $no = 1; foreach ($pdfHistory as $row): ?>
                                            <tr>
                                                <td class="cell text-center text-muted"><?= $no++ ?></td>
                                                <td class="cell">
                                                    <span class="badge border" style="color: #1e293b !important; background-color: #f8fafc; font-size: 12px; font-weight: 600; padding: 5px 9px;">
                                                        <?= esc($row['pdf_type']) ?>
                                                    </span>
                                                </td>
                                                <td class="cell text-muted small" style="white-space: nowrap;">
                                                    <?= formatTglIndo($row['last_created_at'] ?? '') ?>
                                                </td>
                                                <td class="cell text-center">
                                                    <span class="badge bg-secondary rounded-pill"><?= (int) $row['total_count'] ?>x</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- SCRIPT CHART.JS LOKAL UNTUK VISUALISASI AKTIVITAS -->
<script src="<?= base_url('assets/admin/plugins/chart.js/chart.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // 1. Line Chart: Grafik Aktivitas Pengguna (5 Seri Harian)
    // -------------------------------------------------------------
    const lineCtx = document.getElementById('canvas-activity-line');
    if (lineCtx) {
        const lineLabels = <?= json_encode($lineChartData['labels'] ?? []) ?>;
        const searchData = <?= json_encode($lineChartData['search'] ?? []) ?>;
        const loginData = <?= json_encode($lineChartData['login'] ?? []) ?>;
        const detailData = <?= json_encode($lineChartData['detail'] ?? []) ?>;
        const commData = <?= json_encode($lineChartData['commission'] ?? []) ?>;
        const pdfData = <?= json_encode($lineChartData['pdf'] ?? []) ?>;

        new Chart(lineCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: lineLabels,
                datasets: [
                    {
                        label: 'Pencarian',
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.08)',
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        data: searchData,
                        lineTension: 0.25
                    },
                    {
                        label: 'Login Sales',
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22, 163, 74, 0.08)',
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        data: loginData,
                        lineTension: 0.25
                    },
                    {
                        label: 'Klik Detail',
                        borderColor: '#9333ea',
                        backgroundColor: 'rgba(147, 51, 234, 0.08)',
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        data: detailData,
                        lineTension: 0.25
                    },
                    {
                        label: 'Info Komisi',
                        borderColor: '#ea580c',
                        backgroundColor: 'rgba(234, 88, 12, 0.08)',
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        data: commData,
                        lineTension: 0.25
                    },
                    {
                        label: 'Cetak PDF',
                        borderColor: '#0d9488',
                        backgroundColor: 'rgba(13, 148, 136, 0.08)',
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        data: pdfData,
                        lineTension: 0.25
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        fontSize: 12,
                        usePointStyle: true
                    }
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false,
                            drawBorder: true
                        },
                        ticks: {
                            fontSize: 11,
                            autoSkip: true,
                            maxTicksLimit: 12
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            color: '#f1f5f9',
                            zeroLineColor: '#cbd5e1'
                        },
                        ticks: {
                            beginAtZero: true,
                            precision: 0,
                            fontSize: 11
                        }
                    }]
                }
            }
        });
    }

    // -------------------------------------------------------------
    // 2. Donut Chart: Ringkasan Aktivitas (Proporsi & Teks Tengah)
    // -------------------------------------------------------------
    const donutCtx = document.getElementById('canvas-activity-donut');
    if (donutCtx) {
        const total = <?= (int) $totalActivities ?>;
        const counts = <?= json_encode($donutChartData['counts'] ?? [0, 0, 0, 0, 0]) ?>;

        const chartDatasets = (total > 0) ? [{
            data: counts,
            backgroundColor: ['#0d6efd', '#16a34a', '#9333ea', '#ea580c', '#0d9488'],
            borderWidth: 2,
            borderColor: '#ffffff'
        }] : [{
            data: [1],
            backgroundColor: ['#e2e8f0'],
            borderWidth: 2,
            borderColor: '#ffffff'
        }];

        const chartLabels = (total > 0) 
            ? ['Pencarian', 'Login Sales', 'Klik Detail', 'Klik Info Komisi', 'Cetak PDF']
            : ['Belum ada aktivitas'];

        // Plugin kustom untuk teks di tengah donut
        const centerTextPlugin = {
            beforeDraw: function (chart) {
                const width = chart.chart.width;
                const height = chart.chart.height;
                const ctx = chart.chart.ctx;
                ctx.restore();

                const fontSizeNumber = (height / 110).toFixed(2);
                ctx.font = 'bold ' + fontSizeNumber + 'em sans-serif';
                ctx.textBaseline = 'middle';
                ctx.fillStyle = '#1e293b';

                const textNum = total.toString();
                const textNumX = Math.round((width - ctx.measureText(textNum).width) / 2);
                const textNumY = height / 2 - 10;
                ctx.fillText(textNum, textNumX, textNumY);

                const fontSizeLabel = (height / 200).toFixed(2);
                ctx.font = '600 ' + fontSizeLabel + 'em sans-serif';
                ctx.fillStyle = '#64748b';

                const textLabel = 'Total Aktivitas';
                const textLabelX = Math.round((width - ctx.measureText(textLabel).width) / 2);
                const textLabelY = height / 2 + 15;
                ctx.fillText(textLabel, textLabelX, textLabelY);

                ctx.save();
            }
        };

        new Chart(donutCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: chartDatasets
            },
            plugins: [centerTextPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 70,
                legend: {
                    display: false
                },
                tooltips: {
                    enabled: total > 0,
                    callbacks: {
                        label: function (tooltipItem, data) {
                            const dataset = data.datasets[tooltipItem.datasetIndex];
                            const currentValue = dataset.data[tooltipItem.index];
                            const label = data.labels[tooltipItem.index] || '';
                            const pct = total > 0 ? Math.round((currentValue / total) * 100) : 0;
                            return ' ' + label + ': ' + currentValue + ' (' + pct + '%)';
                        }
                    }
                }
            }
        });
    }
});
</script>

<?= $this->endSection('content') ?>
