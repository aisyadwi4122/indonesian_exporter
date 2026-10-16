<?= $this->extend('supplier/layout') ?>

<?= $this->section('content') ?>

<!-- Flash Alerts -->
<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="fa-solid fa-circle-check fs-5"></i>
        <div><?= esc(session()->getFlashdata('success')) ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="fa-solid fa-circle-exclamation fs-5"></i>
        <div><?= session()->getFlashdata('error') ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0 text-dark">
            <i class="fa-solid fa-clock-rotate-left text-success me-2"></i>Catatan History Ekspor Perusahaan
        </h5>
        <span class="badge bg-light text-dark border">Total: <?= count($histories) ?> Catatan</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Tahun</th>
                        <th>Produk</th>
                        <th>Negara Tujuan</th>
                        <th>Jenis Ekspor</th>
                        <th>Kuantitas</th>
                        <th>Nilai Ekspor (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($histories)) : ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-plane-departure fs-2 mb-2 d-block text-secondary opacity-50"></i>
                                Belum ada catatan history ekspor untuk perusahaan Anda.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php $no = 1; foreach ($histories as $row) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-bold"><?= esc($row['tahun']) ?></td>
                                <td><?= esc($row['produk'] ?? '-') ?></td>
                                <td><span class="badge bg-light text-dark border"><?= esc($row['negara_tujuan'] ?? '-') ?></span></td>
                                <td><?= esc($row['jenis_ekspor'] ?? '-') ?></td>
                                <td><?= esc($row['kuantitas'] ?? '-') ?></td>
                                <td class="fw-semibold text-success"><?= !empty($row['nilai_ekspor_usd']) ? '$ ' . number_format($row['nilai_ekspor_usd'], 2) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
