<?= $this->extend('sales/layout') ?>

<?= $this->section('content') ?>

<!-- Welcome Greeting -->
<div class="mb-4">
    <h2 class="fw-bold mb-1" style="font-size: 22px; color: #1e293b;">
        Selamat datang, <?= esc($username); ?> 👋
    </h2>
    <p class="text-muted mb-0" style="font-size: 14px;">
        Berikut adalah ringkasan inventaris produk dan aktivitas terbaru di katalog Indonesian Exporter.
    </p>
</div>

<!-- 3 Statistics Cards -->
<div class="row g-3 mb-4">
    <!-- Total Produk -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="sales-card h-100 d-flex align-items-center justify-content-between p-3 p-md-4">
            <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 12px; letter-spacing: 0.5px;">Total Produk</span>
                <h3 class="fw-bold mt-2 mb-0" style="color: #0f172a; font-size: 28px;"><?= number_format($totalProduk, 0, ',', '.'); ?></h3>
                <small class="text-muted" style="font-size: 12px;">Produk terdaftar di sistem</small>
            </div>
            <div style="width: 52px; height: 52px; border-radius: 10px; background-color: #dcfce7; color: #16a34a;" class="d-flex align-items-center justify-content-center fs-4">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>
    </div>

    <!-- Total Kategori -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="sales-card h-100 d-flex align-items-center justify-content-between p-3 p-md-4">
            <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 12px; letter-spacing: 0.5px;">Total Kategori</span>
                <h3 class="fw-bold mt-2 mb-0" style="color: #0f172a; font-size: 28px;"><?= number_format($totalKategori, 0, ',', '.'); ?></h3>
                <small class="text-muted" style="font-size: 12px;">Kategori produk aktif</small>
            </div>
            <div style="width: 52px; height: 52px; border-radius: 10px; background-color: #e0f2fe; color: #0284c7;" class="d-flex align-items-center justify-content-center fs-4">
                <i class="fa-solid fa-tags"></i>
            </div>
        </div>
    </div>

    <!-- Produk Terbaru (30 Hari Terakhir) -->
    <div class="col-12 col-sm-12 col-xl-4">
        <div class="sales-card h-100 d-flex align-items-center justify-content-between p-3 p-md-4">
            <div>
                <span class="text-muted text-uppercase fw-semibold" style="font-size: 12px; letter-spacing: 0.5px;">Produk Baru (30 Hari)</span>
                <h3 class="fw-bold mt-2 mb-0" style="color: #0f172a; font-size: 28px;"><?= number_format($produkBaru30Hari, 0, ',', '.'); ?></h3>
                <small class="text-muted" style="font-size: 12px;">Ditambahkan dalam 30 hari terakhir</small>
            </div>
            <div style="width: 52px; height: 52px; border-radius: 10px; background-color: #fef3c7; color: #d97706;" class="d-flex align-items-center justify-content-center fs-4">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>
    </div>
</div>

<!-- Latest Products Table Card -->
<div class="sales-card p-0 overflow-hidden">
    <div class="d-flex flex-wrap align-items-center justify-content-between p-3 p-md-4 border-bottom">
        <div>
            <h5 class="fw-bold mb-1" style="font-size: 16px; color: #0f172a;">
                <i class="fa-solid fa-fire text-warning me-2"></i>Produk Terbaru
            </h5>
            <small class="text-muted">5 produk yang paling baru ditambahkan ke katalog</small>
        </div>
        <a href="<?= base_url('sales/produk'); ?>" class="btn-sales-secondary btn-sm mt-2 mt-sm-0">
            <span>Kelola Semua Produk</span>
            <i class="fa-solid fa-arrow-right fs-xs"></i>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead class="table-light" style="font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                <tr>
                    <th scope="col" style="padding: 14px 20px; width: 70px;">Gambar</th>
                    <th scope="col" style="padding: 14px 16px;">Nama Produk</th>
                    <th scope="col" style="padding: 14px 16px;">Kategori</th>
                    <th scope="col" style="padding: 14px 16px;">Tanggal Dibuat</th>
                    <th scope="col" class="text-end" style="padding: 14px 20px; width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($latestProducts)) : ?>
                    <?php foreach ($latestProducts as $idx => $prod) : ?>
                        <tr>
                            <td style="padding: 12px 20px;">
                                <div style="width: 46px; height: 46px; border-radius: 6px; overflow: hidden; background-color: #f1f5f9; border: 1px solid #e2e8f0;" class="d-flex align-items-center justify-content-center">
                                    <?php if (!empty($prod['foto_produk'])) : ?>
                                        <img src="<?= base_url('assets/img/produk/' . esc($prod['foto_produk'])); ?>"
                                            alt="<?= esc($prod['nama_produk_id']); ?>"
                                            style="width: 100%; height: 100%; object-fit: cover;"
                                            onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Img';">
                                    <?php else : ?>
                                        <i class="fa-solid fa-image text-muted"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div class="fw-semibold text-dark"><?= esc($prod['nama_produk_id']); ?></div>
                                <small class="text-muted fst-italic"><?= esc($prod['nama_produk_en']); ?></small>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background-color: #f1f5f9; color: #475569; font-weight: 500; font-size: 12px; border: 1px solid #e2e8f0; padding: 5px 10px;">
                                    <?= esc($prod['nama_kategori_id'] ?? 'Tanpa Kategori'); ?>
                                </span>
                            </td>
                            <td style="padding: 12px 16px; color: #64748b; font-size: 13px;">
                                <?= !empty($prod['created_at']) ? date('d M Y, H:i', strtotime($prod['created_at'])) : '-'; ?>
                            </td>
                            <td class="text-end" style="padding: 12px 20px;">
                                <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                                    data-bs-toggle="modal" data-bs-target="#modalLihat<?= $prod['id_produk']; ?>" style="border-radius: 6px; font-size: 12.5px;">
                                    <i class="fa-solid fa-eye"></i>
                                    <span>Lihat</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Detail Produk -->
                        <div class="modal fade" id="modalLihat<?= $prod['id_produk']; ?>" tabindex="-1" aria-labelledby="modalLabel<?= $prod['id_produk']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-bottom pb-3">
                                        <h5 class="modal-title fw-bold" id="modalLabel<?= $prod['id_produk']; ?>" style="font-size: 16px;">
                                            Detail Produk #<?= $prod['id_produk']; ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-4">
                                            <div class="col-md-5 text-center">
                                                <div class="rounded border p-2 bg-light">
                                                    <img src="<?= base_url('assets/img/produk/' . esc($prod['foto_produk'])); ?>"
                                                        alt="<?= esc($prod['nama_produk_id']); ?>"
                                                        class="img-fluid rounded" style="max-height: 260px; object-fit: contain;"
                                                        onerror="this.onerror=null; this.src='https://placehold.co/300x300?text=No+Image';">
                                                </div>
                                                <div class="mt-2 text-muted small">
                                                    File: <code><?= esc($prod['foto_produk']); ?></code>
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="mb-3">
                                                    <label class="text-muted small fw-semibold text-uppercase">Nama Produk (ID)</label>
                                                    <div class="fw-bold fs-6"><?= esc($prod['nama_produk_id']); ?></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small fw-semibold text-uppercase">Nama Produk (EN)</label>
                                                    <div class="fw-semibold text-secondary"><?= esc($prod['nama_produk_en']); ?></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small fw-semibold text-uppercase">Kategori</label>
                                                    <div>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                            <?= esc($prod['nama_kategori_id'] ?? '-'); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small fw-semibold text-uppercase">Deskripsi (ID)</label>
                                                    <div class="border rounded p-2 bg-light small" style="max-height: 120px; overflow-y: auto;">
                                                        <?= $prod['deskripsi_produk_id'] ?? '-'; ?>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="text-muted small fw-semibold text-uppercase">Slug</label>
                                                    <div><code><?= esc($prod['slug_id'] ?? '-'); ?></code></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top bg-light">
                                        <a href="<?= base_url('sales/produk/edit/' . $prod['id_produk']); ?>" class="btn btn-sm btn-sales-primary">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                            <span>Edit Produk Ini</span>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-box-open fs-2 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada produk yang ditambahkan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
