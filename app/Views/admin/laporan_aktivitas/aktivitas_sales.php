<?= $this->extend('admin/template/template'); ?>
<?= $this->section('content'); ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">

        <!-- Filter Bar & Header -->
        <?= $this->include('admin/laporan_aktivitas/_filter_bar', [
            'pageTitle'    => $pageTitle,
            'pageSubtitle' => $pageSubtitle,
            'filters'      => $filters,
            'exportUrl'    => base_url('admin/laporan-aktivitas/aktivitas-sales/export'),
            'showMode'     => true,
            'showSales'    => true,
            'showProduct'  => true,
            'showJenis'    => true,
            'salesList'    => $salesList,
            'productList'  => $productList,
        ]); ?>

        <!-- Catatan Pengecualian Aturan Filter -->
        <?php if ($isModeActive || $isProductActive): ?>
            <div class="alert alert-info border-0 shadow-sm py-2 px-3 small d-flex align-items-center gap-2 mb-3" style="background-color: #f0fdf4; border-left: 4px solid #16a34a !important; color: #166534;">
                <i class="fa-solid fa-circle-info fs-6"></i>
                <div>
                    <strong>Catatan Filter:</strong>
                    <?php if ($isModeActive && !$isProductActive): ?>
                        Aktivitas <strong>Login</strong> tidak ditampilkan karena login tidak memiliki mode tampilan.
                    <?php elseif (!$isModeActive && $isProductActive): ?>
                        Aktivitas <strong>Login</strong> dan <strong>Pencarian</strong> tidak ditampilkan karena tidak terikat pada produk tertentu.
                    <?php else: ?>
                        Aktivitas <strong>Login</strong> dan <strong>Pencarian</strong> tidak ditampilkan saat filter Mode dan Produk aktif secara bersamaan.
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabel Data -->
        <div class="app-card app-card-orders-table shadow-sm mb-4">
            <div class="app-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th style="white-space: nowrap;">Waktu</th>
                                <th>Nama Sales</th>
                                <th class="text-center">Mode</th>
                                <th class="text-center">Aktivitas</th>
                                <th>Produk</th>
                                <th>Perusahaan</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Belum ada data</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = $from; ?>
                                <?php foreach ($items as $row): ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $no++ ?></td>
                                        <td class="small text-muted" style="white-space: nowrap;">
                                            <?= App\Controllers\admin\LaporanAktivitas::formatTglIndo($row['created_at']) ?>
                                        </td>
                                        <td class="fw-semibold text-dark">
                                            <?= esc($row['nama_sales']) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['view_mode'] === 'internal'): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">Internal</span>
                                            <?php elseif ($row['view_mode'] === 'eksternal'): ?>
                                                <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1">Eksternal</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $actType = $row['activity_type'];
                                            if ($actType === 'Login') {
                                                echo '<span class="badge bg-success text-white px-2 py-1"><i class="fa-solid fa-right-to-bracket me-1"></i>Login</span>';
                                            } elseif ($actType === 'Pencarian') {
                                                echo '<span class="badge bg-secondary text-white px-2 py-1"><i class="fa-solid fa-magnifying-glass me-1"></i>Pencarian</span>';
                                            } elseif ($actType === 'Klik Detail') {
                                                echo '<span class="badge bg-info text-dark px-2 py-1" style="--bs-badge-color: #0f172a; color: #0f172a !important;"><i class="fa-solid fa-eye me-1"></i>Detail</span>';
                                            } elseif ($actType === 'Klik Info Komisi') {
                                                echo '<span class="badge bg-warning text-dark px-2 py-1" style="--bs-badge-color: #78350f; color: #78350f !important;"><i class="fa-solid fa-percent me-1"></i>Komisi</span>';
                                            } elseif ($actType === 'Cetak PDF') {
                                                echo '<span class="badge bg-danger text-white px-2 py-1"><i class="fa-solid fa-file-pdf me-1"></i>PDF</span>';
                                            } else {
                                                echo '<span class="badge bg-light text-dark">' . esc($actType) . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['product_name']) && $row['product_name'] !== '-'): ?>
                                                <span class="fw-semibold text-dark"><?= esc($row['product_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted">
                                            <?= esc($row['nama_perusahaan'] ?? '-') ?>
                                        </td>
                                        <td class="small">
                                            <?php if (!empty($row['keterangan']) && $row['keterangan'] !== '-'): ?>
                                                <span class="badge border font-monospace text-wrap text-start px-2 py-1" style="--bs-badge-color: #1e293b; color: #1e293b !important; background-color: #f8fafc; font-weight: 500;">
                                                    <?= esc($row['keterangan']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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
