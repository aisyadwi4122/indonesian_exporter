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
}
</style>

<div class="produk-hero mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <div class="produk-breadcrumb mb-0"><strong>SALES</strong> / PRODUK</div>
        
        <!-- BADGE & KONTROL MODE -->
        <div class="d-flex align-items-center gap-2">
            <?php if ($is_internal): ?>
                <span class="badge bg-success text-white shadow-sm" style="font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 6px;">
                    <i class="fa-solid fa-shield-halved me-1"></i> Mode: Internal
                </span>
                <?php if ($account_tipe === 'internal'): ?>
                    <a href="<?= base_url('sales/produk?mode=eksternal' . (!empty($q) ? '&q=' . urlencode($q) : '')) ?>" 
                       class="btn btn-sm btn-outline-light shadow-sm" 
                       style="font-size: 12px; border-radius: 6px; padding: 4px 10px;"
                       title="Lihat tampilan katalog dalam mode eksternal">
                        <i class="fa-solid fa-eye me-1"></i> Pratinjau Eksternal
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <span class="badge text-white shadow-sm" style="background-color: #ea580c; font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 6px;">
                    <i class="fa-solid fa-globe me-1"></i> Mode: Eksternal<?= $is_preview ? ' (Pratinjau)' : '' ?>
                </span>
                <?php if ($account_tipe === 'internal'): ?>
                    <a href="<?= base_url('sales/produk' . (!empty($q) ? '?q=' . urlencode($q) : '')) ?>" 
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
            <h2 class="fw-bold mb-1" style="font-size: 24px;">Produk</h2>
            <p class="mb-0" style="font-size: 14px;">Daftar katalog produk ekspor (hanya lihat).</p>
        </div>
        <div>
            <?php if ($account_tipe === 'internal' && !$is_preview): ?>
                <!-- Dua pilihan PDF untuk akun internal: Eksternal (default) & Internal -->
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sales-primary shadow-sm dropdown-toggle" type="button" id="pdfDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="pdfDropdown" style="min-width: 290px;">
                        <li>
                            <a class="dropdown-item py-2" id="btnExportPdfEksternal" href="<?= base_url('sales/produk/pdf?tipe_pdf=eksternal' . (!empty($q) ? '&q=' . urlencode($q) : '')) ?>" target="_blank">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fw-bold text-dark"><i class="fa-solid fa-file-pdf text-success me-2"></i>PDF Eksternal</span>
                                    <span class="badge bg-success" style="font-size: 10px;">Default</span>
                                </div>
                                <small class="text-muted d-block mt-1">Aman dikirim ke buyer (tanpa data supplier & komisi)</small>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2" id="btnExportPdfInternal" href="<?= base_url('sales/produk/pdf?tipe_pdf=internal' . (!empty($q) ? '&q=' . urlencode($q) : '')) ?>" target="_blank">
                                <div class="fw-bold text-dark"><i class="fa-solid fa-file-shield text-warning me-2"></i>PDF Internal</div>
                                <small class="text-muted d-block mt-1">Lengkap dengan data supplier, legalitas & komisi</small>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <!-- Akun eksternal atau dalam pratinjau: HANYA PDF Eksternal -->
                <a href="<?= base_url('sales/produk/pdf' . (!empty($q) ? '?q=' . urlencode($q) : '')) ?>" 
                   id="btnExportPdf"
                   class="btn btn-sales-primary shadow-sm" 
                   target="_blank">
                    <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Kotak Pencarian Produk -->
<div class="sales-card mb-4" style="padding: 16px 20px;">
    <form method="GET" action="<?= base_url('sales/produk') ?>" class="row g-2 align-items-center">
        <?php if ($is_preview): ?>
            <input type="hidden" name="mode" value="eksternal">
        <?php endif; ?>
        <div class="col">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" id="searchInput" name="q" class="form-control border-start-0 ps-0" 
                       placeholder="<?= $is_internal ? 'Cari produk, supplier, lokasi...' : 'Cari nama produk, spesifikasi, lokasi...' ?>" 
                       value="<?= esc($q ?? '') ?>" 
                       style="border-radius: 0 8px 8px 0; font-size: 14px;"
                       autocomplete="off">
            </div>
        </div>
        <?php if (!empty($q)): ?>
        <div class="col-auto">
            <a href="<?= base_url('sales/produk' . ($is_preview ? '?mode=eksternal' : '')) ?>" class="btn btn-sales-secondary" style="font-size: 13.5px;">
                <i class="fa-solid fa-xmark me-1"></i> Reset
            </a>
        </div>
        <?php endif; ?>
    </form>
</div>

<!-- Pesan Jika Produk Tidak Ditemukan -->
<div id="emptyState" class="sales-card text-center py-5" style="display: <?= empty($products) ? 'block' : 'none' ?>;">
    <div class="mb-3">
        <i class="fa-solid fa-box-open text-muted" style="font-size: 48px;"></i>
    </div>
    <h5 class="fw-semibold text-dark mb-1">Produk tidak ditemukan</h5>
    <p class="text-muted mb-0" style="font-size: 14px;">Coba gunakan kata kunci pencarian yang lain.</p>
</div>

<!-- Daftar Kartu Produk -->
<div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4" id="productsGrid">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $p): ?>
            <?php
                $idProduk = $p['id_produk_supplier'] ?? ($p['id_produk'] ?? 0);
                $kodeProduk = $p['kode_produk'] ?? ('IE-' . str_pad((string)$idProduk, 4, '0', STR_PAD_LEFT));
                $nama = !empty($p['nama_produk']) ? $p['nama_produk'] : (!empty($p['nama_produk_id']) ? $p['nama_produk_id'] : ($p['nama_produk_en'] ?? 'Produk'));
                $descRaw = !empty($p['spesifikasi']) ? $p['spesifikasi'] : (!empty($p['deskripsi_produk_id']) ? $p['deskripsi_produk_id'] : ($p['deskripsi_produk_en'] ?? ''));
                $descClean = trim(strip_tags($descRaw));
                $descShort = mb_strlen($descClean) > 110 ? mb_substr($descClean, 0, 110) . '...' : $descClean;
                $supplier = $is_internal ? (!empty($p['nama_perusahaan']) ? $p['nama_perusahaan'] : '') : '';
                $lokasi = !empty($p['lokasi_barang']) ? $p['lokasi_barang'] : (!empty($p['domisili_usaha']) ? $p['domisili_usaha'] : '');

                $imgUrl = !empty($p['foto_url']) ? $p['foto_url'] : base_url('assets/img/logo.png');
                $isPlaceholder = empty($p['foto_url']);
                
                $detailUrl = base_url('sales/produk/' . $idProduk . ($is_preview ? '?mode=eksternal' : ''));
                $detailPdfUrl = base_url('sales/produk/' . $idProduk . '/pdf' . ($is_preview ? '?mode=eksternal' : ''));
                $searchDesc = $is_internal ? ($descClean . ' ' . $supplier . ' ' . $lokasi) : ($descClean . ' ' . $lokasi . ' ' . $kodeProduk);
            ?>
            <div class="col product-card-col" 
                 data-id="<?= $idProduk ?>"
                 data-title="<?= esc(strtolower($nama)) ?>"
                 data-desc="<?= esc(strtolower($searchDesc)) ?>">
                <div class="sales-card h-100 d-flex flex-column p-0 overflow-hidden" style="border-radius: 10px; transition: transform 0.15s ease, box-shadow 0.15s ease;">
                    <div style="height: 180px; background-color: #f1f5f9; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        <img src="<?= esc($imgUrl) ?>" 
                             alt="<?= esc($nama) ?>" 
                             loading="lazy"
                             referrerpolicy="no-referrer"
                             style="width: 100%; height: 100%; object-fit: <?= $isPlaceholder ? 'contain' : 'cover' ?>; padding: <?= $isPlaceholder ? '20px' : '0' ?>;"
                             onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>'; this.style.objectFit='contain'; this.style.padding='20px';">
                    </div>
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <?php if ($is_internal): ?>
                            <?php if (!empty($supplier)): ?>
                            <div class="mb-1 text-truncate" style="font-size: 11.5px; font-weight: 600; color: #16a34a;" title="<?= esc($supplier) ?>">
                                <i class="fa-solid fa-building me-1"></i><?= esc($supplier) ?>
                                <span class="text-muted fw-normal ms-1">(<?= esc($kodeProduk) ?>)</span>
                            </div>
                            <?php if (!empty($p['no_hp'])): ?>
                            <div class="mb-1 text-truncate text-muted" style="font-size: 11px;" title="<?= esc($p['no_hp']) ?>">
                                <i class="fa-solid fa-phone me-1 text-success"></i><?= esc($p['no_hp']) ?>
                            </div>
                            <?php endif; ?>
                            <?php else: ?>
                            <div class="mb-1 text-truncate" style="font-size: 11.5px; font-weight: 600; color: #16a34a;" title="<?= esc($kodeProduk) ?>">
                                <i class="fa-solid fa-tag me-1"></i><?= esc($kodeProduk) ?>
                            </div>
                            <?php if (!empty($p['no_hp'])): ?>
                            <div class="mb-1 text-truncate text-muted" style="font-size: 11px;" title="<?= esc($p['no_hp']) ?>">
                                <i class="fa-solid fa-phone me-1 text-success"></i><?= esc($p['no_hp']) ?>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="mb-1 text-truncate" style="font-size: 11.5px; font-weight: 600; color: #16a34a;" title="<?= esc($kodeProduk) ?>">
                                <i class="fa-solid fa-tag me-1"></i><?= esc($kodeProduk) ?>
                            </div>
                        <?php endif; ?>

                        <h5 class="fw-bold mb-2 text-dark" style="font-size: 15.5px; line-height: 1.35;" title="<?= esc($nama) ?>">
                            <a href="<?= $detailUrl ?>" class="text-dark text-decoration-none">
                                <?= esc($nama) ?>
                            </a>
                        </h5>
                        <?php if (!empty($lokasi)): ?>
                        <div class="text-muted mb-2 small text-truncate" style="font-size: 12px;">
                            <i class="fa-solid fa-location-dot me-1 text-secondary"></i><?= esc($lokasi) ?>
                        </div>
                        <?php endif; ?>
                        <p class="text-muted mb-3 flex-grow-1" style="font-size: 13px; line-height: 1.5;">
                            <?= esc($descShort) ?>
                        </p>
                        
                        <!-- Aksi Kartu Produk (Hanya Lihat & Unduh) -->
                        <div class="pt-2 border-top d-flex gap-2">
                            <a href="<?= $detailUrl ?>" 
                               class="btn btn-sm btn-outline-success flex-grow-1"
                               title="Detail">
                                <i class="fa-solid fa-eye me-1"></i> Detail
                            </a>
                            <a href="<?= $detailPdfUrl ?>" 
                               target="_blank"
                               class="btn btn-sm btn-outline-danger"
                               title="Export PDF">
                                <i class="fa-solid fa-file-pdf me-1"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // FITUR PENCARIAN REAL-TIME & SINKRONISASI EXPORT PDF
    // -------------------------------------------------------------
    const searchInput = document.getElementById('searchInput');
    const productCards = document.querySelectorAll('.product-card-col');
    const emptyState = document.getElementById('emptyState');
    const btnExportPdf = document.getElementById('btnExportPdf');
    const btnExportPdfEksternal = document.getElementById('btnExportPdfEksternal');
    const btnExportPdfInternal = document.getElementById('btnExportPdfInternal');

    function updatePdfLink(element, query) {
        if (!element) return;
        try {
            const url = new URL(element.href, window.location.origin);
            if (query && query.trim() !== '') {
                url.searchParams.set('q', query.trim());
            } else {
                url.searchParams.delete('q');
            }
            element.href = url.pathname + url.search;
        } catch (e) {
            // Fallback string manipulation
        }
    }

    function filterProducts(query) {
        const q = (query || '').trim().toLowerCase();
        let visibleCount = 0;

        productCards.forEach(function (card) {
            const title = card.getAttribute('data-title') || '';
            const desc = card.getAttribute('data-desc') || '';

            if (q === '' || title.indexOf(q) !== -1 || desc.indexOf(q) !== -1) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
        }

        updatePdfLink(btnExportPdf, query);
        updatePdfLink(btnExportPdfEksternal, query);
        updatePdfLink(btnExportPdfInternal, query);
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            filterProducts(this.value);
        });

        // Trigger pencarian awal jika terdapat nilai pencarian dari URL
        if (searchInput.value.trim() !== '') {
            filterProducts(searchInput.value);
        }
    }
});
</script>
<?= $this->endSection() ?>
