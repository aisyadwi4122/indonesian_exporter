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
            <i class="fa-solid fa-box-open text-success me-2"></i>Daftar Produk Perusahaan
        </h5>
        <span class="badge bg-light text-dark border">Total: <?= count($products) ?> Produk</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Produk</th>
                        <th>Kapasitas Produksi</th>
                        <th>Lokasi Barang</th>
                        <th>MOQ</th>
                        <th>Harga EXW</th>
                        <th>Status Ekspor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)) : ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open fs-2 mb-2 d-block text-secondary opacity-50"></i>
                                Belum ada produk yang terdaftar untuk perusahaan Anda.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php $no = 1; foreach ($products as $prod) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-bold text-dark"><?= esc($prod['nama_produk']) ?></td>
                                <td><?= esc($prod['kapasitas_produksi'] ?? '-') ?></td>
                                <td><?= esc($prod['lokasi_barang'] ?? '-') ?></td>
                                <td><?= esc($prod['moq'] ?? '-') ?></td>
                                <td><?= esc($prod['harga_exw'] ?? '-') ?></td>
                                <td>
                                    <?php if (!empty($prod['sudah_ekspor']) && strtolower($prod['sudah_ekspor']) === 'ya') : ?>
                                        <span class="badge bg-success">Sudah Ekspor</span>
                                    <?php else : ?>
                                        <span class="badge bg-secondary">Belum Ekspor</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
