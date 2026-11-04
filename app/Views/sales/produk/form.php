<?= $this->extend('sales/layout') ?>

<?= $this->section('content') ?>

<?php
$isEdit = !empty($product);
?>

<div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size: 13px;">
                <li class="breadcrumb-item"><a href="<?= base_url('sales/produk-supplier'); ?>" class="text-decoration-none text-muted">Produk Supplier</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'Edit Produk' : 'Tambah Produk'; ?></li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1" style="font-size: 22px; color: #1e293b;">
            <?= $isEdit ? 'Edit Data Produk' : 'Tambah Produk Baru'; ?>
        </h2>
        <p class="text-muted mb-0" style="font-size: 14px;">
            <?= $isEdit ? 'Perbarui informasi dan spesifikasi produk katalog.' : 'Isi formulir berikut untuk mendaftarkan produk baru ke katalog.'; ?>
        </p>
    </div>
    <a href="<?= base_url('sales/produk-supplier'); ?>" class="btn-sales-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Kembali ke Daftar</span>
    </a>
</div>

<form action="<?= $action; ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field(); ?>

    <div class="row g-4">
        <!-- Kolom Kiri: Informasi Utama & Deskripsi -->
        <div class="col-12 col-lg-8">
            <div class="sales-card p-4">
                <h5 class="fw-bold mb-3" style="font-size: 16px; color: #0f172a;">
                    <i class="fa-solid fa-circle-info text-success me-2"></i>Informasi Utama Produk
                </h5>

                <!-- Nama Produk ID & EN -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="nama_produk_id" class="form-label fw-semibold" style="font-size: 13.5px;">
                            Nama Produk (Indonesia) <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control <?= ($validation->hasError('nama_produk_id')) ? 'is-invalid' : ''; ?>"
                            id="nama_produk_id"
                            name="nama_produk_id"
                            placeholder="Contoh: Kayu Manis Super"
                            value="<?= esc(old('nama_produk_id', $product['nama_produk_id'] ?? '')); ?>"
                            required>
                        <div class="invalid-feedback">
                            <?= $validation->getError('nama_produk_id'); ?>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="nama_produk_en" class="form-label fw-semibold" style="font-size: 13.5px;">
                            Nama Produk (Inggris) <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control <?= ($validation->hasError('nama_produk_en')) ? 'is-invalid' : ''; ?>"
                            id="nama_produk_en"
                            name="nama_produk_en"
                            placeholder="Contoh: Super Cinnamon Bark"
                            value="<?= esc(old('nama_produk_en', $product['nama_produk_en'] ?? '')); ?>"
                            required>
                        <div class="invalid-feedback">
                            <?= $validation->getError('nama_produk_en'); ?>
                        </div>
                    </div>
                </div>

                <!-- Kategori Produk -->
                <div class="mb-3">
                    <label for="id_kategori_produk" class="form-label fw-semibold" style="font-size: 13.5px;">
                        Kategori Produk <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?= ($validation->hasError('id_kategori_produk')) ? 'is-invalid' : ''; ?>"
                        id="id_kategori_produk"
                        name="id_kategori_produk"
                        required>
                        <option value="" disabled <?= empty(old('id_kategori_produk', $product['id_kategori_produk'] ?? '')) ? 'selected' : ''; ?>>
                            -- Pilih Kategori Produk --
                        </option>
                        <?php if (!empty($categories)) : ?>
                            <?php foreach ($categories as $cat) : ?>
                                <option value="<?= esc($cat['id_kategori_produk']); ?>"
                                    <?= ((string)old('id_kategori_produk', $product['id_kategori_produk'] ?? '') === (string)$cat['id_kategori_produk']) ? 'selected' : ''; ?>>
                                    <?= esc($cat['nama_kategori_id']); ?> (<?= esc($cat['nama_kategori_en']); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="invalid-feedback">
                        <?= $validation->getError('id_kategori_produk'); ?>
                    </div>
                </div>

                <!-- Deskripsi Produk (ID) -->
                <div class="mb-3">
                    <label for="deskripsi_produk_id" class="form-label fw-semibold" style="font-size: 13.5px;">
                        Deskripsi Produk (Indonesia) <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control <?= ($validation->hasError('deskripsi_produk_id')) ? 'is-invalid' : ''; ?>"
                        id="deskripsi_produk_id"
                        name="deskripsi_produk_id"
                        rows="5"
                        placeholder="Deskripsikan keunggulan, spesifikasi, dan detail produk dalam Bahasa Indonesia..."
                        required><?= esc(old('deskripsi_produk_id', $product['deskripsi_produk_id'] ?? '')); ?></textarea>
                    <div class="invalid-feedback">
                        <?= $validation->getError('deskripsi_produk_id'); ?>
                    </div>
                </div>

                <!-- Deskripsi Produk (EN) -->
                <div class="mb-3">
                    <label for="deskripsi_produk_en" class="form-label fw-semibold" style="font-size: 13.5px;">
                        Deskripsi Produk (Inggris) <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control <?= ($validation->hasError('deskripsi_produk_en')) ? 'is-invalid' : ''; ?>"
                        id="deskripsi_produk_en"
                        name="deskripsi_produk_en"
                        rows="5"
                        placeholder="Describe the product specifications, origin, and export grade in English..."
                        required><?= esc(old('deskripsi_produk_en', $product['deskripsi_produk_en'] ?? '')); ?></textarea>
                    <div class="invalid-feedback">
                        <?= $validation->getError('deskripsi_produk_en'); ?>
                    </div>
                </div>
            </div>

            <!-- SEO & URL Accordion -->
            <div class="sales-card p-4">
                <div class="d-flex align-items-center justify-content-between cursor-pointer" data-bs-toggle="collapse" data-bs-target="#seoCollapse" aria-expanded="false" style="cursor: pointer;">
                    <div>
                        <h5 class="fw-bold mb-1" style="font-size: 16px; color: #0f172a;">
                            <i class="fa-solid fa-magnifying-glass-chart text-primary me-2"></i>Pengaturan SEO & URL Slug (Opsional)
                        </h5>
                        <small class="text-muted">Bisa dikosongkan. Sistem akan membuat slug dan meta tag secara otomatis.</small>
                    </div>
                    <i class="fa-solid fa-chevron-down text-muted"></i>
                </div>

                <div class="collapse mt-3" id="seoCollapse">
                    <hr class="my-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="slug_id" class="form-label fw-semibold small">Slug URL (ID)</label>
                            <input type="text" class="form-control form-control-sm" id="slug_id" name="slug_id"
                                placeholder="otomatis jika kosong"
                                value="<?= esc(old('slug_id', $product['slug_id'] ?? '')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="slug_en" class="form-label fw-semibold small">Slug URL (EN)</label>
                            <input type="text" class="form-control form-control-sm" id="slug_en" name="slug_en"
                                placeholder="otomatis jika kosong"
                                value="<?= esc(old('slug_en', $product['slug_en'] ?? '')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="alt_produk_id" class="form-label fw-semibold small">ALT Gambar (ID)</label>
                            <input type="text" class="form-control form-control-sm" id="alt_produk_id" name="alt_produk_id"
                                placeholder="Teks alternatif foto"
                                value="<?= esc(old('alt_produk_id', $product['alt_produk_id'] ?? '')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="alt_produk_en" class="form-label fw-semibold small">ALT Gambar (EN)</label>
                            <input type="text" class="form-control form-control-sm" id="alt_produk_en" name="alt_produk_en"
                                placeholder="Image alt text"
                                value="<?= esc(old('alt_produk_en', $product['alt_produk_en'] ?? '')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="title_id" class="form-label fw-semibold small">Meta Title (ID)</label>
                            <input type="text" class="form-control form-control-sm" id="title_id" name="title_id" maxlength="59"
                                placeholder="Maksimal 59 karakter"
                                value="<?= esc(old('title_id', $product['title_id'] ?? '')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="title_en" class="form-label fw-semibold small">Meta Title (EN)</label>
                            <input type="text" class="form-control form-control-sm" id="title_en" name="title_en" maxlength="59"
                                placeholder="Max 59 characters"
                                value="<?= esc(old('title_en', $product['title_en'] ?? '')); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="meta_desc_id" class="form-label fw-semibold small">Meta Description (ID)</label>
                            <textarea class="form-control form-control-sm" id="meta_desc_id" name="meta_desc_id" rows="2" maxlength="159"
                                placeholder="Maksimal 159 karakter"><?= esc(old('meta_desc_id', $product['meta_desc_id'] ?? '')); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="meta_desc_en" class="form-label fw-semibold small">Meta Description (EN)</label>
                            <textarea class="form-control form-control-sm" id="meta_desc_en" name="meta_desc_en" rows="2" maxlength="159"
                                placeholder="Max 159 characters"><?= esc(old('meta_desc_en', $product['meta_desc_en'] ?? '')); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Upload Foto Produk & Tombol Simpan -->
        <div class="col-12 col-lg-4">
            <div class="sales-card p-4">
                <h5 class="fw-bold mb-3" style="font-size: 16px; color: #0f172a;">
                    <i class="fa-solid fa-image text-success me-2"></i>Foto Produk <?= !$isEdit ? '<span class="text-danger">*</span>' : ''; ?>
                </h5>

                <div class="mb-3">
                    <label for="foto_produk" class="form-label small text-muted">
                        Pilih file gambar (JPG, JPEG, PNG, WEBP). Maks. 2MB.
                    </label>
                    <input class="form-control <?= ($validation->hasError('foto_produk')) ? 'is-invalid' : ''; ?>"
                        type="file"
                        id="foto_produk"
                        name="foto_produk"
                        accept=".jpg,.jpeg,.png,.webp"
                        <?= !$isEdit ? 'required' : ''; ?>>
                    <div class="invalid-feedback">
                        <?= $validation->getError('foto_produk'); ?>
                    </div>
                </div>

                <!-- Preview Gambar -->
                <div class="mb-3 text-center">
                    <label class="form-label small text-muted d-block text-start">Preview Foto:</label>
                    <div class="border rounded p-2 bg-light d-flex align-items-center justify-content-center" style="min-height: 180px; max-height: 240px; overflow: hidden;">
                        <?php if ($isEdit && !empty($product['foto_produk'])) : ?>
                            <img id="imagePreview"
                                src="<?= base_url('assets/img/produk/' . esc($product['foto_produk'])); ?>"
                                alt="Preview Foto Produk"
                                class="img-fluid rounded"
                                style="max-height: 220px; object-fit: contain;"
                                onerror="this.onerror=null; this.src='https://placehold.co/300x200?text=Preview+Gambar';">
                        <?php else : ?>
                            <img id="imagePreview"
                                src="https://placehold.co/300x200?text=Preview+Gambar"
                                alt="Preview Foto Produk"
                                class="img-fluid rounded"
                                style="max-height: 220px; object-fit: contain;">
                        <?php endif; ?>
                    </div>
                    <?php if ($isEdit && !empty($product['foto_produk'])) : ?>
                        <small class="text-muted d-block mt-2" style="font-size: 11.5px;">
                            File saat ini: <code><?= esc($product['foto_produk']); ?></code>. Biarkan kosong jika tidak ingin mengubah.
                        </small>
                    <?php endif; ?>
                </div>

                <hr class="my-4">

                <!-- Submit / Cancel -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-sales-primary justify-content-center py-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span><?= $isEdit ? 'Perbarui Produk' : 'Simpan Produk Baru'; ?></span>
                    </button>
                    <a href="<?= base_url('sales/produk-supplier'); ?>" class="btn btn-sales-secondary justify-content-center py-2">
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('foto_produk');
        const imgPreview = document.getElementById('imagePreview');

        if (fileInput && imgPreview) {
            fileInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    // Cek ukuran file (maks 2MB = 2 * 1024 * 1024 bytes)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Peringatan: Ukuran file melebihi 2MB. Harap pilih gambar yang lebih kecil.');
                        this.value = '';
                        return;
                    }

                    // Cek tipe file
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Format file tidak didukung! Gunakan format JPG, JPEG, PNG, atau WEBP.');
                        this.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        imgPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>
