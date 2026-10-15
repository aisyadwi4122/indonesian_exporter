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
    <div class="produk-breadcrumb"><strong>SALES</strong> / PRODUK</div>
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 24px;">Produk</h2>
            <p class="mb-0" style="font-size: 14px;">Daftar katalog produk ekspor (hanya lihat).</p>
        </div>
        <div>
            <a href="<?= base_url('sales/produk/pdf') . (!empty($q) ? '?q=' . urlencode($q) : '') ?>" 
               id="btnExportPdf"
               class="btn btn-sales-primary shadow-sm" 
               target="_blank">
                <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>
</div>

<!-- Kotak Pencarian Produk -->
<div class="sales-card mb-4" style="padding: 16px 20px;">
    <div class="row g-2 align-items-center">
        <div class="col">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" id="searchInput" name="q" class="form-control border-start-0 ps-0" 
                       placeholder="Cari produk..." 
                       value="<?= esc($q ?? '') ?>" 
                       style="border-radius: 0 8px 8px 0; font-size: 14px;"
                       autocomplete="off">
            </div>
        </div>
        <?php if (!empty($q)): ?>
        <div class="col-auto">
            <a href="<?= base_url('sales/produk') ?>" class="btn btn-sales-secondary" style="font-size: 13.5px;">
                <i class="fa-solid fa-xmark me-1"></i> Reset
            </a>
        </div>
        <?php endif; ?>
    </div>
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
                $nama = !empty($p['nama_produk']) ? $p['nama_produk'] : (!empty($p['nama_produk_id']) ? $p['nama_produk_id'] : ($p['nama_produk_en'] ?? 'Produk'));
                $descRaw = !empty($p['spesifikasi']) ? $p['spesifikasi'] : (!empty($p['deskripsi_produk_id']) ? $p['deskripsi_produk_id'] : ($p['deskripsi_produk_en'] ?? ''));
                $descClean = trim(strip_tags($descRaw));
                $descShort = mb_strlen($descClean) > 110 ? mb_substr($descClean, 0, 110) . '...' : $descClean;
                $supplier = !empty($p['nama_perusahaan']) ? $p['nama_perusahaan'] : '';
                $lokasi = !empty($p['lokasi_barang']) ? $p['lokasi_barang'] : (!empty($p['domisili_usaha']) ? $p['domisili_usaha'] : '');

                // Resolusi URL Gambar Produk (Default ke logo/placeholder)
                if (!empty($p['foto_1'])) {
                    $imgUrl = (str_starts_with($p['foto_1'], 'http://') || str_starts_with($p['foto_1'], 'https://'))
                        ? $p['foto_1']
                        : base_url($p['foto_1']);
                } elseif (!empty($p['foto_produk'])) {
                    $imgUrl = (str_starts_with($p['foto_produk'], 'http://') || str_starts_with($p['foto_produk'], 'https://'))
                        ? $p['foto_produk']
                        : (str_starts_with($p['foto_produk'], 'assets/') ? base_url($p['foto_produk']) : base_url('assets/img/produk/' . esc($p['foto_produk'])));
                } else {
                    $imgUrl = base_url('assets/img/logo.png');
                }

                $slug = 'produk-' . $idProduk;
            ?>
            <div class="col product-card-col" 
                 data-id="<?= $idProduk ?>"
                 data-title="<?= esc(strtolower($nama)) ?>"
                 data-desc="<?= esc(strtolower($descClean . ' ' . $supplier . ' ' . $lokasi)) ?>">
                <div class="sales-card h-100 d-flex flex-column p-0 overflow-hidden" style="border-radius: 10px; transition: transform 0.15s ease, box-shadow 0.15s ease;">
                    <div style="height: 180px; background-color: #f1f5f9; position: relative; overflow: hidden;">
                        <img src="<?= $imgUrl ?>" 
                             alt="<?= esc($nama) ?>" 
                             loading="lazy"
                             style="width: 100%; height: 100%; object-fit: contain; padding: 12px;"
                             onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>'; this.style.objectFit='contain'; this.style.padding='20px';">
                    </div>
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <?php if (!empty($supplier)): ?>
                        <div class="mb-1 text-truncate" style="font-size: 11.5px; font-weight: 600; color: #16a34a;" title="<?= esc($supplier) ?>">
                            <i class="fa-solid fa-building me-1"></i><?= esc($supplier) ?>
                        </div>
                        <?php endif; ?>
                        <h5 class="fw-bold mb-2 text-dark" style="font-size: 15.5px; line-height: 1.35;" title="<?= esc($nama) ?>">
                            <a href="<?= base_url('sales/produk/' . $idProduk) ?>" class="text-dark text-decoration-none">
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
                            <a href="<?= base_url('sales/produk/' . $idProduk) ?>" 
                               class="btn btn-sm btn-outline-success flex-grow-1"
                               title="Detail">
                                <i class="fa-solid fa-eye me-1"></i> Detail
                            </a>
                            <a href="<?= base_url('sales/produk/' . $idProduk . '/pdf') ?>" 
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
    const baseExportUrl = '<?= base_url('sales/produk/pdf') ?>';

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

        if (btnExportPdf) {
            const rawVal = (query || '').trim();
            btnExportPdf.href = baseExportUrl + (rawVal !== '' ? '?q=' + encodeURIComponent(rawVal) : '');
        }
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
