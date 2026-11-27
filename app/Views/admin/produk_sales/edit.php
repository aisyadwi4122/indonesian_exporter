<?= $this->extend('admin/template/template'); ?>
<?= $this->section('content'); ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/produk_sales/index') ?>">Produk Sales</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ubah Produk <?= esc($kode_produk) ?></li>
                    </ol>
                </nav>
                <h1 class="app-page-title mb-0">Ubah Produk Sales: <?= esc($produk['nama_produk']) ?></h1>
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
                <form action="<?= base_url('admin/produk_sales/proses_edit/' . $produk['id_produk_supplier']) ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="row g-4">
                        <!-- Kolom Kiri: Informasi Pokok & Foto -->
                        <div class="col-12 col-lg-6">
                            <h5 class="section-title border-bottom pb-2 mb-3 text-primary">
                                <i class="fa-solid fa-info-circle me-2"></i>Informasi Utama
                            </h5>

                            <!-- Kode Produk (Readonly) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kode Produk</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light font-monospace fw-bold text-primary">
                                        <i class="fa-solid fa-barcode me-1"></i> <?= esc($kode_produk) ?>
                                    </span>
                                    <input type="text" class="form-control bg-light font-monospace" value="<?= esc($kode_produk) ?>" readonly>
                                </div>
                                <small class="text-muted">Kode produk mengacu pada ID sekuensial unik (ID: <?= esc($produk['id_produk_supplier']) ?>).</small>
                            </div>

                            <!-- Supplier / Perusahaan -->
                            <div class="mb-3">
                                <label for="id_supplier" class="form-label fw-bold">Supplier / Perusahaan <?= !empty($produk['id_supplier']) ? '<span class="text-danger">*</span>' : '' ?></label>
                                <select name="id_supplier" id="id_supplier" class="form-select <?= (isset($validation) && $validation->hasError('id_supplier')) ? 'is-invalid' : '' ?>" <?= !empty($produk['id_supplier']) ? 'required' : '' ?>>
                                    <?php if (empty($produk['id_supplier']) && !empty($produk['nama_perusahaan'])) : ?>
                                        <option value="" selected><?= esc($produk['nama_perusahaan']) ?> (Peserta ECP)</option>
                                    <?php else : ?>
                                        <option value="">-- Pilih Supplier --</option>
                                    <?php endif; ?>
                                    <?php foreach ($suppliers as $s) : ?>
                                        <option value="<?= $s['id_supplier'] ?>" <?= (old('id_supplier', $produk['id_supplier']) == $s['id_supplier']) ? 'selected' : '' ?>>
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
                                       value="<?= old('nama_produk', $produk['nama_produk']) ?>" 
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
                                       value="<?= old('lokasi_barang', $produk['lokasi_barang']) ?>" 
                                       placeholder="Contoh: Semarang, Jawa Tengah">
                                <small class="text-muted">Jika dikosongkan, akan menggunakan domisili supplier.</small>
                            </div>

                            <!-- Status Ekspor -->
                            <div class="mb-3">
                                <label for="sudah_ekspor" class="form-label fw-bold">Status Pengalaman Ekspor</label>
                                <select name="sudah_ekspor" id="sudah_ekspor" class="form-select">
                                    <option value="Belum" <?= (old('sudah_ekspor', $produk['sudah_ekspor']) === 'Belum') ? 'selected' : '' ?>>Belum Pernah Ekspor</option>
                                    <option value="Sudah" <?= (old('sudah_ekspor', $produk['sudah_ekspor']) === 'Sudah') ? 'selected' : '' ?>>Sudah Pernah Ekspor</option>
                                </select>
                            </div>

                            <!-- Pratinjau & Upload Foto Produk -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Foto Produk Saat Ini</label>
                                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded border mb-2">
                                    <img src="<?= $current_photo_url ? esc($current_photo_url) : base_url('assets/img/logo.png') ?>" 
                                         alt="<?= esc($produk['nama_produk']) ?>" 
                                         class="rounded border shadow-sm" 
                                         style="width: 90px; height: 90px; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>';">
                                    <div class="small">
                                        <div class="fw-bold mb-1">Status Foto:</div>
                                        <?php if ($current_photo_url) : ?>
                                            <span class="badge bg-success mb-1">Foto Aktif Tersedia</span>
                                            <div class="text-muted text-break" style="font-size: 11px;">
                                                <?= esc($produk['foto_1'] ?: 'File lokal / Google Drive') ?>
                                            </div>
                                        <?php else : ?>
                                            <span class="badge bg-secondary mb-1">Belum Ada Foto (Memakai Logo Cadangan)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <label for="foto_1" class="form-label fw-bold">Ganti Foto Produk (Opsional)</label>
                                <input type="file" 
                                       name="foto_1" 
                                       id="foto_1" 
                                       class="form-control <?= (isset($validation) && $validation->hasError('foto_1')) ? 'is-invalid' : '' ?>" 
                                       accept="image/jpeg,image/png,image/webp">
                                <div class="form-text text-muted">
                                    Biarkan kosong jika tidak ingin mengganti foto saat ini. Format yang didukung: JPG, JPEG, PNG, WEBP (Maksimal 2 MB).
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
                                          placeholder="Tuliskan deskripsi lengkap produk (kegunaan, keunggulan, penjelasan detail)..."><?= old('deskripsi', $produk['deskripsi'] ?? '') ?></textarea>
                                <small class="text-muted">Deskripsi ini akan ditampilkan lengkap di halaman Detail Sales tab Data Produk dan di dokumen PDF.</small>
                            </div>

                            <!-- Spesifikasi -->
                            <div class="mb-3">
                                <label for="spesifikasi" class="form-label fw-bold">Spesifikasi Produk</label>
                                <textarea name="spesifikasi" 
                                          id="spesifikasi" 
                                          rows="4" 
                                          class="form-control" 
                                          placeholder="Deskripsi detail spesifikasi..."><?= old('spesifikasi', $produk['spesifikasi']) ?></textarea>
                            </div>

                            <!-- Kapasitas Produksi -->
                            <div class="mb-3">
                                <label for="kapasitas_produksi" class="form-label fw-bold">Kapasitas Produksi</label>
                                <input type="text" 
                                       name="kapasitas_produksi" 
                                       id="kapasitas_produksi" 
                                       class="form-control" 
                                       value="<?= old('kapasitas_produksi', $produk['kapasitas_produksi']) ?>" 
                                       placeholder="Contoh: 50 Ton / Bulan">
                            </div>

                            <!-- MOQ & Harga EXW -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label for="moq" class="form-label fw-bold">MOQ (Min. Order)</label>
                                    <input type="text" 
                                           name="moq" 
                                           id="moq" 
                                           class="form-control" 
                                           value="<?= old('moq', $produk['moq']) ?>" 
                                           placeholder="Contoh: 1 Kontainer 20ft">
                                </div>
                                <div class="col-6">
                                    <label for="harga_exw" class="form-label fw-bold">Harga EXW</label>
                                    <input type="text" 
                                           name="harga_exw" 
                                           id="harga_exw" 
                                           class="form-control" 
                                           value="<?= old('harga_exw', $produk['harga_exw']) ?>" 
                                           placeholder="Contoh: USD 1,200 / MT">
                                </div>
                            </div>

                            <!-- Komisi -->
                            <div class="mb-3">
                                <label for="komisi" class="form-label fw-bold">
                                    Komisi Sales <span class="badge bg-warning text-dark ms-1">Internal Saja</span>
                                </label>
                                <input type="text" 
                                       name="komisi" 
                                       id="komisi" 
                                       class="form-control" 
                                       value="<?= old('komisi', $produk['komisi']) ?>" 
                                       placeholder="Contoh: Rp 500 / kg atau 3%">
                                <small class="text-muted">Komisi hanya dapat dilihat oleh tim internal dan tidak muncul di mode Eksternal / PDF buyer.</small>
                            </div>

                            <!-- Sertifikasi & Legalitas -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label for="sertifikasi" class="form-label fw-bold">Sertifikasi</label>
                                    <input type="text" 
                                           name="sertifikasi" 
                                           id="sertifikasi" 
                                           class="form-control" 
                                           value="<?= old('sertifikasi', $produk['sertifikasi']) ?>" 
                                           placeholder="Contoh: Halal, BPOM, COA">
                                </div>
                                <div class="col-6">
                                    <label for="legalitas" class="form-label fw-bold">Legalitas</label>
                                    <input type="text" 
                                           name="legalitas" 
                                           id="legalitas" 
                                           class="form-control" 
                                           value="<?= old('legalitas', $produk['legalitas']) ?>" 
                                           placeholder="Contoh: NIB, NPWP">
                                </div>
                            </div>
                        </div><!--//col-->
                    </div><!--//row-->

                    <div class="border-top pt-3 mt-4 d-flex justify-content-end gap-2">
                        <a href="<?= base_url('admin/produk_sales/index') ?>" class="btn btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary text-white">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div><!--//app-card-body-->
        </div><!--//app-card-->

        <!-- Bagian History Ekspor Perusahaan -->
        <div class="app-card shadow-sm mb-4">
            <div class="app-card-header p-3 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="app-card-title mb-1 text-primary">
                            <i class="fa-solid fa-ship me-2"></i>History Ekspor Perusahaan Ini
                        </h5>
                        <p class="text-muted small mb-0">
                            <i class="fa-solid fa-circle-info me-1"></i> Data ini milik perusahaan, berlaku untuk semua produknya.
                        </p>
                    </div>
                    <div>
                        <?php if (!empty($supplier_id)) : ?>
                            <button type="button" class="btn btn-success text-white btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahHistory">
                                <i class="fa-solid fa-plus me-1"></i> Tambah History
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div><!--//app-card-header-->
            <div class="app-card-body p-3">
                <?php if (empty($supplier_id)) : ?>
                    <div class="alert alert-warning mb-0" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Produk ini belum terhubung dengan data supplier terdaftar. Silakan pilih dan simpan Supplier pada form di atas terlebih dahulu untuk mengelola riwayat ekspor.
                    </div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th style="width: 90px;">Tahun</th>
                                    <th>Produk</th>
                                    <th>Negara Tujuan</th>
                                    <th>Jenis Ekspor</th>
                                    <th>Kuantitas</th>
                                    <th class="text-end">Nilai Ekspor (USD)</th>
                                    <th>Catatan</th>
                                    <th class="text-center" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($exportHistories)) : ?>
                                    <?php $no = 1; foreach ($exportHistories as $hist) : ?>
                                        <tr>
                                            <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= esc($hist['tahun']) ?></span>
                                            </td>
                                            <td class="fw-semibold"><?= esc($hist['produk']) ?></td>
                                            <td>
                                                <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                                                <?= esc($hist['negara_tujuan']) ?>
                                            </td>
                                            <td><?= !empty($hist['jenis_ekspor']) ? esc($hist['jenis_ekspor']) : '<span class="text-muted">-</span>' ?></td>
                                            <td><?= !empty($hist['kuantitas']) ? esc($hist['kuantitas']) : '<span class="text-muted">-</span>' ?></td>
                                            <td class="text-end fw-bold text-success font-monospace">
                                                <?= ($hist['nilai_ekspor_usd'] !== null && $hist['nilai_ekspor_usd'] !== '') ? '$' . number_format((float)$hist['nilai_ekspor_usd'], 2, '.', ',') : '<span class="text-muted fw-normal">-</span>' ?>
                                            </td>
                                            <td class="small text-muted"><?= !empty($hist['catatan']) ? esc($hist['catatan']) : '-' ?></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary btn-edit-history"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalUbahHistory"
                                                            data-id="<?= $hist['id_history'] ?>"
                                                            data-tahun="<?= esc($hist['tahun']) ?>"
                                                            data-produk="<?= esc($hist['produk'], 'attr') ?>"
                                                            data-negara="<?= esc($hist['negara_tujuan'], 'attr') ?>"
                                                            data-jenis="<?= esc($hist['jenis_ekspor'] ?? '', 'attr') ?>"
                                                            data-kuantitas="<?= esc($hist['kuantitas'] ?? '', 'attr') ?>"
                                                            data-nilai="<?= esc($hist['nilai_ekspor_usd'] ?? '', 'attr') ?>"
                                                            data-catatan="<?= esc($hist['catatan'] ?? '', 'attr') ?>"
                                                            title="Ubah">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <form action="<?= base_url('admin/produk_sales/history_hapus/' . $produk['id_produk_supplier'] . '/' . $hist['id_history']) ?>" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus history ekspor produk <?= esc($hist['produk'], 'js') ?> ke <?= esc($hist['negara_tujuan'], 'js') ?>?');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-box-open fa-2x mb-2 d-block text-secondary"></i>
                                            Belum ada history ekspor.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div><!--//app-card-body-->
        </div><!--//app-card-->

        <?php if (!empty($supplier_id)) : ?>
            <!-- Modal Tambah History Ekspor -->
            <div class="modal fade" id="modalTambahHistory" tabindex="-1" aria-labelledby="modalTambahHistoryLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="<?= base_url('admin/produk_sales/history_tambah/' . $produk['id_produk_supplier']) ?>" method="POST">
                            <?= csrf_field() ?>
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalTambahHistoryLabel">
                                    <i class="fa-solid fa-plus-circle text-success me-2"></i>Tambah History Ekspor
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="tambah_tahun" class="form-label fw-bold">Tahun <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           name="tahun" 
                                           id="tambah_tahun" 
                                           class="form-control" 
                                           min="1900" 
                                           max="2099" 
                                           value="<?= date('Y') ?>" 
                                           placeholder="Contoh: 2024" 
                                           required>
                                    <small class="text-muted">Format 4 digit angka.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="tambah_produk" class="form-label fw-bold">Produk <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="produk" 
                                           id="tambah_produk" 
                                           class="form-control" 
                                           value="<?= esc($produk['nama_produk']) ?>" 
                                           placeholder="Nama produk yang diekspor" 
                                           required>
                                </div>
                                <div class="mb-3">
                                    <label for="tambah_negara_tujuan" class="form-label fw-bold">Negara Tujuan <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="negara_tujuan" 
                                           id="tambah_negara_tujuan" 
                                           class="form-control" 
                                           placeholder="Contoh: Malaysia, Amerika Serikat, dll." 
                                           required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label for="tambah_jenis_ekspor" class="form-label fw-bold">Jenis Ekspor</label>
                                        <input type="text" 
                                               name="jenis_ekspor" 
                                               id="tambah_jenis_ekspor" 
                                               class="form-control" 
                                               placeholder="Contoh: Langsung, Maklon">
                                    </div>
                                    <div class="col-6">
                                        <label for="tambah_kuantitas" class="form-label fw-bold">Kuantitas</label>
                                        <input type="text" 
                                               name="kuantitas" 
                                               id="tambah_kuantitas" 
                                               class="form-control" 
                                               placeholder="Contoh: 1 Kontainer 20ft">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="tambah_nilai_ekspor_usd" class="form-label fw-bold">Nilai Ekspor (USD)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               name="nilai_ekspor_usd" 
                                               id="tambah_nilai_ekspor_usd" 
                                               class="form-control" 
                                               placeholder="Contoh: 25000.00">
                                    </div>
                                    <small class="text-muted">Hanya angka &ge; 0 atau kosongkan jika tidak ada data.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="tambah_catatan" class="form-label fw-bold">Catatan</label>
                                    <textarea name="catatan" 
                                              id="tambah_catatan" 
                                              class="form-control" 
                                              rows="2" 
                                              placeholder="Catatan tambahan (opsional)"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary text-white">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan History
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Ubah History Ekspor -->
            <div class="modal fade" id="modalUbahHistory" tabindex="-1" aria-labelledby="modalUbahHistoryLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="formUbahHistory" action="" method="POST">
                            <?= csrf_field() ?>
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalUbahHistoryLabel">
                                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Ubah History Ekspor
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="ubah_tahun" class="form-label fw-bold">Tahun <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           name="tahun" 
                                           id="ubah_tahun" 
                                           class="form-control" 
                                           min="1900" 
                                           max="2099" 
                                           required>
                                    <small class="text-muted">Format 4 digit angka.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="ubah_produk" class="form-label fw-bold">Produk <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="produk" 
                                           id="ubah_produk" 
                                           class="form-control" 
                                           required>
                                </div>
                                <div class="mb-3">
                                    <label for="ubah_negara_tujuan" class="form-label fw-bold">Negara Tujuan <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="negara_tujuan" 
                                           id="ubah_negara_tujuan" 
                                           class="form-control" 
                                           required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label for="ubah_jenis_ekspor" class="form-label fw-bold">Jenis Ekspor</label>
                                        <input type="text" 
                                               name="jenis_ekspor" 
                                               id="ubah_jenis_ekspor" 
                                               class="form-control">
                                    </div>
                                    <div class="col-6">
                                        <label for="ubah_kuantitas" class="form-label fw-bold">Kuantitas</label>
                                        <input type="text" 
                                               name="kuantitas" 
                                               id="ubah_kuantitas" 
                                               class="form-control">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="ubah_nilai_ekspor_usd" class="form-label fw-bold">Nilai Ekspor (USD)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               name="nilai_ekspor_usd" 
                                               id="ubah_nilai_ekspor_usd" 
                                               class="form-control">
                                    </div>
                                    <small class="text-muted">Hanya angka &ge; 0 atau kosongkan jika tidak ada data.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="ubah_catatan" class="form-label fw-bold">Catatan</label>
                                    <textarea name="catatan" 
                                              id="ubah_catatan" 
                                              class="form-control" 
                                              rows="2"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary text-white">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalUbahEl = document.getElementById('modalUbahHistory');
                if (modalUbahEl) {
                    var formUbah = document.getElementById('formUbahHistory');
                    var inputTahun = document.getElementById('ubah_tahun');
                    var inputProduk = document.getElementById('ubah_produk');
                    var inputNegara = document.getElementById('ubah_negara_tujuan');
                    var inputJenis = document.getElementById('ubah_jenis_ekspor');
                    var inputKuantitas = document.getElementById('ubah_kuantitas');
                    var inputNilai = document.getElementById('ubah_nilai_ekspor_usd');
                    var inputCatatan = document.getElementById('ubah_catatan');
                    var baseUrlUbah = "<?= base_url('admin/produk_sales/history_ubah/' . $produk['id_produk_supplier']) ?>";

                    modalUbahEl.addEventListener('show.bs.modal', function(event) {
                        var btn = event.relatedTarget;
                        if (!btn) return;
                        var id = btn.getAttribute('data-id');
                        formUbah.action = baseUrlUbah + '/' + id;
                        inputTahun.value = btn.getAttribute('data-tahun') || '';
                        inputProduk.value = btn.getAttribute('data-produk') || '';
                        inputNegara.value = btn.getAttribute('data-negara') || '';
                        inputJenis.value = btn.getAttribute('data-jenis') || '';
                        inputKuantitas.value = btn.getAttribute('data-kuantitas') || '';
                        inputNilai.value = btn.getAttribute('data-nilai') || '';
                        inputCatatan.value = btn.getAttribute('data-catatan') || '';
                    });
                }
            });
            </script>
        <?php endif; ?>

    </div><!--//container-xl-->
</div><!--//app-content-->

<?= $this->endSection(); ?>
