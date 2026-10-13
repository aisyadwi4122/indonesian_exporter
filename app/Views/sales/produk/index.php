<?= $this->extend('sales/layout') ?>

<?= $this->section('content') ?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size: 24px; color: #1e293b;">Produk</h2>
        <p class="text-muted mb-0" style="font-size: 14px;">Daftar katalog produk ekspor (hanya lihat).</p>
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
                $nama = !empty($p['nama_produk_id']) ? $p['nama_produk_id'] : $p['nama_produk_en'];
                $descRaw = !empty($p['deskripsi_produk_id']) ? $p['deskripsi_produk_id'] : $p['deskripsi_produk_en'];
                $descClean = trim(strip_tags($descRaw));
                $descShort = mb_strlen($descClean) > 110 ? mb_substr($descClean, 0, 110) . '...' : $descClean;
                $imgUrl = base_url('assets/img/produk/' . esc($p['foto_produk']));
                $slug = !empty($p['slug_id']) ? $p['slug_id'] : (!empty($p['slug_en']) ? $p['slug_en'] : 'produk-' . $p['id_produk']);
            ?>
            <div class="col product-card-col" 
                 data-id="<?= $p['id_produk'] ?>"
                 data-title="<?= esc(strtolower($nama)) ?>"
                 data-desc="<?= esc(strtolower($descClean)) ?>">
                <div class="sales-card h-100 d-flex flex-column p-0 overflow-hidden" style="border-radius: 10px; transition: transform 0.15s ease, box-shadow 0.15s ease;">
                    <div style="height: 180px; background-color: #f1f5f9; position: relative; overflow: hidden;">
                        <img src="<?= $imgUrl ?>" 
                             alt="<?= esc($nama) ?>" 
                             loading="lazy"
                             style="width: 100%; height: 100%; object-fit: cover;"
                             onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>'; this.style.objectFit='contain'; this.style.padding='20px';">
                    </div>
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <h5 class="fw-bold mb-2 text-dark" style="font-size: 15.5px; line-height: 1.35;" title="<?= esc($nama) ?>">
                            <?= esc($nama) ?>
                        </h5>
                        <p class="text-muted mb-3 flex-grow-1" style="font-size: 13px; line-height: 1.5;">
                            <?= esc($descShort) ?>
                        </p>
                        
                        <!-- Aksi Kartu Produk (Hanya Lihat & Unduh) -->
                        <div class="pt-2 border-top">
                            <!-- halaman detail Sales belum dibuat -->
                            <button type="button" 
                                    class="btn btn-sm btn-sales-secondary w-100 mb-2 btn-detail-sales"
                                    title="Lihat Detail (Hanya Tampilan)">
                                <i class="fa-solid fa-circle-info me-1"></i> Lihat Detail
                            </button>

                            <button type="button" 
                                    class="btn btn-sm btn-sales-primary w-100 btn-download-pdf"
                                    data-id="<?= $p['id_produk'] ?>"
                                    data-name="<?= esc($nama) ?>"
                                    data-img="<?= $imgUrl ?>"
                                    data-desc="<?= esc($descClean) ?>"
                                    data-slug="<?= esc($slug) ?>">
                                <i class="fa-solid fa-file-pdf me-1"></i> Unduh PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Kontainer Tersembunyi untuk Render PDF A4 -->
<div id="pdfRenderContainer" style="display: none;"></div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- html2pdf.js via CDN (hanya dimuat di halaman produk Sales ini) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" 
        integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqg3/Hqd0OTaiJTEAUVVGUvPBnrA==" 
        crossorigin="anonymous" 
        referrerpolicy="no-referrer"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // FITUR PENCARIAN REAL-TIME
    // -------------------------------------------------------------
    const searchInput = document.getElementById('searchInput');
    const productCards = document.querySelectorAll('.product-card-col');
    const emptyState = document.getElementById('emptyState');

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

    // -------------------------------------------------------------
    // FITUR UNDUH PDF PER PRODUK (A4 Portrait)
    // -------------------------------------------------------------
    const pdfButtons = document.querySelectorAll('.btn-download-pdf');
    const pdfRenderContainer = document.getElementById('pdfRenderContainer');

    pdfButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const button = this;
            const productName = button.getAttribute('data-name') || 'Produk';
            const productImg = button.getAttribute('data-img') || '';
            const productDesc = button.getAttribute('data-desc') || '';
            const productSlug = button.getAttribute('data-slug') || 'produk';

            const originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Memproses PDF...';

            // Pastikan gambar sudah selesai dimuat sebelum merender PDF (cegah gambar kosong)
            const img = new Image();
            img.crossOrigin = 'anonymous';

            function renderAndDownload(imageSrc) {
                // Bersihkan kontainer render
                pdfRenderContainer.innerHTML = '';
                pdfRenderContainer.style.display = 'block';

                // Siapkan template A4 portrait khusus produk ini
                const pdfContent = document.createElement('div');
                pdfContent.style.width = '190mm';
                pdfContent.style.minHeight = '270mm';
                pdfContent.style.padding = '20mm 15mm';
                pdfContent.style.background = '#ffffff';
                pdfContent.style.color = '#1e293b';
                pdfContent.style.fontFamily = 'Arial, sans-serif';
                pdfContent.style.boxSizing = 'border-box';

                pdfContent.innerHTML = `
                    <div style="text-align: center; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 2px solid #16a34a;">
                        <h1 style="font-size: 24px; font-weight: bold; color: #16a34a; margin: 0 0 6px 0; text-transform: uppercase;">
                            ${escapeHtml(productName)}
                        </h1>
                    </div>
                    <div style="text-align: center; margin-bottom: 28px;">
                        <img src="${imageSrc}" 
                             style="max-width: 100%; max-height: 130mm; object-fit: contain; border-radius: 8px; border: 1px solid #e2e8f0; padding: 4px;" 
                             alt="${escapeHtml(productName)}">
                    </div>
                    <div style="margin-top: 10px;">
                        <h3 style="font-size: 15px; font-weight: bold; color: #334155; margin: 0 0 10px 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px;">
                            Deskripsi Produk
                        </h3>
                        <p style="font-size: 13.5px; line-height: 1.7; color: #475569; text-align: justify; margin: 0; white-space: pre-line;">
                            ${escapeHtml(productDesc)}
                        </p>
                    </div>
                `;

                pdfRenderContainer.appendChild(pdfContent);

                // Nama file PDF: nama-produk.pdf
                let cleanFileName = productSlug.toLowerCase().replace(/[^a-z0-9_-]/g, '-').replace(/-+/g, '-');
                if (!cleanFileName) {
                    cleanFileName = 'produk';
                }
                const filename = cleanFileName + '.pdf';

                const opt = {
                    margin:       [10, 10, 10, 10],
                    filename:     filename,
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true, logging: false },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };

                html2pdf().set(opt).from(pdfContent).save().then(function () {
                    pdfRenderContainer.style.display = 'none';
                    pdfRenderContainer.innerHTML = '';
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }).catch(function (err) {
                    console.error('Gagal membuat PDF:', err);
                    pdfRenderContainer.style.display = 'none';
                    pdfRenderContainer.innerHTML = '';
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                    alert('Gagal mengunduh PDF produk.');
                });
            }

            img.onload = function () {
                renderAndDownload(productImg);
            };

            img.onerror = function () {
                // Jika gambar gagal dimuat via CORS atau offline, tetap render dengan URL langsung
                renderAndDownload(productImg);
            };

            img.src = productImg;
        });
    });

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function (m) { return map[m]; });
    }
});
</script>
<?= $this->endSection() ?>
