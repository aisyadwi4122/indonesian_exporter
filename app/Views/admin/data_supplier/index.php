<?= $this->extend('admin/template/template'); ?>
<?= $this->section('content'); ?>

<style>
.product-card-hover {
    transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
}
.product-card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 .25rem .5rem rgba(0,0,0,.08) !important;
}
.product-title-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.35;
    max-height: 2.7em;
}
[aria-expanded="true"] .fa-chevron-down {
    transform: rotate(180deg);
}
.fa-chevron-down {
    transition: transform 0.2s ease-in-out;
}
/* Highlight produk yang cocok saat pencarian (latar kuning lembut) */
.product-card-match {
    background-color: #fff9db !important;
    border-color: #ffe066 !important;
    box-shadow: 0 0 0 2px #fcc419 !important;
}
.product-card-match .product-title-clamp {
    color: #856404 !important;
    font-weight: 700 !important;
}
/* Styling tombol clear pencarian */
#supplierSearchClearBtn:hover {
    color: #dc3545 !important;
    background-color: transparent !important;
}
</style>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="row g-3 mb-3 align-items-center justify-content-between">
            <div class="col-auto">
                <h1 class="app-page-title mb-1">Data Supplier</h1>
                <p class="text-muted small mb-0"><?= count($suppliers) ?> perusahaan · <?= (int)$totalCatalogProducts ?> produk</p>
            </div>
            <div class="col-12 col-sm-auto d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary text-white btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPerusahaan">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Perusahaan
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBanyak">
                    <i class="fa-solid fa-table me-1"></i> Tambah Banyak
                </button>
            </div>
        </div><!--//row-->

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php $bulkReport = session()->getFlashdata('bulk_report'); ?>
        <?php if (!empty($bulkReport) && is_array($bulkReport)) : ?>
            <div class="app-card shadow-sm mb-3 border <?= ((int)($bulkReport['saved_count'] ?? 0) > 0) ? 'border-success' : 'border-warning' ?>">
                <div class="app-card-header p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="app-card-title mb-0 <?= ((int)($bulkReport['saved_count'] ?? 0) > 0) ? 'text-success' : 'text-warning' ?>">
                        <i class="fa-solid fa-clipboard-check me-2"></i>Laporan Tambah Banyak Perusahaan
                    </h6>
                    <div class="d-flex flex-wrap gap-2 small">
                        <span class="badge bg-success"><?= (int)($bulkReport['saved_count'] ?? 0) ?> disimpan</span>
                        <span class="badge bg-secondary"><?= (int)($bulkReport['skipped_count'] ?? 0) ?> dilewati</span>
                    </div>
                </div>
                <div class="app-card-body p-3">
                    <p class="mb-2 small text-dark">
                        Ringkasan: <strong><?= (int)($bulkReport['saved_count'] ?? 0) ?></strong> perusahaan baru disimpan,
                        <strong><?= (int)($bulkReport['skipped_count'] ?? 0) ?></strong> baris dilewati
                        (<?= (int)($bulkReport['summary']['sudah_ada'] ?? 0) ?> sudah ada,
                        <?= (int)($bulkReport['summary']['ganda'] ?? 0) ?> ganda di tempelan,
                        <?= (int)($bulkReport['summary']['tidak_valid'] ?? 0) ?> tidak valid).
                    </p>
                    <?php if (!empty($bulkReport['skipped_items'])) : ?>
                        <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0 small align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 70px;">Baris</th>
                                        <th>Nama Perusahaan</th>
                                        <th style="width: 180px;">Status</th>
                                        <th>Alasan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bulkReport['skipped_items'] as $skip) : ?>
                                        <tr>
                                            <td class="text-center">#<?= (int)($skip['row_number'] ?? 0) ?></td>
                                            <td><?= esc($skip['nama_perusahaan'] ?? '-') ?></td>
                                            <td><span class="badge bg-secondary"><?= esc($skip['status_label'] ?? '-') ?></span></td>
                                            <td><?= esc($skip['reason'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search Bar Section -->
        <div class="row g-2 mb-3 align-items-center justify-content-between">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group shadow-xs">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" 
                           id="supplierSearchInput" 
                           class="form-control border-start-0 border-end-0 ps-0" 
                           placeholder="Cari perusahaan atau produk..." 
                           autocomplete="off">
                    <button class="btn btn-outline-secondary border-start-0 text-muted d-none" 
                            type="button" 
                            id="supplierSearchClearBtn" 
                            title="Hapus pencarian">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 col-md-auto text-md-end">
                <span id="supplierSearchCount" class="text-muted small fw-medium">
                    Menampilkan <span id="visibleSupplierCount"><?= count($suppliers) ?></span> dari <?= count($suppliers) ?> perusahaan
                </span>
            </div>
        </div>

        <!-- Table Card -->
        <div class="app-card app-card-orders-table shadow-sm mb-4">
            <div class="app-card-body">
                <div class="table-responsive">
                    <table class="table app-table-hover mb-0 text-left align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">No</th>
                                <th>Nama Perusahaan</th>
                                <th class="text-center" style="width: 150px;">Jumlah Produk</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($suppliers)) : ?>
                                <?php foreach ($suppliers as $index => $sup) : ?>
                                    <?php
                                        $sid = (int)$sup['id_supplier'];
                                        $prods = $supplierProducts[$sid] ?? [];
                                        $targetId = 'supplier-prods-' . $sid;
                                    ?>
                                    <tr class="supplier-row" 
                                        data-company-name="<?= esc(mb_strtolower($sup['nama_perusahaan']), 'attr') ?>"
                                        data-target-id="<?= $targetId ?>"
                                        role="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#<?= $targetId ?>" 
                                        data-toggle="collapse" 
                                        data-target="#<?= $targetId ?>" 
                                        aria-expanded="false" 
                                        aria-controls="<?= $targetId ?>" 
                                        style="cursor: pointer;">
                                        <td class="text-center text-muted"><?= $index + 1 ?></td>
                                        <td>
                                            <span class="fw-semibold text-dark"><?= esc($sup['nama_perusahaan']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary"><?= count($prods) ?> produk</span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#<?= $targetId ?>" data-toggle="collapse" data-target="#<?= $targetId ?>" aria-expanded="false" aria-controls="<?= $targetId ?>" onclick="event.stopPropagation();">
                                                <i class="fa-solid fa-chevron-down me-1"></i>Buka/Tutup
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="supplier-detail-row border-0" data-target-id="<?= $targetId ?>">
                                        <td colspan="4" class="p-0 border-0">
                                            <div class="collapse" id="<?= $targetId ?>">
                                                <div class="p-3 bg-light border-bottom">
                                                    <?php if (!empty($prods)) : ?>
                                                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-3">
                                                            <?php foreach ($prods as $prod) : ?>
                                                                <?php
                                                                    $imgUrl = !empty($prod['foto_url']) ? $prod['foto_url'] : base_url('assets/img/logo.png');
                                                                    $isPlaceholder = empty($prod['foto_url']);
                                                                    $hasLink = !empty($prod['link']);
                                                                ?>
                                                                <div class="col product-col" data-product-name="<?= esc(mb_strtolower($prod['nama_produk']), 'attr') ?>">
                                                                    <?php if ($hasLink) : ?>
                                                                        <a href="<?= esc($prod['link']) ?>" target="_blank" rel="noopener" class="card h-100 text-decoration-none text-reset border shadow-xs product-card-hover product-card" title="<?= esc($prod['nama_produk']) ?>">
                                                                    <?php else : ?>
                                                                        <div class="card h-100 border shadow-xs product-card" title="<?= esc($prod['nama_produk']) ?>">
                                                                    <?php endif; ?>

                                                                        <div class="ratio ratio-4x3 bg-white rounded-top overflow-hidden position-relative border-bottom d-flex align-items-center justify-content-center">
                                                                            <img src="<?= esc($imgUrl) ?>" 
                                                                                 alt="<?= esc($prod['nama_produk']) ?>" 
                                                                                 loading="lazy" 
                                                                                 class="w-100 h-100" 
                                                                                 style="object-fit: <?= $isPlaceholder ? 'contain' : 'cover' ?>; padding: <?= $isPlaceholder ? '12px' : '0' ?>;" 
                                                                                 onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>'; this.style.objectFit='contain'; this.style.padding='12px';">
                                                                        </div>

                                                                        <div class="card-body p-2 d-flex flex-column justify-content-start">
                                                                            <div class="product-title-clamp text-dark fw-medium small">
                                                                                <?= esc($prod['nama_produk']) ?>
                                                                            </div>
                                                                        </div>

                                                                    <?php if ($hasLink) : ?>
                                                                        </a>
                                                                    <?php else : ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else : ?>
                                                        <span class="text-muted fst-italic">Belum ada produk</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Tidak ada data supplier.</td>
                                </tr>
                            <?php endif; ?>
                            <!-- Baris jika pencarian tidak menemukan hasil -->
                            <tr id="supplierSearchNoResult" class="d-none">
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <div class="py-2">
                                        <i class="fa-solid fa-magnifying-glass fa-2x mb-3 text-secondary opacity-50 d-block"></i>
                                        <span class="fs-6">Tidak ada perusahaan atau produk yang cocok dengan &lsquo;<strong id="searchKeywordDisplay" class="text-dark"></strong>&rsquo;</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div><!--//table-responsive-->
            </div><!--//app-card-body-->
        </div><!--//app-card-->

        <!-- Unmatched Products Section (hanya jika ada) -->
        <?php if (!empty($unmatchedProducts)) : ?>
            <div class="app-card shadow-sm mb-4 border-warning">
                <div class="app-card-header p-3 border-bottom">
                    <h6 class="app-card-title mb-0 text-warning">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Tidak terhubung ke supplier
                    </h6>
                </div>
                <div class="app-card-body p-3">
                    <ol class="mb-0 ps-3">
                        <?php foreach ($unmatchedProducts as $unmatched) : ?>
                            <li class="py-1 text-dark"><?= esc($unmatched['nama_produk'] ?? '-') ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        <?php endif; ?>

    </div><!--//container-xl-->
</div><!--//app-content-->

<!-- Modal Tambah Satu Perusahaan -->
<div class="modal fade" id="modalTambahPerusahaan" tabindex="-1" aria-labelledby="modalTambahPerusahaanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form action="<?= base_url('admin/data-supplier/tambah-perusahaan') ?>" method="POST" id="formTambahPerusahaan">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahPerusahaanLabel">
                        <i class="fa-solid fa-building-circle-check text-primary me-2"></i>Tambah Perusahaan Supplier
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (session()->getFlashdata('error_single')) : ?>
                        <div class="alert alert-danger small mb-3" role="alert">
                            <i class="fa-solid fa-circle-exclamation me-1"></i>
                            <?= esc(session()->getFlashdata('error_single')) ?>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="add_nama_perusahaan" class="form-label fw-semibold">
                                Nama Perusahaan <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="nama_perusahaan"
                                   id="add_nama_perusahaan"
                                   class="form-control"
                                   maxlength="255"
                                   value="<?= esc(old('nama_perusahaan', ''), 'attr') ?>"
                                   placeholder="Contoh: PT Sumber Makmur Jaya"
                                   required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="add_nama_kontak" class="form-label fw-semibold">Nama Kontak</label>
                            <input type="text"
                                   name="nama_kontak"
                                   id="add_nama_kontak"
                                   class="form-control"
                                   maxlength="150"
                                   value="<?= esc(old('nama_kontak', ''), 'attr') ?>"
                                   placeholder="Nama PIC / Kontak">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="add_email" class="form-label fw-semibold">Email</label>
                            <input type="email"
                                   name="email"
                                   id="add_email"
                                   class="form-control"
                                   maxlength="150"
                                   value="<?= esc(old('email', ''), 'attr') ?>"
                                   placeholder="nama@perusahaan.com">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="add_no_hp" class="form-label fw-semibold">No. HP / WhatsApp</label>
                            <input type="text"
                                   name="no_hp"
                                   id="add_no_hp"
                                   class="form-control"
                                   maxlength="30"
                                   value="<?= esc(old('no_hp', ''), 'attr') ?>"
                                   placeholder="0812xxxx">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="add_sumber_tahun" class="form-label fw-semibold">Sumber Tahun</label>
                            <input type="text"
                                   name="sumber_tahun"
                                   id="add_sumber_tahun"
                                   class="form-control"
                                   maxlength="20"
                                   value="<?= esc(old('sumber_tahun', ''), 'attr') ?>"
                                   placeholder="Contoh: 2025">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="add_website" class="form-label fw-semibold">Website</label>
                            <input type="text"
                                   name="website"
                                   id="add_website"
                                   class="form-control"
                                   maxlength="255"
                                   value="<?= esc(old('website', ''), 'attr') ?>"
                                   placeholder="contoh: perusahaan.co.id">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="add_instagram" class="form-label fw-semibold">Instagram</label>
                            <input type="text"
                                   name="instagram"
                                   id="add_instagram"
                                   class="form-control"
                                   maxlength="255"
                                   value="<?= esc(old('instagram', ''), 'attr') ?>"
                                   placeholder="contoh: @akunperusahaan">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="add_domisili_usaha" class="form-label fw-semibold">Domisili Usaha</label>
                            <input type="text"
                                   name="domisili_usaha"
                                   id="add_domisili_usaha"
                                   class="form-control"
                                   maxlength="255"
                                   value="<?= esc(old('domisili_usaha', ''), 'attr') ?>"
                                   placeholder="Kota / Provinsi">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="add_jenis_perusahaan" class="form-label fw-semibold">Jenis Perusahaan</label>
                            <input type="text"
                                   name="jenis_perusahaan"
                                   id="add_jenis_perusahaan"
                                   class="form-control"
                                   maxlength="50"
                                   value="<?= esc(old('jenis_perusahaan', ''), 'attr') ?>"
                                   placeholder="Contoh: Manufaktur / Trading">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary text-white">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perusahaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Banyak Perusahaan -->
<div class="modal fade" id="modalTambahBanyak" tabindex="-1" aria-labelledby="modalTambahBanyakLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahBanyakLabel">
                    <i class="fa-solid fa-table text-primary me-2"></i>Tambah Banyak Perusahaan (Tempel dari Excel)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-info me-1"></i>Urutan Kolom (dipisah Tab dari Excel):</div>
                    <div class="font-monospace">
                        1. nama_perusahaan <span class="text-danger fw-bold">(wajib)</span> &rarr;
                        2. nama_kontak &rarr;
                        3. email &rarr;
                        4. no_hp &rarr;
                        5. website &rarr;
                        6. instagram &rarr;
                        7. domisili_usaha &rarr;
                        8. jenis_perusahaan
                    </div>
                    <div class="mt-1 text-muted">
                        Satu baris = satu perusahaan. Hanya kolom pertama yang wajib. Baris pertama berupa judul kolom akan diabaikan otomatis jika terdeteksi. Maksimal <strong>300 baris</strong> per tempelan.
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-sm-6 col-md-4">
                        <label for="bulkSumberTahun" class="form-label fw-semibold">Sumber Tahun (Opsional)</label>
                        <input type="text"
                               id="bulkSumberTahun"
                               class="form-control"
                               maxlength="20"
                               placeholder="Contoh: 2025">
                    </div>
                    <div class="col-12">
                        <label for="bulkRawData" class="form-label fw-semibold">Tempel Data dari Excel</label>
                        <textarea id="bulkRawData"
                                  class="form-control font-monospace small"
                                  rows="7"
                                  placeholder="Salin langsung dari Excel lalu tempel di sini (Ctrl+V)...&#10;Contoh:&#10;PT Maju Jaya&#9;Budi&#9;budi@majujaya.co.id&#9;08123456789&#9;majujaya.co.id&#9;@majujaya&#9;Surabaya&#9;Manufaktur"></textarea>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <button type="button" class="btn btn-primary text-white" id="btnBulkCheck">
                        <i class="fa-solid fa-magnifying-glass-chart me-1"></i> Periksa
                    </button>
                    <span id="bulkCheckStatusText" class="text-muted small"></span>
                </div>

                <div id="bulkCheckError" class="alert alert-danger small d-none" role="alert"></div>

                <!-- Pratinjau Hasil Pemeriksaan -->
                <div id="bulkPreviewContainer" class="d-none">
                    <hr>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <h6 class="mb-0 fw-bold">Pratinjau Data</h6>
                        <div class="d-flex flex-wrap gap-2 small">
                            <span class="badge bg-success" id="bulkSummaryBaru">0 baru</span>
                            <span class="badge bg-warning text-dark" id="bulkSummarySudahAda">0 sudah ada</span>
                            <span class="badge bg-info text-dark" id="bulkSummaryGanda">0 ganda</span>
                            <span class="badge bg-danger" id="bulkSummaryTidakValid">0 tidak valid</span>
                        </div>
                    </div>
                    <div id="bulkHeaderSkippedNote" class="text-muted small mb-2 d-none">
                        <i class="fa-solid fa-check me-1"></i>Baris pertama terdeteksi sebagai judul kolom dan diabaikan otomatis.
                    </div>
                    <div class="table-responsive border rounded" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0 small align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="text-center" style="width: 70px;">Baris</th>
                                    <th>Nama Perusahaan</th>
                                    <th style="width: 190px;">Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="bulkPreviewTbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <form action="<?= base_url('admin/data-supplier/banyak-simpan') ?>" method="POST" id="formBulkSave" class="m-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="raw_data" id="bulkSaveRawData" value="">
                    <input type="hidden" name="sumber_tahun" id="bulkSaveSumberTahun" value="">
                    <button type="submit" class="btn btn-success text-white d-none" id="btnBulkSave" disabled>
                        <i class="fa-solid fa-floppy-disk me-1"></i> <span id="btnBulkSaveLabel">Simpan 0 Perusahaan Baru</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-buka kembali modal Tambah Satu Perusahaan jika terjadi error validasi / duplikat
    <?php if (session()->getFlashdata('open_modal') === 'single') : ?>
    (function () {
        var singleModalEl = document.getElementById('modalTambahPerusahaan');
        if (singleModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(singleModalEl).show();
        }
    })();
    <?php endif; ?>

    // Logika Tambah Banyak (Periksa & Simpan)
    var csrfTokenName = <?= json_encode(csrf_token()) ?>;
    var csrfHash      = <?= json_encode(csrf_hash()) ?>;

    var btnBulkCheck       = document.getElementById('btnBulkCheck');
    var bulkRawData        = document.getElementById('bulkRawData');
    var bulkSumberTahun    = document.getElementById('bulkSumberTahun');
    var bulkCheckStatus    = document.getElementById('bulkCheckStatusText');
    var bulkCheckError     = document.getElementById('bulkCheckError');
    var bulkPreviewBox     = document.getElementById('bulkPreviewContainer');
    var bulkPreviewTbody   = document.getElementById('bulkPreviewTbody');
    var bulkHeaderNote     = document.getElementById('bulkHeaderSkippedNote');
    var sumBaruEl          = document.getElementById('bulkSummaryBaru');
    var sumSudahAdaEl      = document.getElementById('bulkSummarySudahAda');
    var sumGandaEl         = document.getElementById('bulkSummaryGanda');
    var sumTidakValidEl    = document.getElementById('bulkSummaryTidakValid');
    var formBulkSave       = document.getElementById('formBulkSave');
    var bulkSaveRawInput   = document.getElementById('bulkSaveRawData');
    var bulkSaveTahunInput = document.getElementById('bulkSaveSumberTahun');
    var btnBulkSave        = document.getElementById('btnBulkSave');
    var btnBulkSaveLabel   = document.getElementById('btnBulkSaveLabel');

    function syncCsrfTokens(tokenName, tokenHash) {
        if (!tokenName || !tokenHash) return;
        csrfTokenName = tokenName;
        csrfHash      = tokenHash;
        var inputs = document.querySelectorAll('input[name="' + tokenName + '"]');
        inputs.forEach(function (inp) {
            inp.value = tokenHash;
        });
    }

    function resetBulkPreview() {
        if (bulkPreviewBox) bulkPreviewBox.classList.add('d-none');
        if (bulkCheckError) {
            bulkCheckError.classList.add('d-none');
            bulkCheckError.textContent = '';
        }
        if (bulkPreviewTbody) bulkPreviewTbody.innerHTML = '';
        if (btnBulkSave) {
            btnBulkSave.classList.add('d-none');
            btnBulkSave.disabled = true;
        }
        if (bulkSaveRawInput) bulkSaveRawInput.value = '';
        if (bulkSaveTahunInput) bulkSaveTahunInput.value = '';
    }

    if (bulkRawData) {
        bulkRawData.addEventListener('input', resetBulkPreview);
    }
    if (bulkSumberTahun) {
        bulkSumberTahun.addEventListener('input', resetBulkPreview);
    }

    function getStatusBadgeClass(code) {
        switch (code) {
            case 'new':       return 'bg-success';
            case 'exists':    return 'bg-warning text-dark';
            case 'duplicate': return 'bg-info text-dark';
            default:          return 'bg-danger';
        }
    }

    if (btnBulkCheck) {
        btnBulkCheck.addEventListener('click', function () {
            resetBulkPreview();

            var rawVal   = bulkRawData ? bulkRawData.value : '';
            var tahunVal = bulkSumberTahun ? bulkSumberTahun.value : '';

            if (rawVal.trim() === '') {
                if (bulkCheckError) {
                    bulkCheckError.textContent = 'Silakan tempel data dari Excel terlebih dahulu.';
                    bulkCheckError.classList.remove('d-none');
                }
                return;
            }

            btnBulkCheck.disabled = true;
            if (bulkCheckStatus) bulkCheckStatus.textContent = 'Memeriksa data...';

            var formData = new FormData();
            formData.append('raw_data', rawVal);
            formData.append('sumber_tahun', tahunVal);
            formData.append(csrfTokenName, csrfHash);

            fetch('<?= base_url('admin/data-supplier/banyak-periksa') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(function (resp) {
                return resp.json().then(function (data) {
                    return { ok: resp.ok, data: data };
                });
            })
            .then(function (res) {
                var data = res.data || {};
                if (data.csrf_token && data.csrf_hash) {
                    syncCsrfTokens(data.csrf_token, data.csrf_hash);
                }

                if (!res.ok || !data.ok) {
                    if (bulkCheckError) {
                        bulkCheckError.textContent = data.message || 'Gagal memeriksa data.';
                        bulkCheckError.classList.remove('d-none');
                    }
                    return;
                }

                var summary = data.summary || {};
                var rows    = Array.isArray(data.rows) ? data.rows : [];
                var baruCnt = parseInt(summary.baru || 0, 10);

                if (sumBaruEl) sumBaruEl.textContent = baruCnt + ' baru';
                if (sumSudahAdaEl) sumSudahAdaEl.textContent = (summary.sudah_ada || 0) + ' sudah ada';
                if (sumGandaEl) sumGandaEl.textContent = (summary.ganda || 0) + ' ganda';
                if (sumTidakValidEl) sumTidakValidEl.textContent = (summary.tidak_valid || 0) + ' tidak valid';

                if (bulkHeaderNote) {
                    if (data.header_skipped) {
                        bulkHeaderNote.classList.remove('d-none');
                    } else {
                        bulkHeaderNote.classList.add('d-none');
                    }
                }

                if (bulkPreviewTbody) {
                    bulkPreviewTbody.innerHTML = '';
                    rows.forEach(function (item) {
                        var tr = document.createElement('tr');

                        var tdRow = document.createElement('td');
                        tdRow.className = 'text-center text-muted';
                        tdRow.textContent = '#' + (item.row_number || '');

                        var tdName = document.createElement('td');
                        tdName.className = 'fw-medium';
                        tdName.textContent = item.nama_perusahaan || '';

                        var tdStatus = document.createElement('td');
                        var badge = document.createElement('span');
                        badge.className = 'badge ' + getStatusBadgeClass(item.status_code);
                        badge.textContent = item.status_label || '';
                        tdStatus.appendChild(badge);

                        var tdReason = document.createElement('td');
                        tdReason.className = 'text-muted';
                        tdReason.textContent = item.reason || '';

                        tr.appendChild(tdRow);
                        tr.appendChild(tdName);
                        tr.appendChild(tdStatus);
                        tr.appendChild(tdReason);
                        bulkPreviewTbody.appendChild(tr);
                    });
                }

                if (bulkPreviewBox) bulkPreviewBox.classList.remove('d-none');

                if (bulkSaveRawInput) bulkSaveRawInput.value = rawVal;
                if (bulkSaveTahunInput) bulkSaveTahunInput.value = tahunVal;

                if (btnBulkSave && btnBulkSaveLabel) {
                    btnBulkSaveLabel.textContent = 'Simpan ' + baruCnt + ' Perusahaan Baru';
                    btnBulkSave.classList.remove('d-none');
                    btnBulkSave.disabled = (baruCnt <= 0);
                }
            })
            .catch(function () {
                if (bulkCheckError) {
                    bulkCheckError.textContent = 'Terjadi kesalahan jaringan saat memeriksa data. Silakan muat ulang halaman dan coba lagi.';
                    bulkCheckError.classList.remove('d-none');
                }
            })
            .finally(function () {
                btnBulkCheck.disabled = false;
                if (bulkCheckStatus) bulkCheckStatus.textContent = '';
            });
        });
    }

    if (formBulkSave && btnBulkSave) {
        formBulkSave.addEventListener('submit', function () {
            btnBulkSave.disabled = true;
            if (btnBulkSaveLabel) {
                btnBulkSaveLabel.textContent = 'Menyimpan...';
            }
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('supplierSearchInput');
    const clearBtn = document.getElementById('supplierSearchClearBtn');
    const visibleCountEl = document.getElementById('visibleSupplierCount');
    const noResultRow = document.getElementById('supplierSearchNoResult');
    const keywordDisplay = document.getElementById('searchKeywordDisplay');

    if (!searchInput) return;

    const supplierRows = document.querySelectorAll('.supplier-row');
    const totalCount = supplierRows.length;

    // Cache daftar elemen perusahaan dan produknya untuk performa optimal
    const suppliersData = Array.from(supplierRows).map(function (row) {
        const targetId = row.getAttribute('data-target-id');
        const detailRow = document.querySelector('.supplier-detail-row[data-target-id="' + targetId + '"]');
        const collapseEl = document.getElementById(targetId);
        const toggleBtn = row.querySelector('button[data-bs-toggle="collapse"], button[data-toggle="collapse"]');
        const companyName = (row.getAttribute('data-company-name') || '').trim();

        const productCols = detailRow ? Array.from(detailRow.querySelectorAll('.product-col')) : [];
        const products = productCols.map(function (col) {
            return {
                col: col,
                card: col.querySelector('.product-card'),
                name: (col.getAttribute('data-product-name') || '').trim()
            };
        });

        return {
            row: row,
            detailRow: detailRow,
            collapseEl: collapseEl,
            toggleBtn: toggleBtn,
            companyName: companyName,
            products: products
        };
    });

    function openSection(item) {
        if (!item.collapseEl) return;
        item.collapseEl.classList.remove('collapsing');
        item.collapseEl.classList.add('collapse', 'show');
        item.collapseEl.style.height = '';
        if (item.row) item.row.setAttribute('aria-expanded', 'true');
        if (item.toggleBtn) item.toggleBtn.setAttribute('aria-expanded', 'true');
    }

    function closeSection(item) {
        if (!item.collapseEl) return;
        item.collapseEl.classList.remove('collapsing', 'show');
        item.collapseEl.classList.add('collapse');
        item.collapseEl.style.height = '';
        if (item.row) item.row.setAttribute('aria-expanded', 'false');
        if (item.toggleBtn) item.toggleBtn.setAttribute('aria-expanded', 'false');
    }

    function performSearch() {
        const rawQuery = searchInput.value;
        const query = rawQuery.trim().toLowerCase();

        // Tampilkan atau sembunyikan tombol clear (x)
        if (rawQuery.length > 0) {
            clearBtn.classList.remove('d-none');
        } else {
            clearBtn.classList.add('d-none');
        }

        // Jika query kosong, kembalikan semua ke kondisi default semula
        if (query === '') {
            suppliersData.forEach(function (item) {
                item.row.style.display = '';
                if (item.detailRow) item.detailRow.style.display = '';
                closeSection(item);
                item.products.forEach(function (prod) {
                    if (prod.card) prod.card.classList.remove('product-card-match');
                });
            });

            if (noResultRow) noResultRow.classList.add('d-none');
            if (visibleCountEl) visibleCountEl.textContent = totalCount;
            return;
        }

        let visibleCount = 0;

        suppliersData.forEach(function (item) {
            const companyMatched = item.companyName.includes(query);
            let productMatchCount = 0;

            item.products.forEach(function (prod) {
                const prodMatched = prod.name.includes(query);
                if (prodMatched) {
                    productMatchCount++;
                    if (prod.card) prod.card.classList.add('product-card-match');
                } else {
                    if (prod.card) prod.card.classList.remove('product-card-match');
                }
            });

            if (companyMatched || productMatchCount > 0) {
                // Tampilkan perusahaan
                item.row.style.display = '';
                if (item.detailRow) item.detailRow.style.display = '';
                visibleCount++;

                if (productMatchCount > 0) {
                    // Jika produk cocok: buka otomatis bagian produk & highlight produk yang cocok
                    openSection(item);
                } else {
                    // Jika hanya nama perusahaan yang cocok: tampilkan perusahaan seperti biasa (tetap tertutup)
                    closeSection(item);
                }
            } else {
                // Sembunyikan perusahaan jika tidak cocok nama perusahaan maupun produknya
                item.row.style.display = 'none';
                if (item.detailRow) item.detailRow.style.display = 'none';
                closeSection(item);
            }
        });

        if (visibleCountEl) visibleCountEl.textContent = visibleCount;

        if (visibleCount === 0) {
            if (noResultRow) {
                noResultRow.classList.remove('d-none');
                if (keywordDisplay) keywordDisplay.textContent = rawQuery.trim();
            }
        } else {
            if (noResultRow) noResultRow.classList.add('d-none');
        }
    }

    // Debounce ~200 ms untuk responsivitas live search yang ringan
    let debounceTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performSearch, 200);
    });

    // Hapus pencarian saat klik tombol "x"
    clearBtn.addEventListener('click', function () {
        clearTimeout(debounceTimer);
        searchInput.value = '';
        performSearch();
        searchInput.focus();
    });

    // Tekan tombol Escape untuk membersihkan kotak pencarian
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            clearTimeout(debounceTimer);
            searchInput.value = '';
            performSearch();
        }
    });
});
</script>

<?= $this->endSection(); ?>

