<?= $this->extend('supplier/layout') ?>

<?= $this->section('content') ?>

<style>
    .form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }
    .form-section-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 18px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-section-title i {
        color: var(--primary-green);
    }
    .form-label {
        font-size: 13.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }
    .form-control, .form-select {
        font-size: 14px;
        border-color: #cbd5e1;
        border-radius: 8px;
        padding: 8px 12px;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
    }
    .form-text {
        font-size: 12px;
        color: #64748b;
    }
</style>

<!-- Breadcrumb & Header -->
<div class="row g-3 mb-4 align-items-center justify-content-between">
    <div class="col-12 col-md-auto">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= base_url('supplier/produk') ?>" class="text-success text-decoration-none">Produk</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Produk</li>
            </ol>
        </nav>
        <h2 class="h4 fw-bold text-dark mb-0">Tambah Produk Baru</h2>
    </div>
    <div class="col-12 col-md-auto">
        <a href="<?= base_url('supplier/produk') ?>" class="btn-sales-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </div>
</div>

<!-- Validation Errors Box -->
<?php $validationErrors = session('errors') ?? (isset($validation) ? $validation->getErrors() : []); ?>
<?php if (!empty($validationErrors)) : ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>Mohon periksa kesalahan berikut:</div>
        <ul class="mb-0 ps-3">
            <?php foreach ($validationErrors as $err) : ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Form Card -->
<div class="form-card">
    <form action="<?= base_url('supplier/produk/proses_tambah') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="row g-4">
            <!-- Kolom Kiri: Informasi Pokok Produk -->
            <div class="col-12 col-lg-6">
                <h5 class="form-section-title">
                    <i class="fa-solid fa-box"></i> Informasi Utama Produk
                </h5>

                <!-- Nama Produk (Wajib) -->
                <div class="mb-3">
                    <label for="nama_produk" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="nama_produk" 
                           id="nama_produk" 
                           class="form-control <?= (isset($validation) && $validation->hasError('nama_produk')) ? 'is-invalid' : '' ?>" 
                           value="<?= old('nama_produk') ?>" 
                           placeholder="Contoh: Arang Briket Batok Kelapa Premium" 
                           required>
                    <?php if (isset($validation) && $validation->hasError('nama_produk')) : ?>
                        <div class="invalid-feedback"><?= $validation->getError('nama_produk') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Lokasi Barang (Kosong = Domisili Supplier) -->
                <div class="mb-3">
                    <label for="lokasi_barang" class="form-label">Lokasi Barang</label>
                    <input type="text" 
                           name="lokasi_barang" 
                           id="lokasi_barang" 
                           class="form-control <?= (isset($validation) && $validation->hasError('lokasi_barang')) ? 'is-invalid' : '' ?>" 
                           value="<?= old('lokasi_barang') ?>" 
                           placeholder="<?= !empty($domisili_usaha) ? esc($domisili_usaha) : 'Contoh: Semarang, Jawa Tengah' ?>">
                    <div class="form-text">
                        <?php if (!empty($domisili_usaha)) : ?>
                            Jika dikosongkan, otomatis menggunakan domisili perusahaan Anda: <strong><?= esc($domisili_usaha) ?></strong>.
                        <?php else : ?>
                            Lokasi gudang atau pabrik penyimpanan barang siap muat.
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status Pengalaman Ekspor -->
                <div class="mb-3">
                    <label for="sudah_ekspor" class="form-label">Status Pengalaman Ekspor</label>
                    <select name="sudah_ekspor" id="sudah_ekspor" class="form-select">
                        <option value="Belum" <?= (old('sudah_ekspor') === 'Belum') ? 'selected' : '' ?>>Belum Pernah Ekspor</option>
                        <option value="Sudah" <?= (old('sudah_ekspor') === 'Sudah') ? 'selected' : '' ?>>Sudah Pernah Ekspor</option>
                    </select>
                </div>

                <!-- Foto Produk -->
                <div class="mb-3">
                    <label for="foto_1" class="form-label">Foto Produk</label>
                    <input type="file" 
                           name="foto_1" 
                           id="foto_1" 
                           class="form-control <?= (isset($validation) && $validation->hasError('foto_1')) ? 'is-invalid' : '' ?>" 
                           accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">
                        Format yang didukung: JPG, JPEG, PNG, WEBP. Ukuran maksimal: 2 MB.
                    </div>
                    <?php if (isset($validation) && $validation->hasError('foto_1')) : ?>
                        <div class="invalid-feedback"><?= $validation->getError('foto_1') ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kolom Kanan: Spesifikasi & Komersial -->
            <div class="col-12 col-lg-6">
                <h5 class="form-section-title">
                    <i class="fa-solid fa-list-check"></i> Spesifikasi & Detail
                </h5>

                <!-- Spesifikasi Produk -->
                <div class="mb-3">
                    <label for="spesifikasi" class="form-label">Spesifikasi Produk</label>
                    <textarea name="spesifikasi" 
                              id="spesifikasi" 
                              rows="4" 
                              class="form-control" 
                              placeholder="Rincian spesifikasi teknis, mutu, ukuran, grade, kadar air, atau standar produk..."><?= old('spesifikasi') ?></textarea>
                </div>

                <!-- Kapasitas Produksi -->
                <div class="mb-3">
                    <label for="kapasitas_produksi" class="form-label">Kapasitas Produksi</label>
                    <input type="text" 
                           name="kapasitas_produksi" 
                           id="kapasitas_produksi" 
                           class="form-control" 
                           value="<?= old('kapasitas_produksi') ?>" 
                           placeholder="Contoh: 100 Ton / Bulan atau 5.000 Pcs / Bulan">
                </div>

                <!-- MOQ & Harga EXW (2 kolom) -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="moq" class="form-label">MOQ (Min. Order)</label>
                        <input type="text" 
                               name="moq" 
                               id="moq" 
                               class="form-control" 
                               value="<?= old('moq') ?>" 
                               placeholder="Contoh: 1 Kontainer 20ft">
                    </div>
                    <div class="col-6">
                        <label for="harga_exw" class="form-label">Harga EXW</label>
                        <input type="text" 
                               name="harga_exw" 
                               id="harga_exw" 
                               class="form-control" 
                               value="<?= old('harga_exw') ?>" 
                               placeholder="Contoh: USD 1,200 / MT">
                    </div>
                </div>

                <!-- Legalitas & Sertifikasi (2 kolom) -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="legalitas" class="form-label">Legalitas Produk</label>
                        <input type="text" 
                               name="legalitas" 
                               id="legalitas" 
                               class="form-control" 
                               value="<?= old('legalitas') ?>" 
                               placeholder="Contoh: NIB, NPWP">
                    </div>
                    <div class="col-6">
                        <label for="sertifikasi" class="form-label">Sertifikasi</label>
                        <input type="text" 
                               name="sertifikasi" 
                               id="sertifikasi" 
                               class="form-control" 
                               value="<?= old('sertifikasi') ?>" 
                               placeholder="Contoh: Halal, BPOM, COA">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="border-top pt-3 mt-4 d-flex justify-content-end gap-2">
            <a href="<?= base_url('supplier/produk') ?>" class="btn-sales-secondary">
                Batal
            </a>
            <button type="submit" class="btn-sales-primary">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Produk</span>
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
