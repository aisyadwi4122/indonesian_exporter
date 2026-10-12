<?= $this->extend('sales/layout') ?>

<?= $this->section('content') ?>

<style>
    /* Pagination Styling matching theme */
    .pagination .page-link {
        color: #374151;
        border-color: #e5e7eb;
        font-size: 13.5px;
        padding: 6px 12px;
    }
    .pagination .page-link:hover {
        background-color: #f1f5f9;
        color: #16a34a;
        border-color: #cbd5e1;
    }
    .pagination .page-item.active .page-link {
        background-color: #16a34a;
        border-color: #16a34a;
        color: #ffffff;
    }
    .pagination .page-item.disabled .page-link {
        color: #9ca3af;
        border-color: #e5e7eb;
    }
</style>

<!-- Header & Add Button -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size: 22px; color: #1e293b;">Kelola Produk</h2>
        <p class="text-muted mb-0" style="font-size: 14px;">
            Daftar semua produk di katalog Indonesian Exporter. Tambah, perbarui, atau hapus data produk.
        </p>
    </div>
    <a href="<?= base_url('sales/produk/tambah'); ?>" class="btn-sales-primary">
        <i class="fa-solid fa-plus"></i>
        <span>Tambah Produk</span>
    </a>
</div>

<!-- Filter & Search Toolbar Card -->
<div class="sales-card p-3 mb-4">
    <form action="<?= base_url('sales/produk'); ?>" method="GET" class="row g-2 align-items-center">
        <!-- Search Input -->
        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted" style="border-color: #e5e7eb;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="keyword" class="form-control border-start-0 ps-0"
                    placeholder="Cari nama produk (Indonesia / Inggris)..."
                    value="<?= esc($keyword ?? ''); ?>" style="border-color: #e5e7eb; font-size: 13.5px;">
            </div>
        </div>

        <!-- Category Dropdown Filter -->
        <div class="col-12 col-md-4">
            <select name="kategori" class="form-select" style="border-color: #e5e7eb; font-size: 13.5px;">
                <option value="">Semua Kategori</option>
                <?php if (!empty($categories)) : ?>
                    <?php foreach ($categories as $cat) : ?>
                        <option value="<?= esc($cat['id_kategori_produk']); ?>"
                            <?= (isset($kategori) && (string)$kategori === (string)$cat['id_kategori_produk']) ? 'selected' : ''; ?>>
                            <?= esc($cat['nama_kategori_id']); ?> (<?= esc($cat['nama_kategori_en']); ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <!-- Action Buttons -->
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-sales-primary flex-grow-1 justify-content-center">
                <i class="fa-solid fa-filter"></i>
                <span>Terapkan</span>
            </button>
            <?php if (!empty($keyword) || !empty($kategori)) : ?>
                <a href="<?= base_url('sales/produk'); ?>" class="btn btn-sm btn-sales-secondary" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Reset</span>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Products Table Card -->
<div class="sales-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead class="table-light" style="font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                <tr>
                    <th scope="col" style="padding: 14px 16px; width: 60px;" class="text-center">No</th>
                    <th scope="col" style="padding: 14px 16px; width: 80px;">Gambar</th>
                    <th scope="col" style="padding: 14px 16px;">Nama Produk</th>
                    <th scope="col" style="padding: 14px 16px; width: 200px;">Kategori</th>
                    <th scope="col" class="text-end" style="padding: 14px 20px; width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)) : ?>
                    <?php foreach ($products as $idx => $item) : ?>
                        <?php $rowNumber = (($currentPage - 1) * $perPage) + $idx + 1; ?>
                        <tr>
                            <!-- No -->
                            <td class="text-center fw-semibold text-muted" style="padding: 12px 16px;">
                                <?= $rowNumber; ?>
                            </td>

                            <!-- Gambar -->
                            <td style="padding: 12px 16px;">
                                <div style="width: 50px; height: 50px; border-radius: 6px; overflow: hidden; background-color: #f1f5f9; border: 1px solid #e2e8f0;" class="d-flex align-items-center justify-content-center">
                                    <?php if (!empty($item['foto_produk'])) : ?>
                                        <img src="<?= base_url('assets/img/produk/' . esc($item['foto_produk'])); ?>"
                                            alt="<?= esc($item['nama_produk_id']); ?>"
                                            style="width: 100%; height: 100%; object-fit: cover;"
                                            onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Img';">
                                    <?php else : ?>
                                        <i class="fa-solid fa-image text-muted"></i>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Nama -->
                            <td style="padding: 12px 16px;">
                                <div class="fw-semibold text-dark"><?= esc($item['nama_produk_id']); ?></div>
                                <small class="text-muted fst-italic d-block"><?= esc($item['nama_produk_en']); ?></small>
                                <small class="text-muted d-block" style="font-size: 11px;">
                                    Slug: <code><?= esc($item['slug_id']); ?></code>
                                </small>
                            </td>

                            <!-- Kategori -->
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background-color: #f1f5f9; color: #334155; font-weight: 500; font-size: 12px; border: 1px solid #e2e8f0; padding: 6px 10px;">
                                    <?= esc($item['nama_kategori_id'] ?? 'Tanpa Kategori'); ?>
                                </span>
                            </td>

                            <!-- Aksi (Edit, Hapus) -->
                            <td class="text-end" style="padding: 12px 20px;">
                                <div class="d-inline-flex gap-1">
                                    <a href="<?= base_url('sales/produk/edit/' . $item['id_produk']); ?>"
                                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px; border-radius: 6px;" title="Edit Produk">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px; border-radius: 6px;"
                                        title="Hapus Produk"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalHapus<?= $item['id_produk']; ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Konfirmasi Hapus -->
                        <div class="modal fade" id="modalHapus<?= $item['id_produk']; ?>" tabindex="-1" aria-labelledby="labelHapus<?= $item['id_produk']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-body p-4 text-center">
                                        <div style="width: 54px; height: 54px; border-radius: 50%; background-color: #fee2e2; color: #dc2626;" class="d-flex align-items-center justify-content-center mx-auto mb-3 fs-4">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2" id="labelHapus<?= $item['id_produk']; ?>" style="font-size: 17px; color: #0f172a;">
                                            Hapus Produk Ini?
                                        </h5>
                                        <p class="text-muted mb-3" style="font-size: 13.5px;">
                                            Anda akan menghapus produk <strong>"<?= esc($item['nama_produk_id']); ?>"</strong>.
                                            Data produk serta file gambar di server akan dihapus secara permanen.
                                        </p>

                                        <form action="<?= base_url('sales/produk/hapus/' . $item['id_produk']); ?>" method="POST" class="d-flex justify-content-center gap-2 mt-3">
                                            <?= csrf_field(); ?>
                                            <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal" style="border-radius: 6px;">
                                                Batal
                                            </button>
                                            <button type="submit" class="btn btn-sm btn-danger px-3" style="border-radius: 6px;">
                                                <i class="fa-solid fa-trash me-1"></i> Ya, Hapus Produk
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-box-open fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <div class="fw-semibold">Tidak ada produk ditemukan</div>
                            <small class="text-muted">Coba ubah kata kunci pencarian atau reset filter kategori.</small>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pager / Pagination Footer -->
    <?php if ($pager) : ?>
        <div class="p-3 border-top d-flex justify-content-center">
            <?= $pager->links('produk', 'default_full') ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
