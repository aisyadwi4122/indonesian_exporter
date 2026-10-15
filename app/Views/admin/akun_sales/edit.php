<?= $this->extend('admin/template/template'); ?>
<?= $this->section('content'); ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-auto">
                <h1 class="app-page-title mb-0">Edit Akun Sales</h1>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('admin/akun_sales/index') ?>" class="btn btn-secondary text-white">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row g-4 settings-section">
            <div class="col-12 col-md-8">
                <div class="app-card app-card-settings shadow-sm p-4">
                    <div class="app-card-body">
                        <?php if (session()->getFlashdata('error')) : ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="<?= base_url('admin/akun_sales/proses_edit/' . esc($akun['id_sales'])) ?>" method="POST" autocomplete="off">
                            <?= csrf_field(); ?>

                            <div class="mb-3">
                                <label for="nama_sales" class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nama_sales" 
                                       name="nama_sales" 
                                       maxlength="100" 
                                       placeholder="Contoh: Budi Santoso"
                                       value="<?= esc(old('nama_sales', $akun['nama_sales'])) ?>" 
                                       required>
                                <div class="form-text">Maksimal 100 karakter.</div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       minlength="3" 
                                       maxlength="100" 
                                       placeholder="Contoh: budi_sales"
                                       value="<?= esc(old('username', $akun['username'])) ?>" 
                                       required>
                                <div class="form-text">3 - 100 karakter, tanpa spasi (hanya huruf, angka, titik, underscore, strip).</div>
                            </div>

                            <div class="border rounded p-3 mb-3 bg-light">
                                <h6 class="fw-bold mb-2 text-primary">
                                    <i class="fa-solid fa-key me-1"></i> Ganti Password (Opsional)
                                </h6>
                                <p class="text-muted small mb-3">Kosongkan kedua kolom di bawah jika tidak ingin mengubah password akun.</p>

                                <div class="row">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label for="password" class="form-label fw-bold">Password Baru</label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="password" 
                                               name="password" 
                                               minlength="8" 
                                               placeholder="Minimal 8 karakter jika diisi">
                                        <div class="form-text">Minimal 8 karakter jika diisi.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="konfirmasi_password" class="form-label fw-bold">Konfirmasi Password Baru</label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="konfirmasi_password" 
                                               name="konfirmasi_password" 
                                               minlength="8" 
                                               placeholder="Ulangi password baru">
                                        <div class="form-text">Wajib diisi jika password baru diisi.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="status" class="form-label fw-bold">Status Akun <span class="text-danger">*</span></label>
                                <?php $currentStatus = old('status', $akun['status']); ?>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="aktif" <?= $currentStatus === 'aktif' ? 'selected' : '' ?>>Aktif (Dapat Login)</option>
                                    <option value="nonaktif" <?= $currentStatus === 'nonaktif' ? 'selected' : '' ?>>Nonaktif (Tidak Dapat Login)</option>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary text-white">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                                </button>
                                <a href="<?= base_url('admin/akun_sales/index') ?>" class="btn btn-outline-secondary">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div><!--//app-card-body-->
                </div><!--//app-card-->
            </div>
        </div><!--//row-->
    </div><!--//container-xl-->
</div><!--//app-content-->

<?= $this->endSection(); ?>
