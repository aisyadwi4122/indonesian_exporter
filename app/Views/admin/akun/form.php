<?= $this->extend('admin/template/template'); ?>
<?= $this->section('content'); ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-auto">
                <h1 class="app-page-title mb-0"><?= esc($title) ?></h1>
                <p class="text-muted small mb-0">
                    <?= $is_edit ? 'Perbarui informasi dan hak akses akun.' : 'Tambahkan akun pengguna Sales atau Supplier baru.' ?>
                </p>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('admin/akun/index') ?>" class="btn btn-secondary text-white">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <div class="row g-4 settings-section">
            <div class="col-12 col-md-9 col-lg-8">
                <div class="app-card app-card-settings shadow-sm p-4">
                    <div class="app-card-body">
                        <?php if (session()->getFlashdata('error')) : ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php 
                            $formAction = $is_edit 
                                ? base_url('admin/akun/proses_edit/' . esc($account['tipe']) . '/' . esc($account['id'])) 
                                : base_url('admin/akun/proses_tambah');
                        ?>
                        <form action="<?= $formAction ?>" method="POST" autocomplete="off" id="akunForm">
                            <?= csrf_field(); ?>

                            <!-- TIPE AKUN -->
                            <div class="mb-3">
                                <label class="form-label fw-bold d-block">Tipe Akun <span class="text-danger">*</span></label>
                                <?php if ($is_edit) : ?>
                                    <div class="py-1">
                                        <?php if ($account['tipe'] === 'sales') : ?>
                                            <span class="badge bg-success fs-6 px-3 py-2">
                                                <i class="fa-solid fa-user-tie me-1"></i> Sales
                                            </span>
                                        <?php else : ?>
                                            <span class="badge bg-primary fs-6 px-3 py-2">
                                                <i class="fa-solid fa-truck-field me-1"></i> Supplier
                                            </span>
                                        <?php endif; ?>
                                        <small class="text-muted ms-2">(Tipe akun tidak dapat diubah)</small>
                                    </div>
                                <?php else : ?>
                                    <?php 
                                        $selectedTipe = old('tipe', $default_tipe ?? 'sales'); 
                                    ?>
                                    <div class="d-flex gap-4 pt-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tipe" id="tipe_sales" value="sales" <?= $selectedTipe === 'sales' ? 'checked' : '' ?> onchange="toggleSupplierField()">
                                            <label class="form-check-label fw-bold text-success" for="tipe_sales">
                                                <i class="fa-solid fa-user-tie me-1"></i> Sales
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tipe" id="tipe_supplier" value="supplier" <?= $selectedTipe === 'supplier' ? 'checked' : '' ?> onchange="toggleSupplierField()">
                                            <label class="form-check-label fw-bold text-primary" for="tipe_supplier">
                                                <i class="fa-solid fa-truck-field me-1"></i> Supplier
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-text">Pilih apakah akun diperuntukkan bagi tim Sales internal atau mitra Supplier.</div>
                                <?php endif; ?>
                            </div>

                            <!-- PERUSAHAAN SUPPLIER (Hanya jika tipe Supplier) -->
                            <?php 
                                $showSupplierSection = $is_edit ? ($account['tipe'] === 'supplier') : ($selectedTipe === 'supplier');
                            ?>
                            <div class="mb-3" id="supplier_wrapper" style="<?= $showSupplierSection ? '' : 'display: none;' ?>">
                                <label for="id_supplier" class="form-label fw-bold">
                                    Perusahaan Supplier <span class="text-danger">*</span>
                                </label>
                                <?php if (empty($suppliers)) : ?>
                                    <div class="alert alert-warning py-2 mb-1 small">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Belum ada data perusahaan supplier.
                                    </div>
                                    <select class="form-select" id="id_supplier" name="id_supplier" disabled>
                                        <option value="">-- Belum ada perusahaan supplier --</option>
                                    </select>
                                <?php else : ?>
                                    <?php 
                                        $selectedSupplier = old('id_supplier', $is_edit ? ($account['id_supplier'] ?? '') : ''); 
                                    ?>
                                    <select class="form-select" id="id_supplier" name="id_supplier" <?= $showSupplierSection ? 'required' : 'disabled' ?>>
                                        <option value="">-- Pilih Perusahaan Supplier --</option>
                                        <?php foreach ($suppliers as $sup) : ?>
                                            <option value="<?= esc($sup['id_supplier']) ?>" <?= (string)$selectedSupplier === (string)$sup['id_supplier'] ? 'selected' : '' ?>>
                                                <?= esc($sup['nama_perusahaan']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                                <div class="form-text">Akun supplier ini akan terhubung dengan data perusahaan yang dipilih.</div>
                            </div>

                            <!-- TIPE AKSES (Hanya untuk tipe Sales) -->
                            <?php 
                                $isSalesAccount = $is_edit ? ($account['tipe'] === 'sales') : ($selectedTipe === 'sales');
                                $selectedAkses = old('tipe_akses', $is_edit ? ($account['tipe_akses'] ?? '') : '');
                            ?>
                            <div class="mb-3" id="tipe_akses_wrapper" style="<?= $isSalesAccount ? '' : 'display: none;' ?>">
                                <label for="tipe_akses" class="form-label fw-bold">
                                    Tipe Akses <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="tipe_akses" name="tipe_akses" <?= $isSalesAccount ? 'required' : 'disabled' ?>>
                                    <option value="" <?= ($selectedAkses === '' || $selectedAkses === null) ? 'selected' : '' ?>>-- Pilih Tipe Akses --</option>
                                    <option value="internal" <?= $selectedAkses === 'internal' ? 'selected' : '' ?>>Internal</option>
                                    <option value="eksternal" <?= $selectedAkses === 'eksternal' ? 'selected' : '' ?>>Eksternal</option>
                                </select>
                                <div class="form-text">
                                    <strong>Internal:</strong> Akses penuh melihat identitas supplier dan komisi.<br>
                                    <strong>Eksternal:</strong> Akses terbatas tanpa identitas supplier dan tanpa komisi (aman untuk sales pihak luar/buyer).
                                </div>
                            </div>

                            <!-- NAMA LENGKAP -->
                            <div class="mb-3">
                                <label for="nama" class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nama" 
                                       name="nama" 
                                       maxlength="100" 
                                       placeholder="Contoh: Budi Santoso"
                                       value="<?= esc(old('nama', $is_edit ? $account['nama'] : '')) ?>" 
                                       required>
                                <div class="form-text">Maksimal 100 karakter.</div>
                            </div>

                            <!-- USERNAME -->
                            <div class="mb-3">
                                <label for="username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                                <?php if ($is_edit) : ?>
                                    <input type="text" 
                                           class="form-control bg-light" 
                                           id="username" 
                                           value="<?= esc($account['username']) ?>" 
                                           readonly 
                                           disabled>
                                    <div class="form-text">Username tidak dapat diubah setelah dibuat.</div>
                                <?php else : ?>
                                    <input type="text" 
                                           class="form-control" 
                                           id="username" 
                                           name="username" 
                                           minlength="4" 
                                           maxlength="50" 
                                           pattern="^[a-z0-9._-]+$"
                                           placeholder="Contoh: budi_sales / supplier_kopi"
                                           value="<?= esc(old('username')) ?>" 
                                           required>
                                    <div class="form-text">4 - 50 karakter. Hanya huruf kecil (a-z), angka (0-9), titik (.), garis bawah (_), dan strip (-).</div>
                                <?php endif; ?>
                            </div>

                            <!-- PASSWORD & KONFIRMASI -->
                            <div class="border rounded p-3 mb-3 bg-light">
                                <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                    <h6 class="fw-bold mb-0 text-primary">
                                        <i class="fa-solid fa-key me-1"></i>
                                        <?= $is_edit ? 'Ganti Password (Opsional)' : 'Kata Sandi (Password)' ?>
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="generateRandomPassword()">
                                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Buat Password Otomatis
                                    </button>
                                </div>
                                
                                <p class="text-muted small mb-3">
                                    <?= $is_edit ? 'Kosongkan kedua kolom di bawah jika tidak ingin mengubah password akun.' : 'Minimal 8 karakter. Anda dapat menggunakan tombol di atas untuk membuat password acak kuat otomatis.' ?>
                                </p>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label fw-bold">
                                            <?= $is_edit ? 'Password Baru' : 'Password' ?> 
                                            <?= $is_edit ? '' : '<span class="text-danger">*</span>' ?>
                                        </label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control font-monospace" 
                                                   id="password" 
                                                   name="password" 
                                                   minlength="8" 
                                                   placeholder="<?= $is_edit ? 'Kosongkan jika tetap' : 'Minimal 8 karakter' ?>"
                                                   <?= $is_edit ? '' : 'required' ?>>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('password', 'eyeIcon1')" title="Lihat/Sembunyikan">
                                                <i class="fa-solid fa-eye" id="eyeIcon1"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Minimal 8 karakter.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="konfirmasi_password" class="form-label fw-bold">
                                            <?= $is_edit ? 'Konfirmasi Password Baru' : 'Konfirmasi Password' ?> 
                                            <?= $is_edit ? '' : '<span class="text-danger">*</span>' ?>
                                        </label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   class="form-control font-monospace" 
                                                   id="konfirmasi_password" 
                                                   name="konfirmasi_password" 
                                                   minlength="8" 
                                                   placeholder="<?= $is_edit ? 'Ulangi password baru' : 'Ulangi password' ?>"
                                                   <?= $is_edit ? '' : 'required' ?>>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassVisibility('konfirmasi_password', 'eyeIcon2')" title="Lihat/Sembunyikan">
                                                <i class="fa-solid fa-eye" id="eyeIcon2"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Harus sama persis dengan kolom password.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- STATUS -->
                            <div class="mb-4">
                                <label for="status" class="form-label fw-bold">Status Akun <span class="text-danger">*</span></label>
                                <?php 
                                    $currentActive = (int)old('status', $is_edit ? $account['is_active'] : 1);
                                ?>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="1" <?= $currentActive === 1 ? 'selected' : '' ?>>Aktif (Dapat Login)</option>
                                    <option value="0" <?= $currentActive === 0 ? 'selected' : '' ?>>Nonaktif (Tidak Dapat Login)</option>
                                </select>
                                <div class="form-text">Akun nonaktif tidak akan dapat melakukan login ke dalam sistem.</div>
                            </div>

                            <!-- SUBMIT BUTTONS -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary text-white">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> <?= $is_edit ? 'Simpan Perubahan' : 'Simpan Akun' ?>
                                </button>
                                <a href="<?= base_url('admin/akun/index') ?>" class="btn btn-outline-secondary">
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

<script>
function toggleSupplierField() {
    var supplierRadio = document.getElementById('tipe_supplier');
    var supplierWrapper = document.getElementById('supplier_wrapper');
    var supplierSelect = document.getElementById('id_supplier');
    var tipeAksesWrapper = document.getElementById('tipe_akses_wrapper');
    var tipeAksesSelect = document.getElementById('tipe_akses');
    
    if (supplierRadio) {
        if (supplierRadio.checked) {
            if (supplierWrapper) supplierWrapper.style.display = 'block';
            if (supplierSelect) {
                supplierSelect.removeAttribute('disabled');
                supplierSelect.setAttribute('required', 'required');
            }
            if (tipeAksesWrapper) tipeAksesWrapper.style.display = 'none';
            if (tipeAksesSelect) {
                tipeAksesSelect.setAttribute('disabled', 'disabled');
                tipeAksesSelect.removeAttribute('required');
            }
        } else {
            if (supplierWrapper) supplierWrapper.style.display = 'none';
            if (supplierSelect) {
                supplierSelect.setAttribute('disabled', 'disabled');
                supplierSelect.removeAttribute('required');
            }
            if (tipeAksesWrapper) tipeAksesWrapper.style.display = 'block';
            if (tipeAksesSelect) {
                tipeAksesSelect.removeAttribute('disabled');
                tipeAksesSelect.setAttribute('required', 'required');
            }
        }
    }
}

function generateRandomPassword() {
    var lower = 'abcdefghjkmnpqrstuvwxyz';
    var upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    var numbers = '23456789';
    var symbols = '!@#$%&*';
    var all = lower + upper + numbers + symbols;

    // Pastikan 12 karakter kuat dengan setidaknya 1 dari masing-masing jenis
    var pass = '';
    pass += lower.charAt(Math.floor(Math.random() * lower.length));
    pass += upper.charAt(Math.floor(Math.random() * upper.length));
    pass += numbers.charAt(Math.floor(Math.random() * numbers.length));
    pass += symbols.charAt(Math.floor(Math.random() * symbols.length));

    for (var i = 4; i < 12; i++) {
        pass += all.charAt(Math.floor(Math.random() * all.length));
    }

    // Acak urutan karakter
    pass = pass.split('').sort(function() { return 0.5 - Math.random(); }).join('');

    var passInput = document.getElementById('password');
    var confirmInput = document.getElementById('konfirmasi_password');
    if (passInput && confirmInput) {
        passInput.value = pass;
        confirmInput.value = pass;

        // Tampilkan teks password agar langsung terlihat oleh admin
        passInput.type = 'text';
        confirmInput.type = 'text';

        var eye1 = document.getElementById('eyeIcon1');
        var eye2 = document.getElementById('eyeIcon2');
        if (eye1) { eye1.classList.remove('fa-eye'); eye1.classList.add('fa-eye-slash'); }
        if (eye2) { eye2.classList.remove('fa-eye'); eye2.classList.add('fa-eye-slash'); }
    }
}

function togglePassVisibility(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon = document.getElementById(iconId);
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Inisialisasi on load
document.addEventListener('DOMContentLoaded', function() {
    toggleSupplierField();
});
</script>

<?= $this->endSection(); ?>
