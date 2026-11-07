<?= $this->extend('admin/template/template'); ?>
<?= $this->section('content'); ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">

        <!-- Filter Bar & Header -->
        <?= $this->include('admin/laporan_aktivitas/_filter_bar', [
            'pageTitle'    => $pageTitle,
            'pageSubtitle' => $pageSubtitle,
            'filters'      => $filters,
            'exportUrl'    => base_url('admin/laporan-aktivitas/sales-teraktif/export'),
            'showMode'     => true,
            'showProduct'  => true,
            'productList'  => $productList,
        ]); ?>

        <!-- Catatan Pengecualian Aturan Filter -->
        <?php if ($isModeActive || $isProductActive): ?>
            <div class="alert alert-info border-0 shadow-sm py-2 px-3 small d-flex align-items-center gap-2 mb-3" style="background-color: #f0fdf4; border-left: 4px solid #16a34a !important; color: #166534;">
                <i class="fa-solid fa-circle-info fs-6"></i>
                <div>
                    <strong>Catatan Filter:</strong>
                    <?php if ($isModeActive && !$isProductActive): ?>
                        Aktivitas <strong>Login</strong> tidak memiliki relasi mode (Internal/Eksternal), sehingga kolom Login ditampilkan <code>-</code> dan tidak dihitung ke Total Aktivitas.
                    <?php elseif (!$isModeActive && $isProductActive): ?>
                        Aktivitas <strong>Login</strong> dan <strong>Pencarian</strong> tidak terikat produk tertentu, sehingga ditampilkan <code>-</code> dan tidak dihitung ke Total Aktivitas.
                    <?php else: ?>
                        Karena filter Mode dan Produk aktif, aktivitas <strong>Login</strong> dan <strong>Pencarian</strong> tidak terikat ke kriteria tersebut sehingga ditampilkan <code>-</code> dan tidak dihitung ke Total Aktivitas.
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
                                <th class="text-center" style="width: 70px;">Peringkat</th>
                                <th>Nama Sales</th>
                                <th class="text-center">Login</th>
                                <th class="text-center">Pencarian</th>
                                <th class="text-center">Klik Detail</th>
                                <th class="text-center">Klik Komisi</th>
                                <th class="text-center">Cetak PDF</th>
                                <th class="text-center">Total Aktivitas</th>
                                <th>Produk Sering Dikunjungi</th>
                                <th style="white-space: nowrap;">Terakhir Aktif</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">Belum ada data</td>
                                </tr>
                            <?php else: ?>
                                <?php $rank = $from; ?>
                                <?php foreach ($items as $row): ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php if ($rank === 1): ?>
                                                <span class="badge bg-warning text-dark px-2 py-1"><i class="fa-solid fa-trophy me-1"></i>#1</span>
                                            <?php elseif ($rank === 2): ?>
                                                <span class="badge bg-secondary text-white px-2 py-1">#2</span>
                                            <?php elseif ($rank === 3): ?>
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1">#3</span>
                                            <?php else: ?>
                                                <span class="text-muted fw-bold">#<?= $rank ?></span>
                                            <?php endif; ?>
                                            <?php $rank++; ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= esc($row['nama_sales']) ?></div>
                                            <small class="text-muted"><?= esc($row['sales_email']) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['count_login'] === null): ?>
                                                <span class="text-muted" title="Tidak dihitung karena filter aktif">-</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark border px-2 py-1" style="--bs-badge-color: #1e293b; color: #1e293b !important;">
                                                    <?= number_format($row['count_login'], 0, ',', '.') ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['count_search'] === null): ?>
                                                <span class="text-muted" title="Tidak dihitung karena filter aktif">-</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark border px-2 py-1" style="--bs-badge-color: #1e293b; color: #1e293b !important;">
                                                    <?= number_format($row['count_search'], 0, ',', '.') ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info-subtle text-primary px-2 py-1 border"><?= number_format($row['count_detail'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning-subtle text-dark px-2 py-1 border"><?= number_format($row['count_comm'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-dark px-2 py-1 border"><?= number_format($row['count_pdf'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary text-white px-2 py-1 fs-6 fw-bold"><?= number_format($row['total_aktivitas'], 0, ',', '.') ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['top_visited_product'])): ?>
                                                <span class="badge border font-monospace text-wrap text-start px-2 py-1" style="--bs-badge-color: #1e293b; color: #1e293b !important; background-color: #f8fafc; font-weight: 500;">
                                                    <?= esc($row['top_visited_product']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-muted" style="white-space: nowrap;">
                                            <?= App\Controllers\admin\LaporanAktivitas::formatTglIndo($row['last_active'] ?? null) ?>
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
