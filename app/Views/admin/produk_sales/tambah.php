<?= $this->extend('admin/template/template'); ?>
<?= $this->section('content'); ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/produk_sales/index') ?>">Produk Sales</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tambah Produk</li>
                    </ol>
                </nav>
                <h1 class="app-page-title mb-0">Tambah Produk Sales</h1>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('admin/produk_sales/index') ?>" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div><!--//row-->

        <!-- Alert Success Flashdata -->
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Alert Error Flashdata -->
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Validation Errors Box -->
        <?php $validationErrors = session('errors') ?? (isset($validation) ? $validation->getErrors() : []); ?>
        <?php if (!empty($validationErrors)) : ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>Mohon perbaiki kesalahan berikut:</div>
                <ul class="mb-0 ps-3">
                    <?php foreach ($validationErrors as $err) : ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="app-card shadow-sm mb-4">
            <div class="app-card-body p-4">
                <form action="<?= base_url('admin/produk_sales/proses_tambah') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="row g-4">
                        <!-- Kolom Kiri: Informasi Pokok -->
                        <div class="col-12 col-lg-6">
                            <h5 class="section-title border-bottom pb-2 mb-3 text-primary">
                                <i class="fa-solid fa-info-circle me-2"></i>Informasi Utama
                            </h5>

                            <!-- Kode Produk (Informasi Otomatis) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Saran Kode Produk</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light font-monospace fw-bold text-success">
                                        <i class="fa-solid fa-barcode me-1"></i> <?= esc($suggested_code) ?>
                                    </span>
                                    <input type="text" class="form-control bg-light" value="<?= esc($suggested_code) ?>" readonly>
                                </div>
                                <small class="text-muted">Kode produk di-generate otomatis unik secara sekuensial (format standar: IE-XXXX) saat disimpan.</small>
                            </div>

                            <!-- Supplier / Perusahaan -->
                            <div class="mb-3">
                                <label for="id_supplier" class="form-label fw-bold">Supplier / Perusahaan <span class="text-danger">*</span></label>
                                <select name="id_supplier" id="id_supplier" class="form-select <?= (isset($validation) && $validation->hasError('id_supplier')) ? 'is-invalid' : '' ?>" required>
                                    <option value="">-- Pilih Supplier --</option>
                                    <?php foreach ($suppliers as $s) : ?>
                                        <option value="<?= $s['id_supplier'] ?>" <?= (old('id_supplier', $selected_supplier_id ?? 0) == $s['id_supplier']) ? 'selected' : '' ?>>
                                            <?= esc($s['nama_perusahaan']) ?> (<?= esc($s['domisili_usaha'] ?? 'Indonesia') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($validation) && $validation->hasError('id_supplier')) : ?>
                                    <div class="invalid-feedback"><?= $validation->getError('id_supplier') ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Nama Produk -->
                            <div class="mb-3">
                                <label for="nama_produk" class="form-label fw-bold">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="nama_produk" 
                                       id="nama_produk" 
                                       class="form-control <?= (isset($validation) && $validation->hasError('nama_produk')) ? 'is-invalid' : '' ?>" 
                                       value="<?= old('nama_produk') ?>" 
                                       placeholder="Contoh: Arang Briket Batok Kelapa" 
                                       required>
                                <?php if (isset($validation) && $validation->hasError('nama_produk')) : ?>
                                    <div class="invalid-feedback"><?= $validation->getError('nama_produk') ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Lokasi Barang -->
                            <div class="mb-3">
                                <label for="lokasi_barang" class="form-label fw-bold">Lokasi Barang</label>
                                <input type="text" 
                                       name="lokasi_barang" 
                                       id="lokasi_barang" 
                                       class="form-control <?= (isset($validation) && $validation->hasError('lokasi_barang')) ? 'is-invalid' : '' ?>" 
                                       value="<?= old('lokasi_barang') ?>" 
                                       placeholder="Contoh: Semarang, Jawa Tengah">
                                <small class="text-muted">Jika dikosongkan, akan otomatis menggunakan domisili usaha supplier terkait.</small>
                            </div>

                            <!-- Status Ekspor -->
                            <div class="mb-3">
                                <label for="sudah_ekspor" class="form-label fw-bold">Status Pengalaman Ekspor</label>
                                <select name="sudah_ekspor" id="sudah_ekspor" class="form-select">
                                    <option value="Belum" <?= (old('sudah_ekspor') === 'Belum') ? 'selected' : '' ?>>Belum Pernah Ekspor</option>
                                    <option value="Sudah" <?= (old('sudah_ekspor') === 'Sudah') ? 'selected' : '' ?>>Sudah Pernah Ekspor</option>
                                </select>
                            </div>

                            <!-- Upload Foto Produk -->
                            <div class="mb-3">
                                <label for="foto_1" class="form-label fw-bold">Foto Produk</label>
                                <input type="file" 
                                       name="foto_1" 
                                       id="foto_1" 
                                       class="form-control <?= (isset($validation) && $validation->hasError('foto_1')) ? 'is-invalid' : '' ?>" 
                                       accept="image/jpeg,image/png,image/webp">
                                <div class="form-text text-muted">
                                    Format: JPG, JPEG, PNG, WEBP. Ukuran maksimal: 2 MB. Gambar akan disimpan dengan nama acak.
                                </div>
                                <?php if (isset($validation) && $validation->hasError('foto_1')) : ?>
                                    <div class="invalid-feedback"><?= $validation->getError('foto_1') ?></div>
                                <?php endif; ?>
                            </div>
                        </div><!--//col-->

                        <!-- Kolom Kanan: Rincian & Spesifikasi -->
                        <div class="col-12 col-lg-6">
                            <h5 class="section-title border-bottom pb-2 mb-3 text-primary">
                                <i class="fa-solid fa-list-check me-2"></i>Deskripsi & Komersial
                            </h5>

                            <!-- Deskripsi Produk -->
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label fw-bold">Deskripsi Produk</label>
                                <textarea name="deskripsi" 
                                          id="deskripsi" 
                                          rows="4" 
                                          class="form-control" 
                                          placeholder="Tuliskan deskripsi lengkap produk (kegunaan, keunggulan, penjelasan detail)..."><?= old('deskripsi') ?></textarea>
                                <small class="text-muted">Deskripsi ini akan ditampilkan lengkap di halaman Detail Sales tab Data Produk dan di dokumen PDF.</small>
                            </div>

                            <!-- Spesifikasi -->
                            <div class="mb-3">
                                <label for="spesifikasi" class="form-label fw-bold">Spesifikasi Produk</label>
                                <textarea name="spesifikasi" 
                                          id="spesifikasi" 
                                          rows="4" 
                                          class="form-control" 
                                          placeholder="Deskripsi detail spesifikasi bahan, ukuran, grade, atau mutu barang..."><?= old('spesifikasi') ?></textarea>
                            </div>

                            <!-- Kapasitas Produksi -->
                            <div class="mb-3">
                                <label for="kapasitas_produksi" class="form-label fw-bold">Kapasitas Produksi</label>
                                <input type="text" 
                                       name="kapasitas_produksi" 
                                       id="kapasitas_produksi" 
                                       class="form-control" 
                                       value="<?= old('kapasitas_produksi') ?>" 
                                       placeholder="Contoh: 50 Ton / Bulan atau 10.000 Pcs / Bulan">
                            </div>

                            <!-- MOQ & Harga EXW (2 kolom) -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label for="moq" class="form-label fw-bold">MOQ (Min. Order)</label>
                                    <input type="text" 
                                           name="moq" 
                                           id="moq" 
                                           class="form-control" 
                                           value="<?= old('moq') ?>" 
                                           placeholder="Contoh: 1 Kontainer 20ft">
                                </div>
                                <div class="col-6">
                                    <label for="harga_exw" class="form-label fw-bold">Harga EXW</label>
                                    <input type="text" 
                                           name="harga_exw" 
                                           id="harga_exw" 
                                           class="form-control" 
                                           value="<?= old('harga_exw') ?>" 
                                           placeholder="Contoh: USD 1,200 / MT">
                                </div>
                            </div>

                            <!-- Komisi (Hanya terlihat di mode internal sales) -->
                            <div class="mb-3">
                                <label for="komisi" class="form-label fw-bold">
                                    Komisi Sales <span class="badge bg-warning text-dark ms-1">Internal Saja</span>
                                </label>
                                <input type="text" 
                                       name="komisi" 
                                       id="komisi" 
                                       class="form-control" 
                                       value="<?= old('komisi') ?>" 
                                       placeholder="Contoh: Rp 500 / kg atau 3%">
                                <small class="text-muted">Nilai komisi ini disensor otomatis pada mode Eksternal sehingga aman dibagikan ke buyer.</small>
                            </div>

                            <!-- Sertifikasi & Legalitas -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label for="sertifikasi" class="form-label fw-bold">Sertifikasi</label>
                                    <input type="text" 
                                           name="sertifikasi" 
                                           id="sertifikasi" 
                                           class="form-control" 
                                           value="<?= old('sertifikasi') ?>" 
                                           placeholder="Contoh: Halal, BPOM, COA">
                                </div>
                                <div class="col-6">
                                    <label for="legalitas" class="form-label fw-bold">Legalitas</label>
                                    <input type="text" 
                                           name="legalitas" 
                                           id="legalitas" 
                                           class="form-control" 
                                           value="<?= old('legalitas') ?>" 
                                           placeholder="Contoh: NIB, NPWP">
                                </div>
                            </div>
                        </div><!--//col-->
                    </div><!--//row-->

                    <!-- Bagian History Ekspor (Opsional) -->
                    <div class="border-top pt-4 mt-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <h5 class="section-title mb-1 text-primary">
                                    <i class="fa-solid fa-ship me-2"></i>History Ekspor (Opsional)
                                </h5>
                                <small class="text-muted">
                                    <i class="fa-solid fa-circle-info me-1"></i> History ini milik perusahaan yang dipilih di atas, berlaku untuk semua produknya.
                                </small>
                            </div>
                            <div>
                                <button type="button" id="btn-add-history" class="btn btn-outline-success btn-sm fw-semibold">
                                    <i class="fa-solid fa-plus me-1"></i> Tambah Baris
                                </button>
                            </div>
                        </div>

                        <div id="history-rows-container">
                            <?php 
                            $oldHistory = old('history');
                            $hIdx = 0;
                            if (is_array($oldHistory) && !empty($oldHistory)) : 
                                foreach ($oldHistory as $key => $h) :
                                    $tRow = esc($h['tahun'] ?? '');
                                    $pRow = esc($h['produk'] ?? '');
                                    $nRow = esc($h['negara_tujuan'] ?? '');
                                    $jRow = esc($h['jenis_ekspor'] ?? '');
                                    $kRow = esc($h['kuantitas'] ?? '');
                                    $uRow = esc($h['nilai_ekspor_usd'] ?? '');
                                    $cRow = esc($h['catatan'] ?? '');
                            ?>
                                <div class="history-row border rounded-3 p-3 mb-3 bg-light position-relative" data-index="<?= $hIdx ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <span class="fw-bold text-secondary history-row-number">
                                            <i class="fa-solid fa-ship me-1 text-primary"></i> Baris History #<span class="row-num-label"><?= $hIdx + 1 ?></span>
                                        </span>
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-history" title="Hapus Baris Ini">
                                            <i class="fa-solid fa-trash-can me-1"></i> Hapus Baris
                                        </button>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-12 col-md-2">
                                            <label class="form-label small fw-bold">Tahun <span class="text-danger">*</span></label>
                                            <input type="number" name="history[<?= $hIdx ?>][tahun]" class="form-control form-control-sm" value="<?= $tRow ?>" placeholder="Contoh: 2024" min="1900" max="2099">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small fw-bold">Produk <span class="text-danger">*</span></label>
                                            <input type="text" name="history[<?= $hIdx ?>][produk]" class="form-control form-control-sm" value="<?= $pRow ?>" placeholder="Nama produk ekspor">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-bold">Negara Tujuan <span class="text-danger">*</span></label>
                                            <input type="text" name="history[<?= $hIdx ?>][negara_tujuan]" class="form-control form-control-sm" value="<?= $nRow ?>" placeholder="Contoh: Malaysia, Singapura">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Jenis Ekspor</label>
                                            <input type="text" name="history[<?= $hIdx ?>][jenis_ekspor]" class="form-control form-control-sm" value="<?= $jRow ?>" placeholder="Contoh: Langsung, Maklon">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Kuantitas</label>
                                            <input type="text" name="history[<?= $hIdx ?>][kuantitas]" class="form-control form-control-sm" value="<?= $kRow ?>" placeholder="Contoh: 800 pcs / 20 MT">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Nilai Ekspor (USD)</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" min="0" name="history[<?= $hIdx ?>][nilai_ekspor_usd]" class="form-control form-control-sm" value="<?= $uRow ?>" placeholder="Contoh: 25000.00">
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-bold">Catatan</label>
                                            <input type="text" name="history[<?= $hIdx ?>][catatan]" class="form-control form-control-sm" value="<?= $cRow ?>" placeholder="Catatan tambahan (opsional)">
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                    $hIdx++;
                                endforeach; 
                            endif; 
                            ?>
                        </div>

                        <div id="history-empty-placeholder" class="text-muted text-center py-4 border border-dashed rounded-3 bg-light mb-3" style="<?= (!empty($oldHistory) && is_array($oldHistory)) ? 'display: none;' : '' ?>">
                            <i class="fa-solid fa-box-open fs-3 me-2 text-secondary d-block mb-1"></i>
                            Belum ada baris history ekspor. Klik tombol <strong>"+ Tambah Baris"</strong> di atas jika perusahaan ini sudah memiliki riwayat ekspor.
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-4 d-flex justify-content-end gap-2">
                        <a href="<?= base_url('admin/produk_sales/index') ?>" class="btn btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary text-white">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Produk Sales
                        </button>
                    </div>
                </form>
            </div><!--//app-card-body-->
        </div><!--//app-card-->

    </div><!--//container-xl-->
</div><!--//app-content-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    var historyContainer = document.getElementById('history-rows-container');
    var emptyPlaceholder = document.getElementById('history-empty-placeholder');
    var btnAdd = document.getElementById('btn-add-history');
    var historyIndex = <?= isset($hIdx) && $hIdx > 0 ? $hIdx : 0 ?>;

    function updateVisibilityAndNumbers() {
        var rows = historyContainer.querySelectorAll('.history-row');
        if (rows.length === 0) {
            emptyPlaceholder.style.display = 'block';
        } else {
            emptyPlaceholder.style.display = 'none';
        }
        rows.forEach(function(row, idx) {
            var label = row.querySelector('.row-num-label');
            if (label) {
                label.textContent = (idx + 1);
            }
        });
    }

    if (btnAdd) {
        btnAdd.addEventListener('click', function() {
            var currentRows = historyContainer.querySelectorAll('.history-row');
            if (currentRows.length >= 20) {
                alert('Maksimal 20 baris history ekspor yang dapat ditambahkan sekaligus.');
                return;
            }

            var defaultProduk = '';
            var namaProdukInput = document.getElementById('nama_produk');
            if (namaProdukInput && namaProdukInput.value.trim() !== '') {
                defaultProduk = namaProdukInput.value.trim();
            }

            var currentYear = new Date().getFullYear();
            var rowHtml = `
            <div class="history-row border rounded-3 p-3 mb-3 bg-light position-relative" data-index="${historyIndex}">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="fw-bold text-secondary history-row-number">
                        <i class="fa-solid fa-ship me-1 text-primary"></i> Baris History #<span class="row-num-label">${currentRows.length + 1}</span>
                    </span>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-history" title="Hapus Baris Ini">
                        <i class="fa-solid fa-trash-can me-1"></i> Hapus Baris
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-12 col-md-2">
                        <label class="form-label small fw-bold">Tahun <span class="text-danger">*</span></label>
                        <input type="number" name="history[${historyIndex}][tahun]" class="form-control form-control-sm" value="${currentYear}" placeholder="Contoh: 2024" min="1900" max="2099">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold">Produk <span class="text-danger">*</span></label>
                        <input type="text" name="history[${historyIndex}][produk]" class="form-control form-control-sm" value="${defaultProduk.replace(/"/g, '&quot;')}" placeholder="Nama produk ekspor">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">Negara Tujuan <span class="text-danger">*</span></label>
                        <input type="text" name="history[${historyIndex}][negara_tujuan]" class="form-control form-control-sm" placeholder="Contoh: Malaysia, Singapura">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold">Jenis Ekspor</label>
                        <input type="text" name="history[${historyIndex}][jenis_ekspor]" class="form-control form-control-sm" placeholder="Contoh: Langsung, Maklon">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold">Kuantitas</label>
                        <input type="text" name="history[${historyIndex}][kuantitas]" class="form-control form-control-sm" placeholder="Contoh: 800 pcs / 20 MT">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold">Nilai Ekspor (USD)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" name="history[${historyIndex}][nilai_ekspor_usd]" class="form-control form-control-sm" placeholder="Contoh: 25000.00">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold">Catatan</label>
                        <input type="text" name="history[${historyIndex}][catatan]" class="form-control form-control-sm" placeholder="Catatan tambahan (opsional)">
                    </div>
                </div>
            </div>`;

            historyContainer.insertAdjacentHTML('beforeend', rowHtml);
            historyIndex++;
            updateVisibilityAndNumbers();
        });
    }

    historyContainer.addEventListener('click', function(e) {
        var btnRemove = e.target.closest('.btn-remove-history');
        if (btnRemove) {
            var row = btnRemove.closest('.history-row');
            if (row) {
                row.remove();
                updateVisibilityAndNumbers();
            }
        }
    });

    updateVisibilityAndNumbers();
});
</script>

<?= $this->endSection(); ?>
