<?= $this->extend('supplier/layout') ?>

<?= $this->section('content') ?>

<style>
    .profile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }
    .profile-section-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .profile-section-title i {
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
    .danger-zone-card {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 12px;
        padding: 24px;
        margin-top: 36px;
        margin-bottom: 24px;
    }
</style>

<!-- Flash Alerts -->
<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="fa-solid fa-circle-check fs-5"></i>
        <div><?= esc(session()->getFlashdata('success')) ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="fa-solid fa-circle-exclamation fs-5"></i>
        <div><?= session()->getFlashdata('error') ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($isNew) : ?>
    <div class="alert alert-warning border-warning d-flex align-items-center gap-3 p-3 mb-4 shadow-sm" style="background-color: #fffbeb;">
        <i class="fa-solid fa-building-circle-exclamation fs-3 text-warning"></i>
        <div>
            <h6 class="fw-bold mb-1 text-dark">Akun Anda Belum Terhubung ke Profil Perusahaan</h6>
            <p class="mb-0 small text-muted">
                Silakan lengkapi formulir di bawah ini dengan data perusahaan Anda untuk mengaktifkan akses fitur Katalog Produk dan History Ekspor.
            </p>
        </div>
    </div>
<?php endif; ?>

<form action="<?= base_url('supplier/perusahaan/simpan') ?>" method="POST" autocomplete="off">
    <?= csrf_field() ?>

    <!-- 1. INFORMASI UTAMA PERUSAHAAN -->
    <div class="profile-card">
        <div class="profile-section-title">
            <i class="fa-solid fa-building"></i>
            <span>Informasi Utama Perusahaan</span>
        </div>

        <div class="row g-3">
            <div class="col-md-8">
                <label for="nama_perusahaan" class="form-label">
                    Nama Perusahaan <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       class="form-control" 
                       id="nama_perusahaan" 
                       name="nama_perusahaan" 
                       maxlength="255"
                       placeholder="Contoh: PT Sumber Pangan Nusantara / CV Arva Indonesia" 
                       value="<?= esc(old('nama_perusahaan', $perusahaan['nama_perusahaan'] ?? '')) ?>" 
                       required>
                <div class="form-text">Nama resmi perusahaan atau badan usaha Anda.</div>
            </div>

            <div class="col-md-4">
                <label for="jenis_perusahaan" class="form-label">Jenis Perusahaan</label>
                <select class="form-select" id="jenis_perusahaan" name="jenis_perusahaan">
                    <?php 
                        $currentJenis = old('jenis_perusahaan', $perusahaan['jenis_perusahaan'] ?? '');
                        $jenisOptions = ['Produsen','Trader'];
                    ?>
                    <option value="">-- Pilih Jenis --</option>
                    <?php foreach ($jenisOptions as $opt) : ?>
                        <option value="<?= $opt ?>" <?= $currentJenis === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="domisili_usaha" class="form-label">Domisili Usaha (Kota / Provinsi)</label>
                <input type="text" 
                       class="form-control" 
                       id="domisili_usaha" 
                       name="domisili_usaha" 
                       placeholder="Contoh: Surabaya, Jawa Timur" 
                       value="<?= esc(old('domisili_usaha', $perusahaan['domisili_usaha'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
                <label for="lokasi_pabrik_gudang" class="form-label">Lokasi Pabrik / Gudang</label>
                <input type="text" 
                       class="form-control" 
                       id="lokasi_pabrik_gudang" 
                       name="lokasi_pabrik_gudang" 
                       placeholder="Contoh: Kawasan Industri Rungkut, Surabaya" 
                       value="<?= esc(old('lokasi_pabrik_gudang', $perusahaan['lokasi_pabrik_gudang'] ?? '')) ?>">
            </div>

            <div class="col-12">
                <label for="produk_dipasarkan" class="form-label">Produk yang Dipasarkan</label>
                <textarea class="form-control" 
                          id="produk_dipasarkan" 
                          name="produk_dipasarkan" 
                          rows="2" 
                          placeholder="Jelaskan jenis komoditas atau kelompok produk utama yang dihasilkan"><?= esc(old('produk_dipasarkan', $perusahaan['produk_dipasarkan'] ?? '')) ?></textarea>
            </div>
        </div>
    </div>

    <!-- 2. KAPASITAS & OPERASIONAL -->
    <div class="profile-card">
        <div class="profile-section-title">
            <i class="fa-solid fa-industry"></i>
            <span>Kapasitas Produksi & Operasional</span>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="kapasitas_produksi" class="form-label">Kapasitas Produksi</label>
                <input type="text" 
                       class="form-control" 
                       id="kapasitas_produksi" 
                       name="kapasitas_produksi" 
                       placeholder="Contoh: 50 Ton / Bulan" 
                       value="<?= esc(old('kapasitas_produksi', $perusahaan['kapasitas_produksi'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
                <label for="jumlah_pegawai" class="form-label">Jumlah Pegawai</label>
                <input type="text" 
                       class="form-control" 
                       id="jumlah_pegawai" 
                       name="jumlah_pegawai" 
                       placeholder="Contoh: 50 - 100 Orang" 
                       value="<?= esc(old('jumlah_pegawai', $perusahaan['jumlah_pegawai'] ?? '')) ?>">
            </div>

            <div class="col-12">
                <label for="keunggulan_produk" class="form-label">Keunggulan Produk / Kelebihan Usaha</label>
                <textarea class="form-control" 
                          id="keunggulan_produk" 
                          name="keunggulan_produk" 
                          rows="2" 
                          placeholder="Jelaskan keunggulan kompetitif, standar mutu, atau diferensiasi produk Anda"><?= esc(old('keunggulan_produk', $perusahaan['keunggulan_produk'] ?? '')) ?></textarea>
            </div>
        </div>
    </div>

    <!-- 3. KONTAK & MEDIA SOSIAL -->
    <div class="profile-card">
        <div class="profile-section-title">
            <i class="fa-solid fa-address-book"></i>
            <span>Kontak & Media Sosial</span>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label for="nama_kontak" class="form-label">Nama Kontak (PIC)</label>
                <input type="text" 
                       class="form-control" 
                       id="nama_kontak" 
                       name="nama_kontak" 
                       placeholder="Nama penanggung jawab" 
                       value="<?= esc(old('nama_kontak', $perusahaan['nama_kontak'] ?? '')) ?>">
            </div>

            <div class="col-md-4">
                <label for="email" class="form-label">Email Perusahaan</label>
                <input type="email" 
                       class="form-control" 
                       id="email" 
                       name="email" 
                       placeholder="info@perusahaan.com" 
                       value="<?= esc(old('email', $perusahaan['email'] ?? '')) ?>">
            </div>

            <div class="col-md-4">
                <label for="no_hp" class="form-label">Nomor WhatsApp / HP</label>
                <input type="text" 
                       class="form-control" 
                       id="no_hp" 
                       name="no_hp" 
                       placeholder="081234567890" 
                       value="<?= esc(old('no_hp', $perusahaan['no_hp'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
                <label for="website" class="form-label">Website Perusahaan</label>
                <input type="text" 
                       class="form-control" 
                       id="website" 
                       name="website" 
                       placeholder="https://www.perusahaan.com" 
                       value="<?= esc(old('website', $perusahaan['website'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
                <label for="instagram" class="form-label">Akun Instagram</label>
                <input type="text" 
                       class="form-control" 
                       id="instagram" 
                       name="instagram" 
                       placeholder="@nama_perusahaan" 
                       value="<?= esc(old('instagram', $perusahaan['instagram'] ?? '')) ?>">
            </div>
        </div>
    </div>

    <!-- 4. LEGALITAS & SERTIFIKASI -->
    <div class="profile-card">
        <div class="profile-section-title">
            <i class="fa-solid fa-certificate"></i>
            <span>Legalitas & Sertifikasi</span>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="legalitas" class="form-label">Legalitas Usaha</label>
                <textarea class="form-control" 
                          id="legalitas" 
                          name="legalitas" 
                          rows="2" 
                          placeholder="Contoh: NIB, NPWP, SIUP, TDP, IUI"><?= esc(old('legalitas', $perusahaan['legalitas'] ?? '')) ?></textarea>
            </div>

            <div class="col-md-6">
                <label for="sertifikasi" class="form-label">Sertifikasi Produk & Fasilitas</label>
                <textarea class="form-control" 
                          id="sertifikasi" 
                          name="sertifikasi" 
                          rows="2" 
                          placeholder="Contoh: Halal MUI, BPOM, HACCP, ISO 22000, FDA"><?= esc(old('sertifikasi', $perusahaan['sertifikasi'] ?? '')) ?></textarea>
            </div>

            <div class="col-12">
                <label for="pameran_internasional" class="form-label">Pameran Internasional</label>
                <input type="text" 
                       class="form-control" 
                       id="pameran_internasional" 
                       name="pameran_internasional" 
                       placeholder="Contoh: Trade Expo Indonesia, Gulfood" 
                       value="<?= esc(old('pameran_internasional', $perusahaan['pameran_internasional'] ?? '')) ?>">
            </div>
        </div>
    </div>

    <!-- 5. TAUTAN DOKUMEN & FOTO -->
    <div class="profile-card">
        <div class="profile-section-title">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>Tautan Dokumen & Galeri (Cloud / Drive)</span>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <label for="link_foto_usaha" class="form-label">Link Foto Usaha / Pabrik</label>
                <input type="text" 
                       class="form-control" 
                       id="link_foto_usaha" 
                       name="link_foto_usaha" 
                       placeholder="Link Google Drive / Dropbox galeri foto fasilitas atau usaha" 
                       value="<?= esc(old('link_foto_usaha', $perusahaan['link_foto_usaha'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
                <label for="link_dokumen_legalitas" class="form-label">Link Dokumen Legalitas</label>
                <input type="text" 
                       class="form-control" 
                       id="link_dokumen_legalitas" 
                       name="link_dokumen_legalitas" 
                       placeholder="Link cloud dokumen legalitas perusahaan" 
                       value="<?= esc(old('link_dokumen_legalitas', $perusahaan['link_dokumen_legalitas'] ?? '')) ?>">
            </div>

            <div class="col-md-6">
                <label for="link_dokumen_sertifikat" class="form-label">Link Dokumen Sertifikat</label>
                <input type="text" 
                       class="form-control" 
                       id="link_dokumen_sertifikat" 
                       name="link_dokumen_sertifikat" 
                       placeholder="Link cloud dokumen sertifikasi produk" 
                       value="<?= esc(old('link_dokumen_sertifikat', $perusahaan['link_dokumen_sertifikat'] ?? '')) ?>">
            </div>
        </div>
    </div>

    <!-- Tombol Simpan -->
    <div class="d-flex align-items-center gap-2 mb-4">
        <button type="submit" class="btn btn-sales-primary text-white px-4 py-2">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            <?= $isNew ? 'Simpan & Lengkapi Profil Perusahaan' : 'Simpan Perubahan Profil' ?>
        </button>
        <a href="<?= base_url('supplier/dashboard') ?>" class="btn btn-sales-secondary">
            Batal
        </a>
    </div>
</form>

<!-- ZONA BAHAYA (HANYA MUNCUL DI MODE EDIT / SUDAH PUNYA PERUSAHAAN) -->
<?php if (!$isNew) : ?>
    <div class="danger-zone-card shadow-sm">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h5 class="fw-bold text-danger mb-1">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Zona Bahaya: Hapus Perusahaan
                </h5>
                <p class="text-muted small mb-0">
                    Menghapus profil perusahaan ini akan menghapus seluruh data produk, file foto, serta history ekspor terkait secara permanen dari sistem.
                </p>
            </div>
            <div>
                <button type="button" class="btn btn-outline-danger px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#deleteCompanyModal">
                    <i class="fa-solid fa-trash me-1"></i> Hapus Perusahaan
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus Dua Langkah -->
    <div class="modal fade" id="deleteCompanyModal" tabindex="-1" aria-labelledby="deleteCompanyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title fs-6 fw-bold" id="deleteCompanyModalLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Konfirmasi Hapus Perusahaan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= base_url('supplier/perusahaan/hapus') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-body p-4">
                        <div class="alert alert-danger py-2 px-3 small mb-3">
                            <i class="fa-solid fa-circle-exclamation me-1"></i>
                            <strong>Peringatan Penting:</strong> Semua produk, foto, dan history ekspor perusahaan ini ikut terhapus permanen. Tindakan ini tidak dapat dibatalkan.
                        </div>
                        
                        <p class="small text-dark mb-2">
                            Untuk mengonfirmasi penghapusan, silakan ketik nama perusahaan persis seperti di bawah:
                        </p>
                        <div class="p-2 rounded bg-light border text-center mb-3">
                            <code class="fw-bold fs-6 text-danger" id="targetCompanyName"><?= esc($perusahaan['nama_perusahaan'] ?? '') ?></code>
                        </div>

                        <div class="mb-3">
                            <label for="confirmCompanyNameInput" class="form-label small fw-semibold">Ketik nama perusahaan:</label>
                            <input type="text" 
                                   name="confirm_nama_perusahaan" 
                                   id="confirmCompanyNameInput" 
                                   class="form-control" 
                                   autocomplete="off" 
                                   placeholder="Ketik persis nama perusahaan di sini">
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btnSubmitDeleteCompany" class="btn btn-sm btn-danger text-white" disabled>
                            <i class="fa-solid fa-trash me-1"></i> Saya Yakin Hapus Perusahaan Ini
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var confirmInput = document.getElementById('confirmCompanyNameInput');
        var targetEl = document.getElementById('targetCompanyName');
        var submitBtn = document.getElementById('btnSubmitDeleteCompany');

        if (confirmInput && targetEl && submitBtn) {
            var targetText = targetEl.textContent.trim();
            confirmInput.addEventListener('input', function() {
                if (this.value.trim() === targetText) {
                    submitBtn.removeAttribute('disabled');
                } else {
                    submitBtn.setAttribute('disabled', 'disabled');
                }
            });
        }
    });
    </script>
<?php endif; ?>

<?= $this->endSection() ?>
