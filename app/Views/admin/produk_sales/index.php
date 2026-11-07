<?= $this->extend('admin/template/template'); ?>
<?= $this->section('content'); ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-auto">
                <h1 class="app-page-title mb-0">Produk supplier</h1>
                <p class="text-muted small mb-0">Kelola katalog produk supplier yang ditampilkan di area Sales & Eksternal.</p>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('admin/produk_sales/tambah') ?>" class="btn btn-primary text-white">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Produk Sales
                </a>
            </div>
        </div><!--//row-->

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="row g-2 mb-3 align-items-center justify-content-end">
            <div class="col-12 col-md-5 col-lg-4">
                <form action="<?= base_url('admin/produk_sales/index') ?>" method="GET">
                    <div class="input-group">
                        <input type="text" 
                               name="q" 
                               class="form-control" 
                               placeholder="Cari kode, nama, lokasi, supplier..." 
                               value="<?= esc($search_query) ?>">
                        <button class="btn btn-primary text-white" type="submit" title="Cari">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        <?php if ($search_query !== '') : ?>
                            <a href="<?= base_url('admin/produk_sales/index') ?>" class="btn btn-outline-secondary" title="Reset Pencarian">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="app-card app-card-orders-table shadow-sm mb-4">
            <div class="app-card-body">
                <div class="table-responsive">
                    <table class="table app-table-hover mb-0 text-left align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 80px;">Foto</th>
                                <th style="width: 110px;">Kode</th>
                                <th>Nama Produk</th>
                                <th>Supplier / Perusahaan</th>
                                <th>Lokasi</th>
                                <th class="text-center" style="width: 130px;">Status Ekspor</th>
                                <th class="text-center" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)) : ?>
                                <?php foreach ($products as $p) : ?>
                                    <?php 
                                        $id         = (int)$p['id_produk_supplier'];
                                        $kodeProduk = !empty($p['kode_produk']) ? $p['kode_produk'] : \App\Models\SupplierProductModel::getKodeProduk($id);
                                        $photoUrl   = !empty($p['foto_url']) ? $p['foto_url'] : \App\Models\SupplierProductModel::photoUrl($p, 1);
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <img src="<?= $photoUrl ? esc($photoUrl) : base_url('assets/img/logo.png') ?>" 
                                                 alt="<?= esc($p['nama_produk']) ?>" 
                                                 class="rounded border" 
                                                 style="width: 60px; height: 60px; object-fit: cover;"
                                                 onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>';">
                                        </td>
                                        <td>
                                            <span class="badge font-monospace px-2 py-1" style="color: #0f172a !important; background-color: #f1f5f9; border: 1px solid #cbd5e1; font-size: 12px;">
                                                <?= esc($kodeProduk) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark mb-1"><?= esc($p['nama_produk']) ?></div>
                                            <?php if (!empty($p['spesifikasi'])) : ?>
                                                <small class="text-muted d-block text-truncate" style="max-width: 280px;">
                                                    <?= esc(strip_tags($p['spesifikasi'])) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="fw-semibold"><?= esc($p['nama_perusahaan'] ?? '-') ?></span>
                                            <?php if (!empty($p['domisili_usaha'])) : ?>
                                                <small class="text-muted d-block"><?= esc($p['domisili_usaha']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <i class="fa-solid fa-location-dot text-danger me-1 small"></i>
                                            <?= esc($p['lokasi_barang'] ?: ($p['domisili_usaha'] ?? '-')) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (strtolower(trim((string)$p['sudah_ekspor'])) === 'sudah') : ?>
                                                <span class="badge bg-success">
                                                    <i class="fa-solid fa-check me-1"></i> Sudah Ekspor
                                                </span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary">
                                                    Belum Ekspor
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?= base_url('admin/produk_sales/edit/' . $id) ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Ubah Produk">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal<?= $id ?>" 
                                                        title="Hapus Produk">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-box-open fs-2 mb-2 d-block"></i>
                                        Tidak ada produk sales yang ditemukan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div><!--//table-responsive-->
            </div><!--//app-card-body-->

            <?php if ($pager && $pager->getPageCount() > 1) : ?>
                <div class="app-card-footer p-3 border-top">
                    <?= $pager->links('default', 'pager_rapi') ?>
                </div>
            <?php endif; ?>
        </div><!--//app-card-->

    </div><!--//container-xl-->
</div><!--//app-content-->

<!-- Modal Konfirmasi Hapus untuk setiap produk -->
<?php if (!empty($products)) : ?>
    <?php foreach ($products as $p) : ?>
        <?php 
            $id         = (int)$p['id_produk_supplier'];
            $kodeProduk = \App\Models\SupplierProductModel::getKodeProduk($id);
        ?>
        <div class="modal fade" id="deleteModal<?= $id ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $id ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-danger" id="deleteModalLabel<?= $id ?>">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>Konfirmasi Hapus Produk
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <p class="mb-1">Apakah Anda yakin ingin menghapus produk ini?</p>
                        <div class="p-2 bg-light rounded border mt-2">
                            <strong>Kode:</strong> <?= esc($kodeProduk) ?><br>
                            <strong>Nama:</strong> <?= esc($p['nama_produk']) ?><br>
                            <strong>Supplier:</strong> <?= esc($p['nama_perusahaan'] ?? '-') ?>
                        </div>
                        <small class="text-danger mt-2 d-block">Tindakan ini tidak dapat dibatalkan dan file foto produk akan dihapus permanen.</small>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <form action="<?= base_url('admin/produk_sales/delete/' . $id) ?>" method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger text-white">
                                <i class="fa-solid fa-trash me-1"></i> Ya, Hapus Produk
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection(); ?>
