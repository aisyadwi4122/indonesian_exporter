<?= $this->extend('admin/template/template'); ?>
<?= $this->section('content'); ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">

        <!-- Header: Judul & Rentang Tanggal -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1 class="app-page-title mb-1 fw-bold fs-4 text-dark">History Login Sales</h1>
                <p class="text-muted small mb-0">Daftar seluruh aktivitas login dari sales</p>
            </div>
            <div>
                <form method="GET" action="<?= current_url() ?>" class="d-flex align-items-center gap-2">
                    <?php if (!empty($filters['search_sales'])): ?>
                        <input type="hidden" name="search_sales" value="<?= esc($filters['search_sales']) ?>">
                    <?php endif; ?>
                    <div class="input-group input-group-sm shadow-sm">
                        <span class="input-group-text bg-white text-muted"><i class="fa-regular fa-calendar"></i></span>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($filters['start_date']) ?>">
                        <span class="input-group-text bg-white text-muted">-</span>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($filters['end_date']) ?>">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Terapkan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filter Bar & Export -->
        <div class="app-card app-card-settings shadow-sm p-3 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3">
                <form method="GET" action="<?= current_url() ?>" class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 flex-grow-1">
                    <input type="hidden" name="start_date" value="<?= esc($filters['start_date']) ?>">
                    <input type="hidden" name="end_date" value="<?= esc($filters['end_date']) ?>">

                    <div class="input-group input-group-sm" style="max-width: 320px;">
                        <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search_sales" class="form-control form-control-sm" placeholder="Cari nama sales..." value="<?= esc($filters['search_sales']) ?>">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-secondary text-white">Filter</button>
                        <?php if (!empty($filters['search_sales'])): ?>
                            <a href="<?= current_url() . '?start_date=' . urlencode($filters['start_date']) . '&end_date=' . urlencode($filters['end_date']) ?>" class="btn btn-sm btn-light border">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>

                <div>
                    <?php
                    $exportQuery = http_build_query([
                        'start_date'   => $filters['start_date'],
                        'end_date'     => $filters['end_date'],
                        'search_sales' => $filters['search_sales'],
                    ]);
                    ?>
                    <a href="<?= base_url('admin/laporan-aktivitas/login-sales/export?' . $exportQuery) ?>" class="btn btn-sm btn-primary text-white d-inline-flex align-items-center gap-1 shadow-sm">
                        <i class="fa-solid fa-download"></i> Export
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="app-card app-card-orders-table shadow-sm mb-4">
            <div class="app-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">No</th>
                                <th>Nama Sales</th>
                                <th>Email</th>
                                <th>Waktu Login</th>
                                <th>IP Address</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada data</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = $from; ?>
                                <?php foreach ($items as $row): ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $no++ ?></td>
                                        <td class="fw-semibold text-dark"><?= esc($row['nama_sales']) ?></td>
                                        <td class="text-muted"><?= esc($row['sales_email']) ?></td>
                                        <td class="text-nowrap"><?= App\Controllers\admin\LaporanAktivitas::formatTglIndo($row['created_at']) ?></td>
                                        <td><code><?= esc($row['ip_address']) ?></code></td>
                                        <td class="text-center">
                                            <span class="badge bg-success text-white px-2 py-1">Berhasil</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Pagination -->
            <div class="app-card-footer p-3 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div class="text-muted small">
                    Menampilkan <?= $from ?> - <?= $to ?> dari <?= $total ?> data
                </div>
                <?php if (!empty($pagination['items'])): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <?php
                            $prevParams = array_merge($filters, ['page' => max(1, $page - 1)]);
                            $prevUrl = current_url() . '?' . http_build_query($prevParams);
                            ?>
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= ($page > 1) ? $prevUrl : '#' ?>">Sebelumnya</a>
                            </li>

                            <?php foreach ($pagination['items'] as $item): ?>
                                <?php if ($item === '...'): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php else: ?>
                                    <?php
                                    $pageParams = array_merge($filters, ['page' => $item]);
                                    $pageUrl = current_url() . '?' . http_build_query($pageParams);
                                    ?>
                                    <li class="page-item <?= ($page == $item) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= $pageUrl ?>"><?= $item ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <?php
                            $nextParams = array_merge($filters, ['page' => min($totalPages, $page + 1)]);
                            $nextUrl = current_url() . '?' . http_build_query($nextParams);
                            ?>
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= ($page < $totalPages) ? $nextUrl : '#' ?>">Berikutnya</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection(); ?>
