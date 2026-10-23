<?= $this->extend('sales/layout') ?>

<?= $this->section('content') ?>

<style>
:root {
  --produk-overlay: rgba(0, 0, 0, 0.45);
  --glass-bg: rgba(255, 255, 255, 0.88);
  --glass-border: rgba(255, 255, 255, 0.35);
  --glass-blur: 10px;
}

body {
  background-color: #14532d !important;
  background-image: linear-gradient(var(--produk-overlay), var(--produk-overlay)), url('<?= base_url('assets/img/header.jpeg') ?>') !important;
  background-size: cover !important;
  background-position: center !important;
  background-repeat: no-repeat !important;
  background-attachment: fixed !important;
}

@media (max-width: 767.98px) {
  body {
    background-attachment: scroll !important;
  }
}

.sales-card {
  background: var(--glass-bg) !important;
  border: 1px solid var(--glass-border) !important;
  backdrop-filter: blur(var(--glass-blur)) !important;
  -webkit-backdrop-filter: blur(var(--glass-blur)) !important;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
  color: #1e293b !important;
  border-radius: 12px;
}

.produk-hero {
  color: #ffffff !important;
  text-shadow: 0 1px 6px rgba(0, 0, 0, 0.45);
}

.produk-hero h2 {
  font-weight: 700 !important;
  color: #ffffff !important;
  text-shadow: 0 1px 6px rgba(0, 0, 0, 0.45);
}

.produk-breadcrumb {
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 13px;
  color: #ffffff;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.45);
  margin-bottom: 6px;
}

.produk-breadcrumb strong {
  font-weight: 700;
  color: #ffffff;
}

/* Nav Tabs Custom Styling */
.nav-tabs {
  border-bottom: 2px solid rgba(255, 255, 255, 0.35) !important;
  gap: 8px;
}

.nav-tabs .nav-link {
  color: #ffffff !important;
  font-weight: 600;
  font-size: 14.5px;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
  border: none !important;
  border-radius: 8px 8px 0 0 !important;
  padding: 12px 22px !important;
  background: rgba(0, 0, 0, 0.28) !important;
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  transition: all 0.2s ease;
}

.nav-tabs .nav-link:hover {
  background: rgba(255, 255, 255, 0.25) !important;
  color: #ffffff !important;
}

.nav-tabs .nav-link.active {
  background-color: #ffffff !important;
  color: #15803d !important;
  font-weight: 700 !important;
  border-radius: 8px 8px 0 0 !important;
  text-shadow: none !important;
  border-top: 1px solid var(--glass-border) !important;
  border-left: 1px solid var(--glass-border) !important;
  border-right: 1px solid var(--glass-border) !important;
  border-bottom: 3px solid #16a34a !important;
}

.tab-content {
  background: var(--glass-bg) !important;
  border: 1px solid var(--glass-border) !important;
  border-radius: 0 12px 12px 12px !important;
  padding: 24px !important;
  backdrop-filter: blur(var(--glass-blur)) !important;
  -webkit-backdrop-filter: blur(var(--glass-blur)) !important;
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.12) !important;
}

/* Table styling inside tabs */
.detail-table {
  width: 100%;
  margin-bottom: 0;
}

.detail-table td {
  padding: 11px 14px;
  vertical-align: top;
  border-bottom: 1px solid rgba(226, 232, 240, 0.8);
  font-size: 14px;
}

.detail-table tr:last-child td {
  border-bottom: none;
}

.detail-table .label-col {
  width: 220px;
  color: #64748b;
  font-weight: 600;
}

.detail-table .val-col {
  color: #0f172a;
  font-weight: 500;
}

@media (max-width: 767.98px) {
  .detail-table .label-col {
    width: 130px;
    font-size: 13px;
  }
}
</style>

<!-- Header Title & Back Button (Produk Hero) -->
<div class="produk-hero mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <div class="produk-breadcrumb mb-0">
            <strong>SALES</strong> / <a href="<?= base_url('sales/produk' . ($is_preview ? '?mode=eksternal' : '')) ?>" class="text-white text-decoration-none">PRODUK</a> / <span style="opacity: 0.85;"><?= esc(strtoupper($product['nama_produk'] ?? 'DETAIL PRODUK')) ?></span>
        </div>

        <!-- BADGE & KONTROL MODE -->
        <div class="d-flex align-items-center gap-2">
            <?php if ($is_internal): ?>
                <span class="badge bg-success text-white shadow-sm" style="font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 6px;">
                    <i class="fa-solid fa-shield-halved me-1"></i> Mode: Internal
                </span>
                <?php if ($account_tipe === 'internal'): ?>
                    <a href="<?= base_url('sales/produk/detail/' . esc($id) . '?mode=eksternal') ?>" 
                       class="btn btn-sm btn-outline-light shadow-sm" 
                       style="font-size: 12px; border-radius: 6px; padding: 4px 10px;"
                       title="Lihat detail dalam mode eksternal">
                        <i class="fa-solid fa-eye me-1"></i> Pratinjau Eksternal
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <span class="badge text-white shadow-sm" style="background-color: #ea580c; font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 6px;">
                    <i class="fa-solid fa-globe me-1"></i> Mode: Eksternal<?= $is_preview ? ' (Pratinjau)' : '' ?>
                </span>
                <?php if ($account_tipe === 'internal'): ?>
                    <a href="<?= base_url('sales/produk/detail/' . esc($id)) ?>" 
                       class="btn btn-sm btn-light text-dark fw-semibold shadow-sm" 
                       style="font-size: 12px; border-radius: 6px; padding: 4px 10px;"
                       title="Kembali ke mode internal">
                        <i class="fa-solid fa-arrow-rotate-left me-1"></i> Kembali ke Internal
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h2 class="fw-bold mb-0" style="font-size: 26px;">
                    <?= esc($product['nama_produk']) ?>
                </h2>
                <span class="badge" style="background-color: rgba(220, 252, 231, 0.95); color: #15803d; font-size: 13px; font-weight: 600; padding: 6px 12px; border-radius: 6px;">
                    <i class="fa-solid fa-tag me-1"></i><?= esc($product['kategori_produk']) ?>
                </span>
            </div>
            <p class="mb-0 fs-6 text-white" style="opacity: 0.92;">
                <i class="fa-solid fa-building me-1"></i><?= esc($company['nama_perusahaan']) ?>
                <?php if (!empty($company['domisili']) && $company['domisili'] !== '-'): ?>
                    <span class="mx-2">•</span><i class="fa-solid fa-location-dot me-1"></i><?= esc($company['domisili']) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($account_tipe === 'internal' && !$is_preview): ?>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sales-primary shadow-sm dropdown-toggle" type="button" id="pdfDropdownDetail" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-file-pdf me-1"></i> Cetak PDF
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="pdfDropdownDetail" style="min-width: 290px;">
                        <li>
                            <a class="dropdown-item py-2" href="<?= base_url('sales/produk/detail/' . esc($id) . '/pdf?tipe_pdf=eksternal') ?>" target="_blank">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fw-bold text-dark"><i class="fa-solid fa-file-pdf text-success me-2"></i>PDF Eksternal</span>
                                    <span class="badge bg-success" style="font-size: 10px;">Default</span>
                                </div>
                                <small class="text-muted d-block mt-1">Aman dikirim ke buyer (tanpa data supplier & komisi)</small>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= base_url('sales/produk/detail/' . esc($id) . '/pdf?tipe_pdf=internal') ?>" target="_blank">
                                <div class="fw-bold text-dark"><i class="fa-solid fa-file-shield text-warning me-2"></i>PDF Internal</div>
                                <small class="text-muted d-block mt-1">Lengkap dengan data supplier, legalitas & komisi</small>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?= base_url('sales/produk/detail/' . esc($id) . '/pdf' . ($is_preview ? '?mode=eksternal' : '')) ?>" 
                   target="_blank" 
                   class="btn btn-sales-primary shadow-sm">
                    <i class="fa-solid fa-file-pdf me-1"></i> Cetak PDF
                </a>
            <?php endif; ?>
            <a href="<?= base_url('sales/produk' . ($is_preview ? '?mode=eksternal' : '')) ?>" class="btn btn-sales-secondary shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
</div>

<!-- Navigasi Tiga Tab (Bootstrap Tab tanpa Reload Halaman) -->
<ul class="nav nav-tabs mb-0" id="produkDetailTabs" role="tablist">
    <!-- TAB 1: Data Produk -->
    <li class="nav-item" role="presentation">
        <button class="nav-link active" 
                id="tab-produk-btn" 
                data-bs-toggle="tab" 
                data-bs-target="#tab-produk" 
                type="button" 
                role="tab" 
                aria-controls="tab-produk" 
                aria-selected="true">
            <i class="fa-solid fa-box-open me-2"></i>1. Data Produk
        </button>
    </li>

    <!-- TAB 2: Data Perusahaan -->
    <li class="nav-item" role="presentation">
        <button class="nav-link" 
                id="tab-perusahaan-btn" 
                data-bs-toggle="tab" 
                data-bs-target="#tab-perusahaan" 
                type="button" 
                role="tab" 
                aria-controls="tab-perusahaan" 
                aria-selected="false">
            <i class="fa-solid fa-building me-2"></i>2. Data Perusahaan
        </button>
    </li>

    <!-- TAB 3: Data History Ekspor -->
    <li class="nav-item" role="presentation">
        <button class="nav-link" 
                id="tab-history-btn" 
                data-bs-toggle="tab" 
                data-bs-target="#tab-history" 
                type="button" 
                role="tab" 
                aria-controls="tab-history" 
                aria-selected="false">
            <i class="fa-solid fa-clock-rotate-left me-2"></i>3. Data History Ekspor
            <span class="badge rounded-pill bg-success-subtle text-success ms-1"><?= count($histories) ?></span>
        </button>
    </li>
</ul>

<!-- Konten Tiga Tab -->
<div class="tab-content mb-4" id="produkDetailTabsContent">

    <!-- ==================== TAB 1: DATA PRODUK ==================== -->
    <div class="tab-pane fade show active" id="tab-produk" role="tabpanel" aria-labelledby="tab-produk-btn">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-box-open text-success me-2"></i>Spesifikasi & Detail Produk
            </h5>
            <span class="badge bg-success-subtle text-success border border-success-subtle">Kategori: <?= esc($product['kategori_produk']) ?></span>
        </div>
        <div class="row g-4">
            <!-- Kolom Kiri: Gambar Produk -->
            <div class="col-md-5 col-lg-4 text-center">
                <div class="rounded border p-2 bg-light mb-2 overflow-hidden shadow-sm" style="min-height: 260px; max-height: 320px; display: flex; align-items: center; justify-content: center;">
                    <?php 
                        $prodImgUrl = !empty($product['foto_url']) ? $product['foto_url'] : base_url('assets/img/logo.png');
                        $isDefLogo = empty($product['foto_url']);
                    ?>
                    <img src="<?= esc($prodImgUrl) ?>" 
                         alt="<?= esc($product['nama_produk']) ?>" 
                         class="img-fluid rounded"
                         referrerpolicy="no-referrer"
                         style="max-height: 280px; width: 100%; object-fit: <?= $isDefLogo ? 'contain' : 'cover' ?>; padding: <?= $isDefLogo ? '25px' : '0' ?>;"
                         onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>'; this.style.objectFit='contain'; this.style.padding='25px';">
                </div>
                <?php if (!empty($product['foto_raw']) && filter_var($product['foto_raw'], FILTER_VALIDATE_URL)): ?>
                <a href="<?= esc($product['foto_raw']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary w-100 mt-1" style="font-size: 12.5px;">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka File Gambar Asli
                </a>
                <?php endif; ?>
            </div>

            <!-- Kolom Kanan: Rincian Data Produk -->
            <div class="col-md-7 col-lg-8">
                <div class="table-responsive">
                    <table class="detail-table">
                        <tbody>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-tag me-2 text-muted"></i>Nama Produk</td>
                                <td class="val-col fw-bold text-dark" style="font-size: 15px;"><?= esc($product['nama_produk']) ?></td>
                            </tr>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-layer-group me-2 text-muted"></i>Kategori Produk</td>
                                <td class="val-col"><span class="badge bg-success-subtle text-success"><?= esc($product['kategori_produk']) ?></span></td>
                            </tr>
                            <?php if (!empty($product['merk_dagang']) && $product['merk_dagang'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-trademark me-2 text-muted"></i>Merk Dagang</td>
                                <td class="val-col fw-semibold text-primary"><?= esc($product['merk_dagang']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-industry me-2 text-muted"></i>Kapasitas Produksi</td>
                                <td class="val-col fw-medium"><?= esc($product['kapasitas_produksi']) ?></td>
                            </tr>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-align-left me-2 text-muted"></i>Deskripsi Produk</td>
                                <td class="val-col" style="line-height: 1.6;"><?= nl2br(esc($product['deskripsi'])) ?></td>
                            </tr>
                            <?php if (!empty($product['keunggulan']) && $product['keunggulan'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-star me-2 text-warning"></i>Keunggulan Produk</td>
                                <td class="val-col"><?= esc($product['keunggulan']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['legalitas']) && $product['legalitas'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-file-shield me-2 text-muted"></i>Legalitas Produk</td>
                                <td class="val-col"><?= esc($product['legalitas']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['sertifikasi']) && $product['sertifikasi'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-certificate me-2 text-muted"></i>Sertifikasi Produk</td>
                                <td class="val-col"><?= esc($product['sertifikasi']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['harga_moq']) && $product['harga_moq'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-money-bill-wave me-2 text-muted"></i>Harga / MOQ</td>
                                <td class="val-col fw-semibold"><?= esc($product['harga_moq']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['komisi']) && $product['komisi'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-percent me-2 text-success"></i>Komisi Sales</td>
                                <td class="val-col fw-bold text-success"><?= esc($product['komisi']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['pernah_ekspor']) && $product['pernah_ekspor'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-plane-departure me-2 text-muted"></i>Pengalaman Ekspor</td>
                                <td class="val-col fw-semibold text-primary"><?= esc($product['pernah_ekspor']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 2: DATA PERUSAHAAN ==================== -->
    <div class="tab-pane fade" id="tab-perusahaan" role="tabpanel" aria-labelledby="tab-perusahaan-btn">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-id-card text-success me-2"></i>Profil & Kontak Perusahaan
            </h5>
        </div>

        <div class="row g-4">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="detail-table">
                        <tbody>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-building me-2 text-muted"></i>Nama Perusahaan</td>
                                <td class="val-col fw-bold text-success" style="font-size: 15px;"><?= esc($company['nama_perusahaan']) ?></td>
                            </tr>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-location-dot me-2 text-muted"></i>Alamat / Domisili</td>
                                <td class="val-col"><?= esc($company['domisili']) ?></td>
                            </tr>
                            <?php if (!empty($company['wilayah_ecp']) && $company['wilayah_ecp'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-map-location-dot me-2 text-muted"></i>Wilayah Program ECP</td>
                                <td class="val-col"><?= esc($company['wilayah_ecp']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($company['lokasi_pabrik']) && $company['lokasi_pabrik'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-warehouse me-2 text-muted"></i>Lokasi Pabrik / Gudang</td>
                                <td class="val-col"><?= esc($company['lokasi_pabrik']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-user-tie me-2 text-muted"></i>Kontak / PIC</td>
                                <td class="val-col fw-semibold"><?= esc($company['nama_pic']) ?></td>
                            </tr>
                            <tr>
                                <td class="label-col"><i class="fa-brands fa-whatsapp me-2 text-success"></i>No. HP / WhatsApp</td>
                                <td class="val-col">
                                    <?php if (!empty($company['no_hp']) && $company['no_hp'] !== '-'): ?>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $company['no_hp']) ?>" target="_blank" class="text-success text-decoration-none fw-semibold">
                                             <i class="fa-brands fa-whatsapp me-1"></i><?= esc($company['no_hp']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col"><i class="fa-regular fa-envelope me-2 text-muted"></i>Email</td>
                                <td class="val-col">
                                    <?php if (!empty($company['email']) && $company['email'] !== '-'): ?>
                                        <a href="mailto:<?= esc($company['email']) ?>" class="text-primary text-decoration-none">
                                            <?= esc($company['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-globe me-2 text-muted"></i>Website</td>
                                <td class="val-col">
                                    <?php if (!empty($company['website']) && $company['website'] !== '-'): ?>
                                        <a href="<?= str_starts_with($company['website'], 'http') ? esc($company['website']) : 'https://' . esc($company['website']) ?>" target="_blank" class="text-primary text-decoration-none">
                                            <?= esc($company['website']) ?> <i class="fa-solid fa-arrow-up-right-from-square small ms-1"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col"><i class="fa-brands fa-instagram me-2 text-danger"></i>Instagram</td>
                                <td class="val-col">
                                    <?php if (!empty($company['instagram']) && $company['instagram'] !== '-'): ?>
                                        <span class="text-dark fw-medium"><?= esc($company['instagram']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-briefcase me-2 text-muted"></i>Jenis Perusahaan</td>
                                <td class="val-col"><?= esc($company['jenis_perusahaan']) ?></td>
                            </tr>
                            <?php if (!empty($company['omset']) && $company['omset'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-chart-line me-2 text-muted"></i>Omset</td>
                                <td class="val-col fw-semibold text-primary">Rp <?= esc($company['omset']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($company['jumlah_pegawai']) && $company['jumlah_pegawai'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-users me-2 text-muted"></i>Jumlah Pegawai</td>
                                <td class="val-col"><?= esc($company['jumlah_pegawai']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($company['legalitas']) && $company['legalitas'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-file-shield me-2 text-muted"></i>Legalitas Usaha</td>
                                <td class="val-col"><?= esc($company['legalitas']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($company['sertifikasi']) && $company['sertifikasi'] !== '-'): ?>
                            <tr>
                                <td class="label-col"><i class="fa-solid fa-certificate me-2 text-muted"></i>Sertifikasi</td>
                                <td class="val-col"><?= esc($company['sertifikasi']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 3: DATA HISTORY EKSPOR ==================== -->
    <div class="tab-pane fade" id="tab-history" role="tabpanel" aria-labelledby="tab-history-btn">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <div>
                <h5 class="fw-bold mb-1 text-dark">
                    <i class="fa-solid fa-ship text-success me-2"></i>Riwayat Ekspor Perusahaan
                </h5>
                <p class="text-muted small mb-0">Catatan transaksi ekspor dari perusahaan <strong><?= esc($company['nama_perusahaan']) ?></strong>.</p>
            </div>
            <?php if (!empty($histories)): ?>
            <div class="text-end">
                <span class="badge bg-success-subtle text-success p-2 fs-6">
                    Total: $<?= number_format($totalNilai, 0, ',', '.') ?> USD
                </span>
            </div>
            <?php endif; ?>
        </div>

        <?php if (empty($histories)): ?>
            <!-- Jika kosong: Belum ada data history ekspor -->
            <div class="sales-card text-center py-5 my-3" style="background: rgba(255,255,255,0.7) !important; border: 1px dashed #cbd5e1;">
                <div class="mb-3">
                    <i class="fa-solid fa-boxes-packing text-muted" style="font-size: 42px;"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Belum ada data history ekspor</h6>
                <p class="text-muted small mb-0">Belum ada catatan riwayat ekspor untuk perusahaan ini pada database sistem.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive rounded border bg-white shadow-sm">
                <table class="table table-hover table-striped mb-0 align-middle" style="font-size: 13.5px;">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 80px;">Tahun</th>
                            <th>Produk Ekspor</th>
                            <th>Negara Tujuan</th>
                            <th>Jenis Ekspor</th>
                            <th>Volume / Kuantitas</th>
                            <th class="text-end" style="width: 150px;">Nilai Ekspor (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($histories as $h): ?>
                        <tr>
                            <td class="text-center fw-medium text-muted"><?= $no++ ?></td>
                            <td><span class="badge bg-secondary"><?= esc($h['tahun'] ?? '-') ?></span></td>
                            <td class="fw-semibold text-dark"><?= esc($h['produk']) ?></td>
                            <td>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                    <i class="fa-solid fa-earth-americas me-1"></i><?= esc($h['negara_tujuan']) ?>
                                </span>
                            </td>
                            <td><?= esc($h['jenis_ekspor']) ?></td>
                            <td><?= esc($h['kuantitas']) ?></td>
                            <td class="text-end fw-bold text-success">
                                <?php if (!empty($h['nilai_ekspor_usd'])): ?>
                                    $<?= number_format($h['nilai_ekspor_usd'], 2, ',', '.') ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light border-top fw-bold">
                        <tr>
                            <td colspan="6" class="text-end">Total Nilai Ekspor:</td>
                            <td class="text-end text-success">$<?= number_format($totalNilai, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?= $this->endSection() ?>
