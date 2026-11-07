<?= $this->extend('admin/template/template'); ?>
<?= $this->section('content'); ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">

        <!-- Filter Bar & Header -->
        <?= view('admin/laporan_aktivitas/_filter_bar', [
            'pageTitle'    => $pageTitle,
            'pageSubtitle' => $pageSubtitle,
            'filters'      => $filters,
            'exportUrl'    => $exportUrl ?? base_url('admin/laporan-aktivitas/produk-terlaris/export'),
            'showMode'     => true,
            'showSales'    => true,
            'salesList'    => $salesList,
        ]); ?>

        <!-- Tabel Data -->
        <div class="app-card app-card-orders-table shadow-sm mb-4">
            <div class="app-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 70px;">Peringkat</th>
                                <th>Produk</th>
                                <th>Perusahaan</th>
                                <th class="text-center">Klik Detail</th>
                                <th class="text-center">Klik Info Komisi</th>
                                <th class="text-center">Cetak PDF</th>
                                <th class="text-center">Total Minat</th>
                                <th class="text-center">Jumlah Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Belum ada data</td>
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
                                        <td class="fw-semibold text-dark"><?= esc($row['nama_produk']) ?></td>
                                        <td class="text-muted"><?= esc($row['nama_perusahaan']) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-info-subtle text-primary px-2 py-1 border"><?= number_format($row['click_detail'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning-subtle text-dark px-2 py-1 border"><?= number_format($row['click_komisi'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-dark px-2 py-1 border"><?= number_format($row['print_pdf'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary text-white px-2 py-1 fs-6 fw-bold"><?= number_format($row['total_minat'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-2 py-1" style="--bs-badge-color: #1e293b; color: #1e293b !important;">
                                                <i class="fa-solid fa-users me-1 text-muted"></i><?= number_format($row['total_sales'], 0, ',', '.') ?>
                                            </span>
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

