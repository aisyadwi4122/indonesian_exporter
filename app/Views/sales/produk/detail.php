<?= $this->extend('sales/layout') ?>

<?= $this->section('content') ?>

<style>
:root {
  --produk-overlay: rgba(0, 0, 0, 0.45);
  --glass-bg: rgba(255, 255, 255, 0.82);
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

.sales-topbar {
  background: rgba(255, 255, 255, 0.80) !important;
  backdrop-filter: blur(8px) !important;
  -webkit-backdrop-filter: blur(8px) !important;
}

.sales-sidebar {
  background: rgba(255, 255, 255, 0.88) !important;
  backdrop-filter: blur(8px) !important;
  -webkit-backdrop-filter: blur(8px) !important;
}

.sales-card {
  background: var(--glass-bg) !important;
  border: 1px solid var(--glass-border) !important;
  backdrop-filter: blur(var(--glass-blur)) !important;
  -webkit-backdrop-filter: blur(var(--glass-blur)) !important;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
  color: #1e293b !important;
}

.sales-card table {
  --bs-table-bg: transparent !important;
  --bs-table-striped-bg: rgba(0, 0, 0, 0.03) !important;
  background-color: transparent !important;
}

.sales-card .form-control,
.sales-card .form-select,
.sales-card .input-group-text {
  background: rgba(255, 255, 255, 0.92) !important;
}

.sales-card .btn-sales-secondary {
  background: rgba(255, 255, 255, 0.92) !important;
}

.pagination .page-link {
  background: rgba(255, 255, 255, 0.9) !important;
  border-color: rgba(226, 232, 240, 0.8) !important;
  color: #1e293b !important;
}

.pagination .page-item.active .page-link {
  background: #16a34a !important;
  border-color: #16a34a !important;
  color: #ffffff !important;
}

/* Produk Hero & Breadcrumb */
.produk-hero {
  color: #ffffff !important;
  text-shadow: 0 1px 6px rgba(0, 0, 0, 0.45);
}

.produk-hero h2 {
  font-weight: 700 !important;
  color: #ffffff !important;
  text-shadow: 0 1px 6px rgba(0, 0, 0, 0.45);
}

.produk-hero p {
  color: #ffffff !important;
  opacity: 0.9;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
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

/* Fallback jika backdrop-filter tidak didukung */
@supports not ((-webkit-backdrop-filter: blur(10px)) or (backdrop-filter: blur(10px))) {
  .sales-card {
    background: rgba(255, 255, 255, 0.95) !important;
  }
  .sales-topbar {
    background: rgba(255, 255, 255, 0.95) !important;
  }
  .sales-sidebar {
    background: rgba(255, 255, 255, 0.96) !important;
  }
  .tab-content {
    background: rgba(255, 255, 255, 0.95) !important;
  }
}

/* Detail Page Specific Styling */
.tab-content {
  background: var(--glass-bg) !important;
  border: 1px solid var(--glass-border) !important;
  border-radius: 12px !important;
  padding: 24px !important;
  backdrop-filter: blur(var(--glass-blur)) !important;
  -webkit-backdrop-filter: blur(var(--glass-blur)) !important;
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.10) !important;
}

.nav-tabs {
  border-bottom: 2px solid rgba(255, 255, 255, 0.35) !important;
  gap: 8px;
}

.nav-tabs .nav-link {
  color: #ffffff !important;
  font-weight: 500;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
  border: none !important;
  border-radius: 8px 8px 0 0 !important;
  padding: 12px 20px !important;
  background: rgba(0, 0, 0, 0.25) !important;
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
  color: #1e293b !important;
  font-weight: 600 !important;
  border-radius: 8px 8px 0 0 !important;
  text-shadow: none !important;
  border-top: 1px solid var(--glass-border) !important;
  border-left: 1px solid var(--glass-border) !important;
  border-right: 1px solid var(--glass-border) !important;
  border-bottom: 3px solid #16a34a !important;
}

.nav-tabs .nav-link.active i {
  color: #16a34a !important;
}

.nav-tabs .nav-link:not(.active) i {
  color: #dcfce7 !important;
}

.label-col {
  color: #475569;
  font-size: 13.5px;
  font-weight: 500;
}

.value-col {
  color: #1e293b;
  font-size: 14px;
  font-weight: 600;
}

.info-row {
  padding: 10px 0;
  border-bottom: 1px solid rgba(226, 232, 240, 0.7);
}

.info-row:last-child {
  border-bottom: none;
}

@media (max-width: 767.98px) {
  .profil-usaha-eksternal .col-md-6:first-child .info-row:last-child {
    border-bottom: 1px solid rgba(226, 232, 240, 0.7);
  }
}

.desc-content {
  background-color: rgba(255, 255, 255, 0.65) !important;
  border: 1px solid rgba(226, 232, 240, 0.8);
  border-radius: 8px;
  padding: 16px;
  font-size: 13.5px;
  line-height: 1.65;
  color: #334155;
}

.desc-content table {
  width: 100%;
  margin-bottom: 1rem;
  border-collapse: collapse;
}

.desc-content th, .desc-content td {
  padding: 6px 10px;
  border: 1px solid #cbd5e1;
}

/* ================= IMAGE MODAL CUSTOM STYLING ================= */
#imageModal {
  position: fixed !important;
  inset: 0 !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  height: 100dvh !important;
  z-index: 99999 !important;
  background-color: rgba(15, 23, 42, 0.82) !important;
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  outline: 0;
  padding: 16px;
  overflow-x: hidden;
  overflow-y: auto;
  overscroll-behavior: contain;
  box-sizing: border-box;
  margin: 0;
  display: none;
  align-items: center;
  justify-content: center;
}
#imageModal.show {
  display: flex !important;
}
#imageModal .modal-dialog {
  max-width: 740px;
  width: 100%;
  margin: auto;
  pointer-events: auto;
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 32px);
  max-height: calc(100dvh - 32px);
}
#imageModal .modal-dialog::before {
  display: none !important;
  content: none !important;
}
#imageModal .modal-content {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.45) !important;
  background-color: #0b1120;
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 32px);
  max-height: calc(100dvh - 32px);
  width: 100%;
}
#imageModal .modal-header {
  background-color: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  padding: 12px 18px;
  flex-shrink: 0;
}
.btn-close-modal-img {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 1px solid #cbd5e1;
  background-color: #f1f5f9;
  color: #1e293b;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}
.btn-close-modal-img:hover {
  background-color: #ef4444;
  color: #ffffff;
  border-color: #ef4444;
  transform: scale(1.08);
  box-shadow: 0 2px 6px rgba(239, 68, 68, 0.35);
}
#imageModal .modal-body {
  background-color: #0b1120;
  position: relative;
  padding: 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-overflow-scrolling: touch;
  cursor: zoom-out;
}
#imageModal .modal-body img {
  display: block;
  max-width: 100%;
  height: auto;
  max-height: 100%;
  object-fit: contain;
  border-radius: 6px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
  transition: transform 0.2s ease;
  margin: auto;
}
#imageModal .modal-body img:hover {
  transform: scale(1.01);
}
.modal-img-click-hint {
  position: relative;
  margin-top: 10px;
  margin-bottom: 0;
  background: rgba(15, 23, 42, 0.78);
  color: #cbd5e1;
  font-size: 11.5px;
  padding: 4px 14px;
  border-radius: 9999px;
  pointer-events: none;
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  white-space: normal;
  text-align: center;
  max-width: 90%;
  flex-shrink: 0;
}
#imageModal .modal-footer {
  background-color: #ffffff;
  border-top: 1px solid #e2e8f0;
  padding: 10px 18px;
  flex-shrink: 0;
}

/* Responsif Tablet (<= 768px) */
@media (max-width: 768px) {
  #imageModal {
    padding: 12px;
  }
  #imageModal .modal-dialog {
    max-height: calc(100vh - 24px);
    max-height: calc(100dvh - 24px);
  }
  #imageModal .modal-content {
    max-height: calc(100vh - 24px);
    max-height: calc(100dvh - 24px);
  }
  #imageModal .modal-header {
    padding: 10px 14px;
  }
  #imageModal .modal-title {
    font-size: 15px;
  }
  #imageModal .modal-footer {
    padding: 8px 14px;
  }
}

/* Responsif Mobile (<= 576px) */
@media (max-width: 576px) {
  #imageModal {
    padding: 8px;
  }
  #imageModal .modal-dialog {
    max-height: calc(100vh - 16px);
    max-height: calc(100dvh - 16px);
  }
  #imageModal .modal-content {
    border-radius: 10px;
    max-height: calc(100vh - 16px);
    max-height: calc(100dvh - 16px);
  }
  #imageModal .modal-header {
    padding: 8px 12px;
  }
  #imageModal .modal-title {
    font-size: 13.5px;
  }
  .btn-close-modal-img {
    width: 32px;
    height: 32px;
    font-size: 14px;
  }
  #imageModal .modal-body {
    padding: 8px;
  }
  .modal-img-click-hint {
    font-size: 10.5px;
    padding: 3px 10px;
  }
  #imageModal .modal-footer {
    padding: 8px 12px;
  }
  #imageModalCloseFooterBtn {
    padding: 6px 14px !important;
    font-size: 13px !important;
  }
}
</style>

<?php
// Normalisasi data kontak untuk mode internal (HANYA saat $is_internal === true)
$rawHp = trim((string)($supplier['no_hp'] ?? ''));
$normalizedHp = null;
if ($is_internal && $rawHp !== '') {
    $digits = preg_replace('/\D+/', '', $rawHp);
    if ($digits !== '') {
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }
        if (strlen($digits) >= 9) {
            $normalizedHp = $digits;
        }
    }
}

$rawWeb = trim((string)($supplier['website'] ?? ''));
$webHref = null;
if ($is_internal && $rawWeb !== '') {
    if (preg_match('#^https?://#i', $rawWeb)) {
        $webHref = $rawWeb;
    } elseif (!preg_match('#^[a-z0-9+.-]+://#i', $rawWeb) && !str_starts_with(strtolower($rawWeb), 'javascript:')) {
        if (!preg_match('#\s#', $rawWeb)) {
            $webHref = 'https://' . $rawWeb;
        }
    }
}

$rawIg = trim((string)($supplier['instagram'] ?? ''));
$igHref = null;
if ($is_internal && $rawIg !== '') {
    if (preg_match('#^https?://#i', $rawIg)) {
        $igHref = $rawIg;
    } elseif (preg_match('/^@?([a-zA-Z0-9._]+)$/', $rawIg, $mIg)) {
        $igHref = 'https://instagram.com/' . $mIg[1];
    }
}
?>

<!-- Header Title & Back Button (Produk Hero) -->
<div class="produk-hero mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <div class="produk-breadcrumb mb-0">
            <strong>SALES</strong> / <a href="<?= base_url('sales/produk' . ($is_preview ? '?mode=eksternal' : '')) ?>" class="text-white text-decoration-none">PRODUK</a> / <span style="opacity: 0.85;"><?= esc(strtoupper($product['nama_produk_id'] ?? 'DETAIL PRODUK')) ?></span>
        </div>

        <!-- BADGE & KONTROL MODE -->
        <div class="d-flex align-items-center gap-2">
            <?php if ($is_internal): ?>
                <span class="badge bg-success text-white shadow-sm" style="font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 6px;">
                    <i class="fa-solid fa-shield-halved me-1"></i> Mode: Internal
                </span>
                <?php if ($account_tipe === 'internal'): ?>
                    <a href="<?= base_url('sales/produk/' . esc($product['id_produk'] ?? '') . '?mode=eksternal') ?>" 
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
                    <a href="<?= base_url('sales/produk/' . esc($product['id_produk'] ?? '')) ?>" 
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
                    <?= esc($product['nama_produk_id'] ?? '-') ?>
                </h2>
                <?php 
                    $kategori = !empty($product['nama_kategori_id']) 
                        ? $product['nama_kategori_id'] 
                        : (!empty($product['nama_kategori_en']) ? $product['nama_kategori_en'] : 'Umum');
                ?>
                <span class="badge" style="background-color: rgba(220, 252, 231, 0.95); color: #15803d; font-size: 13px; font-weight: 600; padding: 6px 12px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">
                    <i class="fa-solid fa-tag me-1"></i><?= esc($kategori) ?>
                </span>
            </div>
            <?php if ($is_internal && !empty($product['nama_produk_en'])): ?>
                <p class="mb-0" style="font-size: 14.5px;">
                    <i class="fa-solid fa-building me-1 text-light opacity-75"></i><?= esc($product['nama_produk_en']) ?>
                </p>
            <?php else: ?>
                <p class="mb-0" style="font-size: 14.5px; opacity: 0.9;">
                    <i class="fa-solid fa-barcode me-1"></i>Kode Produk: <?= esc($product['kode_produk'] ?? '') ?>
                </p>
            <?php endif; ?>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($account_tipe === 'internal' && !$is_preview): ?>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sales-primary shadow-sm dropdown-toggle" type="button" id="pdfDropdownDetail" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-file-pdf me-1"></i> Cetak PDF
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="pdfDropdownDetail" style="min-width: 290px;">
                        <li>
                            <a class="dropdown-item py-2" href="<?= base_url('sales/produk/' . esc($product['id_produk'] ?? '') . '/pdf?tipe_pdf=eksternal') ?>" target="_blank">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fw-bold text-dark"><i class="fa-solid fa-file-pdf text-success me-2"></i>PDF Eksternal</span>
                                    <span class="badge bg-success" style="font-size: 10px;">Default</span>
                                </div>
                                <small class="text-muted d-block mt-1">Aman dikirim ke buyer (tanpa data supplier & komisi)</small>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= base_url('sales/produk/' . esc($product['id_produk'] ?? '') . '/pdf?tipe_pdf=internal') ?>" target="_blank">
                                <div class="fw-bold text-dark"><i class="fa-solid fa-file-shield text-warning me-2"></i>PDF Internal</div>
                                <small class="text-muted d-block mt-1">Lengkap dengan data supplier, legalitas & komisi</small>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?= base_url('sales/produk/' . esc($product['id_produk'] ?? '') . '/pdf' . ($is_preview ? '?mode=eksternal' : '')) ?>" 
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

<!-- Nav Tabs -->
<ul class="nav nav-tabs mb-4" id="detailTabs" role="tablist" style="border-bottom: 2px solid #e2e8f0;">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold detail-tab-btn" 
                id="tab-produk-btn" 
                data-bs-toggle="tab" 
                data-bs-target="#tab-produk" 
                type="button" 
                role="tab" 
                aria-controls="tab-produk" 
                aria-selected="true" 
                style="padding: 12px 20px; font-size: 14.5px;">
            <i class="fa-solid fa-box-open me-2 text-success"></i>Data Produk
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold detail-tab-btn" 
                id="tab-perusahaan-btn" 
                data-bs-toggle="tab" 
                data-bs-target="#tab-perusahaan" 
                type="button" 
                role="tab" 
                aria-controls="tab-perusahaan" 
                aria-selected="false" 
                style="padding: 12px 20px; font-size: 14.5px;">
            <?php if ($is_internal): ?>
                <i class="fa-solid fa-building me-2 text-success"></i>Data Perusahaan
            <?php else: ?>
                <i class="fa-solid fa-briefcase me-2 text-success"></i>Profil Usaha
            <?php endif; ?>
        </button>
    </li>
    <?php if ($is_internal): ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold detail-tab-btn" 
                id="tab-history-btn" 
                data-bs-toggle="tab" 
                data-bs-target="#tab-history" 
                type="button" 
                role="tab" 
                aria-controls="tab-history" 
                aria-selected="false" 
                style="padding: 12px 20px; font-size: 14.5px;">
            <i class="fa-solid fa-clock-rotate-left me-2 text-success"></i>Data History Ekspor
        </button>
    </li>
    <?php endif; ?>
</ul>

<!-- Tab Contents -->
<div class="tab-content" id="detailTabsContent">

    <!-- ================= TAB 1: DATA PRODUK ================= -->
    <div class="tab-pane fade show active" id="tab-produk" role="tabpanel" aria-labelledby="tab-produk-btn">
        <div class="row g-4">
            <!-- Kolom Kiri: Foto Utama & Galeri -->
            <div class="col-lg-4 col-md-5">
                <div class="sales-card p-3 mb-4 text-center">
                    <?php
                        $fotosList = !empty($fotos) && is_array($fotos) ? $fotos : [];
                        $mainPhoto = !empty($fotosList) ? $fotosList[0] : base_url('assets/img/logo.png');
                        $hasPhotos = !empty($fotosList);
                    ?>
                    <div id="mainProductPhotoContainer"
                         style="background-color: #f8fafc; border-radius: 8px; overflow: hidden; height: 320px; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; cursor: pointer;"
                         onclick="openImageModal(this)"
                         title="Klik untuk melihat foto lebih besar">
                        <img id="mainProductImage" src="<?= esc($mainPhoto) ?>" 
                             alt="<?= esc($product['nama_produk_id'] ?? 'Foto Produk') ?>" 
                             class="img-fluid"
                             loading="lazy"
                             referrerpolicy="no-referrer"
                             style="max-height: 100%; max-width: 100%; object-fit: contain; padding: <?= $hasPhotos ? '10px' : '24px' ?>;"
                             onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>'; this.style.padding='24px';">
                    </div>

                    <?php if (count($fotosList) > 1): ?>
                        <div class="mt-3 pt-3 border-top">
                            <div class="text-muted small fw-semibold mb-2 text-start">Galeri Foto (<?= count($fotosList) ?>)</div>
                            <div class="d-flex flex-wrap gap-2 justify-content-start" id="productThumbnails">
                                <?php foreach ($fotosList as $idx => $fUrl): ?>
                                    <button type="button" 
                                            class="btn p-0 border rounded bg-white shadow-sm thumb-btn <?= $idx === 0 ? 'border-primary' : '' ?>" 
                                            style="width: 58px; height: 58px; overflow: hidden; border-width: 2px !important;"
                                            onclick="switchMainPhoto('<?= esc($fUrl, 'attr') ?>', this); openImageModal(this);"
                                            title="Buka foto <?= $idx + 1 ?>">
                                        <img src="<?= esc($fUrl) ?>" 
                                             alt="Thumbnail <?= $idx + 1 ?>" 
                                             loading="lazy"
                                             referrerpolicy="no-referrer"
                                             style="width: 100%; height: 100%; object-fit: cover;"
                                             onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>';">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>


            </div>

            <!-- Kolom Kanan: Informasi Produk & Informasi Ekspor Produk -->
            <div class="col-lg-8 col-md-7">
                <!-- Kartu Informasi Produk -->
                <div class="sales-card mb-4">
                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-dark d-flex align-items-center gap-2" style="font-size: 16px;">
                        <i class="fa-solid fa-circle-info text-success"></i>Informasi Produk
                    </h5>

                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Nama Produk (Indonesia)</div>
                        <div class="col-sm-8 value-col"><?= esc($product['nama_produk_id'] ?? '-') ?></div>
                    </div>
                    <?php if ($is_internal): ?>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Nama Produk (Inggris)</div>
                        <div class="col-sm-8 value-col"><?= esc($product['nama_produk_en'] ?? '-') ?></div>
                    </div>
                    <?php else: ?>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Kode Produk</div>
                        <div class="col-sm-8 value-col fw-bold text-success"><?= esc($product['kode_produk'] ?? '-') ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Kategori Produk</div>
                        <div class="col-sm-8 value-col"><?= esc($kategori) ?></div>
                    </div>

                    <!-- Deskripsi Indonesia -->
                    <div class="mt-3 pt-3 border-top">
                        <label class="fw-semibold text-dark small mb-2 d-block">
                            <i class="fa-solid fa-file-lines me-1 text-secondary"></i>Deskripsi Produk (Bahasa Indonesia)
                        </label>
                        <?php
                            $allowedTags = '<p><br><ul><ol><li><strong><b><em><i><a><h3><h4><table><thead><tbody><tr><th><td>';
                            $descIdClean = !empty($product['deskripsi_produk_id']) ? strip_tags($product['deskripsi_produk_id'], $allowedTags) : '';
                        ?>
                        <div class="desc-content">
                            <?= $descIdClean !== '' ? $descIdClean : '<span class="text-muted fst-italic">Tidak ada deskripsi.</span>' ?>
                        </div>
                    </div>

                    <!-- Deskripsi Inggris -->
                    <div class="mt-3 pt-3 border-top">
                        <label class="fw-semibold text-dark small mb-2 d-block">
                            <i class="fa-solid fa-file-lines me-1 text-secondary"></i>Deskripsi Produk (English)
                        </label>
                        <?php
                            $descEnClean = !empty($product['deskripsi_produk_en']) ? strip_tags($product['deskripsi_produk_en'], $allowedTags) : '';
                        ?>
                        <div class="desc-content">
                            <?= $descEnClean !== '' ? $descEnClean : '<span class="text-muted fst-italic">No description available.</span>' ?>
                        </div>
                    </div>
                </div>

                <!-- Kartu Informasi Ekspor Produk -->
                <div class="sales-card mb-4">
                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-dark d-flex align-items-center gap-2" style="font-size: 16px;">
                        <i class="fa-solid fa-truck-fast text-success"></i>Informasi Ekspor Produk
                    </h5>

                    <?php if (!empty($produkSupplier['spesifikasi']) && $produkSupplier['spesifikasi'] !== '-'): ?>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Spesifikasi</div>
                        <div class="col-sm-8 value-col"><?= esc($produkSupplier['spesifikasi']) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($produkSupplier['kapasitas_produksi']) && $produkSupplier['kapasitas_produksi'] !== '-'): ?>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Kapasitas Produksi</div>
                        <div class="col-sm-8 value-col"><?= esc($produkSupplier['kapasitas_produksi']) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if ($is_internal && !empty($produkSupplier['legalitas']) && $produkSupplier['legalitas'] !== '-'): ?>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Legalitas</div>
                        <div class="col-sm-8 value-col"><?= esc($produkSupplier['legalitas']) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($produkSupplier['sertifikasi']) && $produkSupplier['sertifikasi'] !== '-'): ?>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Sertifikasi</div>
                        <div class="col-sm-8 value-col"><?= esc($produkSupplier['sertifikasi']) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($produkSupplier['lokasi_barang']) && $produkSupplier['lokasi_barang'] !== '-'): ?>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Lokasi Barang</div>
                        <div class="col-sm-8 value-col"><?= esc($produkSupplier['lokasi_barang']) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($produkSupplier['moq']) && $produkSupplier['moq'] !== '-'): ?>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">MOQ</div>
                        <div class="col-sm-8 value-col"><?= esc($produkSupplier['moq']) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php 
                        $hExw = trim((string)($produkSupplier['harga_exw'] ?? ''));
                        $isHargaValid = ($hExw !== '' && $hExw !== '-' && stripos($hExw, '$0 USD') === false && $hExw !== '$0' && $hExw !== '0');
                    ?>
                    <?php if ($isHargaValid): ?>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Harga EXW</div>
                        <div class="col-sm-8 value-col"><?= esc($hExw) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if ($is_internal && !empty($produkSupplier['komisi']) && $produkSupplier['komisi'] !== '-'): ?>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">
                            Komisi
                            <span class="badge bg-secondary ms-1" style="font-size: 10px; font-weight: 500;">Internal</span>
                        </div>
                        <div class="col-sm-8 value-col"><?= esc($produkSupplier['komisi']) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($produkSupplier['sudah_ekspor']) && $produkSupplier['sudah_ekspor'] !== '-'): ?>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Sudah Ekspor</div>
                        <div class="col-sm-8 value-col"><?= esc($produkSupplier['sudah_ekspor']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= TAB 2: DATA PERUSAHAAN / PROFIL USAHA ================= -->
    <div class="tab-pane fade" id="tab-perusahaan" role="tabpanel" aria-labelledby="tab-perusahaan-btn">
        <div class="sales-card mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 17px;">
                    <?php if ($is_internal): ?>
                        <i class="fa-solid fa-building text-success"></i>
                        <?= esc(!empty($supplier['nama_perusahaan']) ? $supplier['nama_perusahaan'] : 'Belum ada data perusahaan') ?>
                    <?php else: ?>
                        <i class="fa-solid fa-briefcase text-success"></i>
                        Profil Usaha Rekanan (<?= esc($product['kode_produk'] ?? '') ?>)
                    <?php endif; ?>
                </h5>
                <?php if (!empty($supplier['jenis_perusahaan'])): ?>
                    <span class="badge bg-light text-dark border"><?= esc($supplier['jenis_perusahaan']) ?></span>
                <?php endif; ?>
            </div>

            <?php if (empty($supplier)): ?>
                <div class="alert alert-light border py-2 px-3 mb-4 d-flex align-items-center gap-2" style="background-color: #f8fafc; font-size: 13.5px; color: #64748b; border-color: #e2e8f0 !important;">
                    <i class="fa-solid fa-circle-info text-secondary"></i>
                    <span>Data profil usaha belum tersedia untuk produk ini.</span>
                </div>
            <?php endif; ?>

            <?php if ($is_internal): ?>
                <!-- ================= MODE INTERNAL: LENGKAP ================= -->
                <!-- Kartu Informasi Kontak Supplier (HANYA MODE INTERNAL) -->
                <div class="card border-0 mb-4 shadow-sm" style="background: rgba(240, 253, 244, 0.7); border: 1px solid #bbf7d0 !important; border-radius: 10px;">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-3 text-success d-flex align-items-center gap-2" style="font-size: 15px;">
                            <i class="fa-solid fa-address-book"></i>Informasi Kontak
                        </h6>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="row info-row">
                                    <div class="col-5 label-col">No. HP</div>
                                    <div class="col-7 value-col"><?= !empty($rawHp) ? esc($rawHp) : '-' ?></div>
                                </div>
                                <div class="row info-row">
                                    <div class="col-5 label-col">Email</div>
                                    <div class="col-7 value-col">
                                        <?php if (!empty($supplier['email'])): ?>
                                            <a href="mailto:<?= esc($supplier['email']) ?>" class="text-success text-decoration-none">
                                                <i class="fa-solid fa-envelope me-1"></i><?= esc($supplier['email']) ?>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row info-row">
                                    <div class="col-5 label-col">Website</div>
                                    <div class="col-7 value-col">
                                        <?php if (!empty($rawWeb)): ?>
                                            <?php if (!empty($webHref)): ?>
                                                <a href="<?= esc($webHref) ?>" target="_blank" rel="noopener noreferrer" class="text-success text-decoration-none">
                                                    <i class="fa-solid fa-globe me-1"></i><?= esc($rawWeb) ?>
                                                </a>
                                            <?php else: ?>
                                                <?= esc($rawWeb) ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row info-row">
                                    <div class="col-5 label-col">Instagram</div>
                                    <div class="col-7 value-col">
                                        <?php if (!empty($rawIg)): ?>
                                            <?php if (!empty($igHref)): ?>
                                                <a href="<?= esc($igHref) ?>" target="_blank" rel="noopener noreferrer" class="text-success text-decoration-none">
                                                    <i class="fa-brands fa-instagram me-1"></i><?= esc($rawIg) ?>
                                                </a>
                                            <?php else: ?>
                                                <?= esc($rawIg) ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian 1: 2 Kolom di Desktop -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="row info-row">
                            <div class="col-5 label-col">Domisili Usaha</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['domisili_usaha']) ? $supplier['domisili_usaha'] : '-') ?></div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Produk yang Dipasarkan</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['produk_dipasarkan']) ? $supplier['produk_dipasarkan'] : '-') ?></div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Jenis Perusahaan</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['jenis_perusahaan']) ? $supplier['jenis_perusahaan'] : '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row info-row">
                            <div class="col-5 label-col">Kapasitas Produksi</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['kapasitas_produksi']) ? $supplier['kapasitas_produksi'] : '-') ?></div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Jumlah Pegawai</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['jumlah_pegawai']) ? $supplier['jumlah_pegawai'] : '-') ?></div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Frekuensi Ekspor</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['frekuensi_ekspor']) ? $supplier['frekuensi_ekspor'] : '-') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Informasi Tambahan -->
                <h6 class="fw-bold mb-3 pb-2 border-bottom text-dark d-flex align-items-center gap-2" style="font-size: 15px;">
                    <i class="fa-solid fa-list-check text-success"></i>Informasi Tambahan
                </h6>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="row info-row">
                            <div class="col-5 label-col">Pameran Internasional</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['pameran_internasional']) ? $supplier['pameran_internasional'] : '-') ?></div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Keunggulan Produk</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['keunggulan_produk']) ? $supplier['keunggulan_produk'] : '-') ?></div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Legalitas</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['legalitas']) ? $supplier['legalitas'] : '-') ?></div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Sertifikasi</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['sertifikasi']) ? $supplier['sertifikasi'] : '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row info-row">
                            <div class="col-5 label-col">Pernah Ekspor</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['pernah_ekspor']) ? $supplier['pernah_ekspor'] : '-') ?></div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Kategori Ekspor</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['kategori_ekspor']) ? $supplier['kategori_ekspor'] : '-') ?></div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Link Foto Usaha</div>
                            <div class="col-7 value-col">
                                <?php if (!empty($supplier['link_foto_usaha'])): ?>
                                    <?php $links = array_filter(array_map('trim', explode(',', $supplier['link_foto_usaha']))); ?>
                                    <?php foreach ($links as $idx => $link): ?>
                                        <a href="<?= esc($link) ?>" target="_blank" rel="noopener" class="text-success text-decoration-none d-inline-block me-2 mb-1">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i><?= count($links) > 1 ? 'Link ' . ($idx + 1) : 'Buka Link' ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Link Dokumen Legalitas</div>
                            <div class="col-7 value-col">
                                <?php if (!empty($supplier['link_dokumen_legalitas'])): ?>
                                    <?php $links = array_filter(array_map('trim', explode(',', $supplier['link_dokumen_legalitas']))); ?>
                                    <?php foreach ($links as $idx => $link): ?>
                                        <a href="<?= esc($link) ?>" target="_blank" rel="noopener" class="text-success text-decoration-none d-inline-block me-2 mb-1">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i><?= count($links) > 1 ? 'Dokumen ' . ($idx + 1) : 'Buka Dokumen' ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Link Dokumen Sertifikat</div>
                            <div class="col-7 value-col">
                                <?php if (!empty($supplier['link_dokumen_sertifikat'])): ?>
                                    <?php $links = array_filter(array_map('trim', explode(',', $supplier['link_dokumen_sertifikat']))); ?>
                                    <?php foreach ($links as $idx => $link): ?>
                                        <a href="<?= esc($link) ?>" target="_blank" rel="noopener" class="text-success text-decoration-none d-inline-block me-2 mb-1">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i><?= count($links) > 1 ? 'Sertifikat ' . ($idx + 1) : 'Buka Sertifikat' ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- ================= MODE EKSTERNAL: HANYA 5 FIELD PROFIL USAHA ================= -->
                <!-- 5 Field Tetap: Jenis Perusahaan, Domisili Usaha, Produk yang Dipasarkan, Jumlah Pegawai, Pernah Ekspor -->
                <div class="row g-md-4 g-0 mb-4 profil-usaha-eksternal">
                    <div class="col-md-6">
                        <div class="row info-row">
                            <div class="col-5 label-col">Jenis Perusahaan</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['jenis_perusahaan']) ? $supplier['jenis_perusahaan'] : '-') ?></div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Domisili Usaha</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['domisili_usaha']) ? $supplier['domisili_usaha'] : '-') ?></div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Produk yang Dipasarkan</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['produk_dipasarkan']) ? $supplier['produk_dipasarkan'] : '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row info-row">
                            <div class="col-5 label-col">Jumlah Pegawai</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['jumlah_pegawai']) ? $supplier['jumlah_pegawai'] : '-') ?></div>
                        </div>
                        <div class="row info-row">
                            <div class="col-5 label-col">Pernah Ekspor</div>
                            <div class="col-7 value-col"><?= esc(!empty($supplier['pernah_ekspor']) ? $supplier['pernah_ekspor'] : '-') ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($is_internal): ?>
            <!-- Bagian 3: Ringkasan Ekspor Terakhir (hanya untuk Sales Internal) -->
            <h6 class="fw-bold mb-3 pb-2 border-bottom text-dark d-flex align-items-center gap-2" style="font-size: 15px;">
                <i class="fa-solid fa-plane-departure text-success"></i>Ekspor Terakhir
            </h6>
            <div class="row g-3">
                <div class="col-sm-6 col-lg">
                    <div class="p-3 rounded-2 border bg-light text-center">
                        <small class="text-muted d-block mb-1">Tanggal</small>
                        <span class="fw-semibold text-dark"><?= esc(!empty($supplier['ringkasan_ekspor_tanggal']) ? $supplier['ringkasan_ekspor_tanggal'] : '-') ?></span>
                    </div>
                </div>
                <div class="col-sm-6 col-lg">
                    <div class="p-3 rounded-2 border bg-light text-center">
                        <small class="text-muted d-block mb-1">Produk</small>
                        <span class="fw-semibold text-dark text-truncate d-block" title="<?= esc($supplier['ringkasan_ekspor_produk'] ?? '-') ?>"><?= esc(!empty($supplier['ringkasan_ekspor_produk']) ? $supplier['ringkasan_ekspor_produk'] : '-') ?></span>
                    </div>
                </div>
                <div class="col-sm-6 col-lg">
                    <div class="p-3 rounded-2 border bg-light text-center">
                        <small class="text-muted d-block mb-1">Kapasitas</small>
                        <span class="fw-semibold text-dark"><?= esc(!empty($supplier['ringkasan_ekspor_kapasitas']) ? $supplier['ringkasan_ekspor_kapasitas'] : '-') ?></span>
                    </div>
                </div>
                <div class="col-sm-6 col-lg">
                    <div class="p-3 rounded-2 border bg-light text-center">
                        <small class="text-muted d-block mb-1">Negara Tujuan</small>
                        <span class="fw-semibold text-dark"><?= esc(!empty($supplier['ringkasan_ekspor_negara']) ? $supplier['ringkasan_ekspor_negara'] : '-') ?></span>
                    </div>
                </div>
                <div class="col-sm-6 col-lg">
                    <div class="p-3 rounded-2 border bg-light text-center">
                        <small class="text-muted d-block mb-1">Nilai Ekspor</small>
                        <span class="fw-bold text-success"><?= esc(!empty($supplier['ringkasan_ekspor_nilai']) ? $supplier['ringkasan_ekspor_nilai'] : '-') ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($is_internal): ?>
    <!-- ================= TAB 3: DATA HISTORY EKSPOR ================= -->
    <div class="tab-pane fade" id="tab-history" role="tabpanel" aria-labelledby="tab-history-btn">
        <div class="sales-card mb-4">
            <!-- Baris Atas: Pilihan Filter Tahun (Tombol Pill) -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <h5 class="fw-bold mb-1 text-dark" style="font-size: 16px;">
                        <i class="fa-solid fa-chart-line text-success me-2"></i>Riwayat Ekspor
                    </h5>
                    <p class="text-muted mb-0 small">Catatan riwayat transaksi ekspor yang telah dilakukan.</p>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2" id="yearFilterContainer">
                    <span class="text-muted small me-1"><i class="fa-solid fa-filter me-1"></i>Filter Tahun:</span>
                    <?php if (empty($years)): ?>
                        <button type="button" class="btn btn-sm btn-sales-secondary disabled" disabled style="border-radius: 9999px; padding: 4px 14px; font-size: 13px;">
                            Semua
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-sm btn-sales-primary year-filter-btn active" data-year="all" style="border-radius: 9999px; padding: 4px 14px; font-size: 13px;">
                            Semua
                        </button>
                        <?php foreach ($years as $yr): ?>
                            <button type="button" class="btn btn-sm btn-sales-secondary year-filter-btn" data-year="<?= esc($yr) ?>" style="border-radius: 9999px; padding: 4px 14px; font-size: 13px;">
                                <?= esc($yr) ?>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabel Riwayat Ekspor -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="historyTable" style="font-size: 13.5px;">
                    <thead class="table-light text-muted" style="border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th style="width: 100px;">Tahun</th>
                            <th>Produk</th>
                            <th>Negara Tujuan</th>
                            <th>Jenis Ekspor</th>
                            <th>Kuantitas</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                        <?php if (empty($histories)): ?>
                            <tr id="emptyHistoryRow">
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-clock-rotate-left fa-2x mb-2 d-block text-secondary opacity-50"></i>
                                    <span>Belum ada data history ekspor</span>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($histories as $row): ?>
                                <tr class="history-row" data-tahun="<?= esc($row['tahun'] ?? '') ?>">
                                    <td class="fw-semibold text-dark"><?= esc($row['tahun'] ?? '-') ?></td>
                                    <td>
                                        <?= esc($row['produk'] ?? '-') ?>
                                        <?php if (!empty($row['catatan'])): ?>
                                            <i class="fa-solid fa-triangle-exclamation text-warning ms-1" 
                                               title="<?= esc($row['catatan']) ?>" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($row['negara_tujuan'] ?? '-') ?></td>
                                    <td><?= esc($row['jenis_ekspor'] ?? '-') ?></td>
                                    <td><?= esc($row['kuantitas'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="noMatchingHistoryRow" style="display: none;">
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <span>Tidak ada riwayat ekspor untuk tahun yang dipilih</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Modal / Lightbox Foto Besar (Ditempatkan di luar tab-content agar bebas dari stacking context induk) -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                    <i class="fa-solid fa-image text-success fs-5"></i>
                    <h6 class="modal-title text-dark fw-bold mb-0 text-truncate" id="imageModalLabel" title="<?= esc($product['nama_produk_id'] ?? 'Foto Produk') ?>">
                        <?= esc($product['nama_produk_id'] ?? 'Foto Produk') ?>
                    </h6>
                </div>
                <button type="button" 
                        id="imageModalCloseBtn"
                        class="btn-close-modal-img" 
                        aria-label="Tutup" 
                        title="Tutup (Esc)">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Body: Klik gambar atau latar untuk menutup -->
            <div class="modal-body" id="imageModalBody" title="Klik di mana saja untuk menutup">
                <img id="modalProductImage" 
                     src="<?= esc($mainPhoto) ?>" 
                     alt="Preview Foto" 
                     loading="lazy"
                     referrerpolicy="no-referrer"
                     onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>';">
                <div class="modal-img-click-hint">
                    <i class="fa-solid fa-circle-info me-1 text-info"></i> Klik pada gambar atau latar belakang untuk menutup
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer d-flex align-items-center justify-content-between">
                <small class="text-muted d-none d-sm-inline">
                    Tekan tombol <kbd style="background: #e2e8f0; color: #1e293b; padding: 2px 6px; border-radius: 4px; font-size: 11px;">Esc</kbd> untuk menutup
                </small>
                <button type="button" 
                        id="imageModalCloseFooterBtn"
                        class="btn btn-secondary btn-sm px-4 fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Tutup / Kembali</span>
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Pindahkan elemen modal ke body segera setelah DOM siap
    var modalElInit = document.getElementById('imageModal');
    if (modalElInit && modalElInit.parentElement !== document.body) {
        document.body.appendChild(modalElInit);
    }

    // -----------------------------------------------------------------
    // 1. Tooltip Bootstrap
    // -----------------------------------------------------------------
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            try {
                new bootstrap.Tooltip(tooltipTriggerEl);
            } catch (err) {}
        });
    }

    // -----------------------------------------------------------------
    // 2. Hash URL Synchronization (#produk, #perusahaan, #history)
    // -----------------------------------------------------------------
    const hashToTab = {
        '#produk': '#tab-produk-btn',
        '#perusahaan': '#tab-perusahaan-btn'<?php if ($is_internal): ?>,
        '#history': '#tab-history-btn'<?php endif; ?>
    };

    function activateTabByHash() {
        const hash = window.location.hash;
        if (hash && hashToTab[hash]) {
            const targetBtn = document.querySelector(hashToTab[hash]);
            if (targetBtn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                try {
                    const tabInstance = bootstrap.Tab.getOrCreateInstance(targetBtn);
                    tabInstance.show();
                } catch (err) {}
            }
        }
    }

    // Tangani pergantian tab oleh pengguna -> simpan ke hash URL
    const tabButtons = document.querySelectorAll('.detail-tab-btn');
    tabButtons.forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('data-bs-target');
            if (target === '#tab-produk') {
                history.replaceState(null, null, '#produk');
            } else if (target === '#tab-perusahaan') {
                history.replaceState(null, null, '#perusahaan');
            <?php if ($is_internal): ?>
            } else if (target === '#tab-history') {
                history.replaceState(null, null, '#history');
            <?php endif; ?>
            }
        });
    });

    // Pulihkan tab saat halaman pertama kali dibuka / di-refresh / hash diubah
    activateTabByHash();
    window.addEventListener('hashchange', activateTabByHash);

    <?php if ($is_internal): ?>
    // -----------------------------------------------------------------
    // 3. Client-side Filter Tahun (Data History Ekspor - Hanya Sales Internal)
    // -----------------------------------------------------------------
    const yearButtons = document.querySelectorAll('.year-filter-btn');
    const historyRows = document.querySelectorAll('.history-row');
    const noMatchingRow = document.getElementById('noMatchingHistoryRow');

    if (yearButtons.length > 0 && historyRows.length > 0) {
        yearButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const selectedYear = this.getAttribute('data-year');

                // Update styling tombol aktif
                yearButtons.forEach(function (b) {
                    b.classList.remove('btn-sales-primary', 'active');
                    b.classList.add('btn-sales-secondary');
                });
                this.classList.remove('btn-sales-secondary');
                this.classList.add('btn-sales-primary', 'active');

                // Filter baris
                let visibleCount = 0;

                historyRows.forEach(function (row) {
                    const rowYear = row.getAttribute('data-tahun');

                    if (selectedYear === 'all' || rowYear === selectedYear) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (noMatchingRow) {
                    noMatchingRow.style.display = (visibleCount === 0) ? '' : 'none';
                }
            });
        });
    }
    <?php endif; ?>
});

function switchMainPhoto(url, btn) {
    var mainImg = document.getElementById('mainProductImage');
    var modalImg = document.getElementById('modalProductImage');
    if (mainImg) {
        mainImg.src = url;
    }
    if (modalImg) {
        modalImg.src = url;
    }
    document.querySelectorAll('.thumb-btn').forEach(function(b) {
        b.classList.remove('border-primary');
    });
    if (btn) {
        btn.classList.add('border-primary');
    }
}

// -----------------------------------------------------------------
// 4. Pengendali Modal Foto Produk (Buka, Tutup, Escape, Backdrop)
// -----------------------------------------------------------------
var lastFocusedImageTrigger = null;

function openImageModal(triggerEl) {
    var modalEl = document.getElementById('imageModal');
    if (!modalEl) return;

    // Pastikan modal berada langsung di bawah body agar tidak terkurung stacking context / backdrop-filter
    if (modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
    }

    if (triggerEl) {
        lastFocusedImageTrigger = triggerEl;
    } else if (document.activeElement) {
        lastFocusedImageTrigger = document.activeElement;
    }

    modalEl.style.display = 'flex';
    modalEl.classList.add('show');
    modalEl.setAttribute('aria-modal', 'true');
    modalEl.removeAttribute('aria-hidden');

    document.body.classList.add('modal-open');
    document.body.style.overflow = 'hidden';

    var closeBtn = document.getElementById('imageModalCloseBtn');
    if (closeBtn) {
        closeBtn.focus();
    }
}

function closeImageModal() {
    var modalEl = document.getElementById('imageModal');
    if (!modalEl) return;

    modalEl.classList.remove('show');
    modalEl.style.display = 'none';
    modalEl.setAttribute('aria-hidden', 'true');
    modalEl.removeAttribute('aria-modal');

    // Bersihkan sisa efek backdrop jika ada
    document.querySelectorAll('.modal-backdrop').forEach(function (el) {
        el.remove();
    });

    // Kembalikan kemampuan scroll pada body
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');

    // Kembalikan fokus ke thumbnail atau elemen yang membuka popup
    if (lastFocusedImageTrigger && typeof lastFocusedImageTrigger.focus === 'function') {
        try {
            lastFocusedImageTrigger.focus();
        } catch (err) {}
    }
}

// Pasang Event Listener langsung pada elemen tombol & area penutup
var closeBtn = document.getElementById('imageModalCloseBtn');
if (closeBtn) {
    closeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        closeImageModal();
    });
}

var closeFooterBtn = document.getElementById('imageModalCloseFooterBtn');
if (closeFooterBtn) {
    closeFooterBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        closeImageModal();
    });
}

var modalBody = document.getElementById('imageModalBody');
if (modalBody) {
    modalBody.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        closeImageModal();
    });
}

var modalEl = document.getElementById('imageModal');
if (modalEl) {
    modalEl.addEventListener('click', function (e) {
        if (e.target === this) {
            e.preventDefault();
            closeImageModal();
        }
    });
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27) {
        var m = document.getElementById('imageModal');
        if (m && (m.classList.contains('show') || m.style.display !== 'none')) {
            closeImageModal();
        }
    }
});
</script>
<?= $this->endSection() ?>
