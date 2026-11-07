<?= $this->extend('supplier/layout') ?>

<?= $this->section('content') ?>

<style>
    .product-list-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .product-list-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .product-list-title {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .product-thumb {
        width: 58px;
        height: 58px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
    }
    .product-code-badge {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 12px;
        font-weight: 600;
        color: #0f172a;
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-block;
    }
    .search-input-group {
        max-width: 380px;
        width: 100%;
    }
</style>

<!-- Header & Toolbar -->
<div class="row g-3 mb-4 align-items-center justify-content-between">
    <div class="col-12 col-md-auto">
        <h2 class="h4 fw-bold text-dark mb-1">
            <i class="fa-solid fa-box-open text-success me-2"></i>Katalog Produk Perusahaan
        </h2>
        <p class="text-muted small mb-0">Kelola daftar komoditas dan produk ekspor perusahaan Anda.</p>
    </div>
    <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
        <a href="<?= base_url('supplier/produk/tambah') ?>" class="btn-sales-primary">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Produk</span>
        </a>
    </div>
</div>

<!-- Main Content Card -->
<div class="product-list-card">
    <div class="product-list-header">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                Total: <?= number_format($total_products ?? 0) ?> Produk
            </span>
            <?php if (!empty($search_query)) : ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                    Hasil filter: "<?= esc($search_query) ?>"
                </span>
            <?php endif; ?>
        </div>

        <!-- Search Bar -->
        <div class="search-input-group ms-auto">
            <form action="<?= base_url('supplier/produk') ?>" method="GET">
                <div class="input-group">
                    <input type="text" 
                           name="q" 
                           class="form-control" 
                           placeholder="Cari kode, nama, lokasi..." 
                           value="<?= esc($search_query) ?>">
                    <button class="btn btn-outline-secondary" type="submit" title="Cari Produk">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <?php if (!empty($search_query)) : ?>
                        <a href="<?= base_url('supplier/produk') ?>" class="btn btn-outline-secondary" title="Reset Pencarian">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 76px;">Foto</th>
                    <th style="width: 110px;">Kode</th>
                    <th>Nama Produk</th>
                    <th>Lokasi Barang</th>
                    <th>Kapasitas / MOQ</th>
                    <th class="text-center" style="width: 140px;">Status Ekspor</th>
                    <th class="text-center" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)) : ?>
                    <?php foreach ($products as $prod) : ?>
                        <?php 
                            $id         = (int)$prod['id_produk_supplier'];
                            $kodeProduk = \App\Models\SupplierProductModel::getKodeProduk($id);
                            $photoUrl   = \App\Models\SupplierProductModel::photoUrl($prod, 1);
                        ?>
                        <tr>
                            <td class="text-center">
                                <img src="<?= $photoUrl ? esc($photoUrl) : base_url('assets/img/logo.png') ?>" 
                                     alt="<?= esc($prod['nama_produk']) ?>" 
                                     class="product-thumb"
                                     onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>';">
                            </td>
                            <td>
                                <span class="product-code-badge">
                                    <?= esc($kodeProduk) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-1"><?= esc($prod['nama_produk']) ?></div>
                                <?php if (!empty($prod['spesifikasi'])) : ?>
                                    <small class="text-muted d-block text-truncate" style="max-width: 280px;" title="<?= esc(strip_tags($prod['spesifikasi'])) ?>">
                                        <?= esc(strip_tags($prod['spesifikasi'])) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-secondary small">
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                    <?= esc($prod['lokasi_barang'] ?: '-') ?>
                                </div>
                            </td>
                            <td>
                                <div class="small text-dark">
                                    <strong>Kapasitas:</strong> <?= esc($prod['kapasitas_produksi'] ?: '-') ?>
                                </div>
                                <div class="small text-muted">
                                    <strong>MOQ:</strong> <?= esc($prod['moq'] ?: '-') ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if (strtolower(trim((string)$prod['sudah_ekspor'])) === 'sudah') : ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="fa-solid fa-check me-1"></i> Sudah Ekspor
                                    </span>
                                <?php else : ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                        Belum Ekspor
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= base_url('supplier/produk/edit/' . $id) ?>" 
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
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-box-open fs-2 mb-2 d-block text-secondary opacity-50"></i>
                            <?php if (!empty($search_query)) : ?>
                                Tidak ditemukan produk dengan kata kunci "<strong><?= esc($search_query) ?></strong>".
                                <div class="mt-2">
                                    <a href="<?= base_url('supplier/produk') ?>" class="btn btn-sm btn-outline-secondary">Reset Pencarian</a>
                                </div>
                            <?php else : ?>
                                Belum ada produk yang terdaftar untuk perusahaan Anda.
                                <div class="mt-3">
                                    <a href="<?= base_url('supplier/produk/tambah') ?>" class="btn-sales-primary">
                                        <i class="fa-solid fa-plus"></i>
                                        <span>Tambah Produk Pertama</span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pager / Pagination -->
    <?php if (isset($pager) && $pager->getPageCount() > 1) : ?>
        <div class="p-3 border-top bg-light">
            <?= $pager->links('default', 'pager_rapi') ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Konfirmasi Hapus untuk setiap produk -->
<?php if (!empty($products)) : ?>
    <?php foreach ($products as $prod) : ?>
        <?php 
            $id         = (int)$prod['id_produk_supplier'];
            $kodeProduk = \App\Models\SupplierProductModel::getKodeProduk($id);
        ?>
        <div class="modal fade" id="deleteModal<?= $id ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $id ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title text-danger fw-bold" id="deleteModalLabel<?= $id ?>">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>Konfirmasi Hapus Produk
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-3">
                        <p class="mb-2 text-dark">Apakah Anda yakin ingin menghapus produk ini dari katalog perusahaan Anda?</p>
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="mb-1"><strong>Kode:</strong> <span class="product-code-badge"><?= esc($kodeProduk) ?></span></div>
                            <div><strong>Nama Produk:</strong> <?= esc($prod['nama_produk']) ?></div>
                        </div>
                        <small class="text-danger mt-2 d-block">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> Tindakan ini permanen. File foto produk di server juga akan dihapus.
                        </small>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <form action="<?= base_url('supplier/produk/delete/' . $id) ?>" method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger btn-sm text-white">
                                <i class="fa-solid fa-trash me-1"></i> Ya, Hapus Produk
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
