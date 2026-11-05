<?= $this->extend('admin/template/template'); ?>
<?= $this->Section('content'); ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <h1 class="app-page-title">Dashboard</h1>

        <div class="row g-4 mb-4">
            <div class="col-6 col-lg-3">
                <div class="app-card app-card-stat shadow-sm h-100">
                    <div class="app-card-body p-3 p-lg-4">
                        <h4 class="stats-type mb-1">Jumlah Slider</h4>
                        <div class="stats-figure"><?= $sliderCount; ?></div>
                    </div><!--//app-card-body-->
                    <a class="app-card-link-mask" href="<?= base_url('/admin/slider/index') ?>"></a>
                </div><!--//app-card-->
            </div><!--//col-->

            <div class="col-6 col-lg-3">
                <div class="app-card app-card-stat shadow-sm h-100">
                    <div class="app-card-body p-3 p-lg-4">
                        <h4 class="stats-type mb-1">Jumlah Produk</h4>
                        <div class="stats-figure"><?= $productCount; ?></div>
                        <div class="stats-meta text-success">
                        </div>
                    </div><!--//app-card-body-->
                    <a class="app-card-link-mask" href="<?= base_url('/admin/produk/index') ?>"></a>
                </div><!--//app-card-->
            </div><!--//col-->

            <div class="col-6 col-lg-3">
                <div class="app-card app-card-stat shadow-sm h-100">
                    <div class="app-card-body p-3 p-lg-4">
                        <h4 class="stats-type mb-1">Jumlah Artikel</h4>
                        <div class="stats-figure"><?= $artikelCount; ?></div>
                        <div class="stats-meta text-success">
                        </div>
                    </div><!--//app-card-body-->
                    <a class="app-card-link-mask" href="<?= base_url('/admin/artikel/index') ?>"></a>
                </div><!--//app-card-->
            </div><!--//col-->

            <div class="col-6 col-lg-3">
                <div class="app-card app-card-stat shadow-sm h-100">
                    <div class="app-card-body p-3 p-lg-4">
                        <h4 class="stats-type mb-1">Jumlah Pencarian</h4>
                        <div class="stats-figure"><?= (int) ($searchCount ?? 0); ?></div>
                        <div class="stats-meta text-muted small">
                            Pencarian Produk Sales
                        </div>
                    </div><!--//app-card-body-->
                    <a class="app-card-link-mask" href="#riwayat-pencarian"></a>
                </div><!--//app-card-->
            </div><!--//col-->
        </div><!--//row-->

        <!-- Tabel Riwayat Pencarian -->
        <div class="row g-4 mb-4" id="riwayat-pencarian">
            <!-- Tabel 1: Kata Paling Sering Dicari -->
            <div class="col-12 col-lg-5">
                <div class="app-card app-card-orders-table shadow-sm mb-4 h-100">
                    <div class="app-card-header p-3 border-bottom-0">
                        <h4 class="app-card-title mb-0" style="font-size: 1.1rem; font-weight: 600;">
                            <i class="fa-solid fa-fire me-2 text-danger"></i>Kata Paling Sering Dicari
                        </h4>
                    </div>
                    <div class="app-card-body p-3 pt-0">
                        <div class="table-responsive">
                            <table class="table app-table-hover mb-0 text-left">
                                <thead>
                                    <tr>
                                        <th class="cell text-center" style="width: 50px;">No</th>
                                        <th class="cell">Kata Kunci</th>
                                        <th class="cell text-center">Frekuensi</th>
                                        <th class="cell text-end">Terakhir Dicari</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($topKeywords)) : ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada riwayat pencarian</td>
                                        </tr>
                                    <?php else : ?>
                                        <?php $no = 1; foreach ($topKeywords as $item) : ?>
                                            <tr>
                                                <td class="cell text-center"><?= $no++ ?></td>
                                                <td class="cell">
                                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 13px;">
                                                        <?= esc($item['keyword']) ?>
                                                    </span>
                                                </td>
                                                <td class="cell text-center">
                                                    <span class="badge bg-primary rounded-pill"><?= (int) $item['total_search'] ?>x</span>
                                                </td>
                                                <td class="cell text-end text-muted small">
                                                    <?= !empty($item['last_searched_at']) ? date('d/m/Y H:i', strtotime($item['last_searched_at'])) : '-' ?>
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

            <!-- Tabel 2: Riwayat Pencarian Terbaru -->
            <div class="col-12 col-lg-7">
                <div class="app-card app-card-orders-table shadow-sm mb-4 h-100">
                    <div class="app-card-header p-3 border-bottom-0">
                        <h4 class="app-card-title mb-0" style="font-size: 1.1rem; font-weight: 600;">
                            <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Riwayat Pencarian Terbaru
                        </h4>
                    </div>
                    <div class="app-card-body p-3 pt-0">
                        <div class="table-responsive">
                            <table class="table app-table-hover mb-0 text-left">
                                <thead>
                                    <tr>
                                        <th class="cell text-center" style="width: 50px;">No</th>
                                        <th class="cell">Waktu</th>
                                        <th class="cell">Nama Sales</th>
                                        <th class="cell">Kata Kunci</th>
                                        <th class="cell text-center">Jumlah Hasil</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentSearches)) : ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat pencarian</td>
                                        </tr>
                                    <?php else : ?>
                                        <?php $no = 1; foreach ($recentSearches as $row) : ?>
                                            <tr>
                                                <td class="cell text-center"><?= $no++ ?></td>
                                                <td class="cell text-muted small" style="white-space: nowrap;">
                                                    <?= !empty($row['created_at']) ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-' ?>
                                                </td>
                                                <td class="cell">
                                                    <span class="fw-semibold text-dark"><?= esc($row['nama_sales'] ?? 'Sales') ?></span>
                                                </td>
                                                <td class="cell">
                                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 13px;">
                                                        <?= esc($row['keyword']) ?>
                                                    </span>
                                                </td>
                                                <td class="cell text-center">
                                                    <span class="badge bg-secondary rounded-pill"><?= (int) ($row['result_count'] ?? 0) ?></span>
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

    </div><!--//container-fluid-->
</div><!--//app-content-->

<?= $this->endSection('content') ?>
