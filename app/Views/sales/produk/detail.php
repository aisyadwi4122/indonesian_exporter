<?= $this->extend('sales/layout') ?>

<?= $this->section('content') ?>

<style>
.nav-tabs .nav-link {
    color: #64748b;
    border: none;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s ease;
    background: transparent;
}
.nav-tabs .nav-link:hover {
    color: #16a34a;
    border-color: transparent;
    background-color: #f8fafc;
}
.nav-tabs .nav-link.active {
    color: #16a34a !important;
    background-color: transparent !important;
    border-bottom: 3px solid #16a34a !important;
}
.label-col {
    color: #64748b;
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
    border-bottom: 1px solid #f1f5f9;
}
.info-row:last-child {
    border-bottom: none;
}
.desc-content {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    font-size: 13.5px;
    line-height: 1.65;
    color: #334155;
}
.desc-content p {
    margin-bottom: 0.75rem;
}
.desc-content p:last-child {
    margin-bottom: 0;
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
</style>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0" style="font-size: 13.5px;">
        <li class="breadcrumb-item">
            <a href="<?= base_url('sales/produk') ?>" class="text-decoration-none text-success">
                <i class="fa-solid fa-box-open me-1"></i>Produk
            </a>
        </li>
        <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 400px;">
            <?= esc($product['nama_produk_id'] ?? $product['nama_produk_en'] ?? 'Detail Produk') ?>
        </li>
    </ol>
</nav>

<!-- Header Title & Back Button -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
            <h2 class="fw-bold mb-0" style="font-size: 24px; color: #1e293b;">
                <?= esc($product['nama_produk_id'] ?? '-') ?>
            </h2>
            <?php 
                $kategori = !empty($product['nama_kategori_id']) 
                    ? $product['nama_kategori_id'] 
                    : (!empty($product['nama_kategori_en']) ? $product['nama_kategori_en'] : 'Umum');
            ?>
            <span class="badge" style="background-color: #dcfce7; color: #15803d; font-size: 13px; font-weight: 600; padding: 6px 12px; border-radius: 6px;">
                <i class="fa-solid fa-tag me-1"></i><?= esc($kategori) ?>
            </span>
        </div>
        <?php if (!empty($product['nama_produk_en'])): ?>
            <p class="text-muted mb-0" style="font-size: 14.5px;">
                <?= esc($product['nama_produk_en']) ?>
            </p>
        <?php endif; ?>
    </div>
    <div>
        <a href="<?= base_url('sales/produk') ?>" class="btn btn-sales-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
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
            <i class="fa-solid fa-building me-2 text-success"></i>Data Perusahaan
        </button>
    </li>
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
</ul>

<!-- Tab Contents -->
<div class="tab-content" id="detailTabsContent">

    <!-- ================= TAB 1: DATA PRODUK ================= -->
    <div class="tab-pane fade show active" id="tab-produk" role="tabpanel" aria-labelledby="tab-produk-btn">
        <div class="row g-4">
            <!-- Kolom Kiri: Foto Utama & Galeri -->
            <div class="col-lg-4 col-md-5">
                <div class="sales-card p-3 mb-4 text-center">
                    <div style="background-color: #f8fafc; border-radius: 8px; overflow: hidden; height: 320px; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0;">
                        <?php
                            $fotoProduk = !empty($product['foto_produk']) ? esc($product['foto_produk']) : '';
                            if (!empty($fotoProduk)) {
                                if (str_starts_with($fotoProduk, 'http://') || str_starts_with($fotoProduk, 'https://')) {
                                    $fotoUtamaUrl = $fotoProduk;
                                } elseif (str_starts_with($fotoProduk, 'assets/')) {
                                    $fotoUtamaUrl = base_url($fotoProduk);
                                } else {
                                    $fotoUtamaUrl = base_url('assets/img/produk/' . $fotoProduk);
                                }
                            } else {
                                $fotoUtamaUrl = base_url('assets/img/logo.png');
                            }
                        ?>
                        <img id="mainProductImage" src="<?= $fotoUtamaUrl ?>" 
                             alt="<?= esc($product['nama_produk_id'] ?? 'Foto Produk') ?>" 
                             class="img-fluid"
                             style="max-height: 100%; max-width: 100%; object-fit: contain; padding: 12px;"
                             onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>'; this.style.padding='24px';">
                    </div>

                    <?php
                        $galleryPhotos = [];
                        if (!empty($produkSupplier)) {
                            for ($i = 1; $i <= 5; $i++) {
                                $key = 'foto_' . $i;
                                if (!empty($produkSupplier[$key])) {
                                    $galleryPhotos[] = $produkSupplier[$key];
                                }
                            }
                        }
                    ?>

                    <?php if (!empty($galleryPhotos)): ?>
                        <div class="mt-3 pt-3 border-top">
                            <div class="text-muted small fw-semibold mb-2 text-start">Galeri Foto</div>
                            <div class="d-flex flex-wrap gap-2 justify-content-start">
                                <?php foreach ($galleryPhotos as $gPhoto): ?>
                                    <?php
                                        $gUrl = (str_starts_with($gPhoto, 'http://') || str_starts_with($gPhoto, 'https://'))
                                            ? $gPhoto
                                            : base_url($gPhoto);
                                    ?>
                                    <a href="<?= esc($gUrl) ?>" target="_blank" rel="noopener" class="border rounded p-1 d-inline-block bg-white shadow-sm" style="width: 58px; height: 58px;">
                                        <img src="<?= esc($gUrl) ?>" alt="Foto Galeri" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;"
                                             onerror="this.onerror=null; this.src='<?= base_url('assets/img/logo.png') ?>';">
                                    </a>
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
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Nama Produk (Inggris)</div>
                        <div class="col-sm-8 value-col"><?= esc($product['nama_produk_en'] ?? '-') ?></div>
                    </div>
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

                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Spesifikasi</div>
                        <div class="col-sm-8 value-col"><?= !empty($produkSupplier['spesifikasi']) ? esc($produkSupplier['spesifikasi']) : '-' ?></div>
                    </div>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Kapasitas Produksi</div>
                        <div class="col-sm-8 value-col"><?= !empty($produkSupplier['kapasitas_produksi']) ? esc($produkSupplier['kapasitas_produksi']) : '-' ?></div>
                    </div>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Legalitas</div>
                        <div class="col-sm-8 value-col"><?= !empty($produkSupplier['legalitas']) ? esc($produkSupplier['legalitas']) : '-' ?></div>
                    </div>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Sertifikasi</div>
                        <div class="col-sm-8 value-col"><?= !empty($produkSupplier['sertifikasi']) ? esc($produkSupplier['sertifikasi']) : '-' ?></div>
                    </div>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Lokasi Barang</div>
                        <div class="col-sm-8 value-col"><?= !empty($produkSupplier['lokasi_barang']) ? esc($produkSupplier['lokasi_barang']) : '-' ?></div>
                    </div>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">MOQ</div>
                        <div class="col-sm-8 value-col"><?= !empty($produkSupplier['moq']) ? esc($produkSupplier['moq']) : '-' ?></div>
                    </div>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Harga EXW</div>
                        <div class="col-sm-8 value-col"><?= !empty($produkSupplier['harga_exw']) ? esc($produkSupplier['harga_exw']) : '-' ?></div>
                    </div>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">
                            Komisi
                            <span class="badge bg-secondary ms-1" style="font-size: 10px; font-weight: 500;">Internal</span>
                        </div>
                        <div class="col-sm-8 value-col"><?= !empty($produkSupplier['komisi']) ? esc($produkSupplier['komisi']) : '-' ?></div>
                    </div>
                    <div class="row info-row">
                        <div class="col-sm-4 label-col">Sudah Ekspor</div>
                        <div class="col-sm-8 value-col"><?= !empty($produkSupplier['sudah_ekspor']) ? esc($produkSupplier['sudah_ekspor']) : '-' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= TAB 2: DATA PERUSAHAAN ================= -->
    <div class="tab-pane fade" id="tab-perusahaan" role="tabpanel" aria-labelledby="tab-perusahaan-btn">
        <div class="sales-card mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 17px;">
                    <i class="fa-solid fa-building text-success"></i>
                    <?= esc(!empty($supplier['nama_perusahaan']) ? $supplier['nama_perusahaan'] : 'Belum ada data perusahaan') ?>
                </h5>
                <?php if (!empty($supplier['jenis_perusahaan'])): ?>
                    <span class="badge bg-light text-dark border"><?= esc($supplier['jenis_perusahaan']) ?></span>
                <?php endif; ?>
            </div>

            <?php if (empty($supplier)): ?>
                <div class="alert alert-light border py-2 px-3 mb-4 d-flex align-items-center gap-2" style="background-color: #f8fafc; font-size: 13.5px; color: #64748b; border-color: #e2e8f0 !important;">
                    <i class="fa-solid fa-circle-info text-secondary"></i>
                    <span>Data perusahaan belum tersedia untuk produk ini.</span>
                </div>
            <?php endif; ?>

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
                                <?php
                                    $links = array_filter(array_map('trim', explode(',', $supplier['link_foto_usaha'])));
                                ?>
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
                                <?php
                                    $links = array_filter(array_map('trim', explode(',', $supplier['link_dokumen_legalitas'])));
                                ?>
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
                                <?php
                                    $links = array_filter(array_map('trim', explode(',', $supplier['link_dokumen_sertifikat'])));
                                ?>
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

            <!-- Bagian 3: Ekspor Terakhir -->
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
        </div>
    </div>

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

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // -----------------------------------------------------------------
    // 1. Tooltip Bootstrap
    // -----------------------------------------------------------------
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // -----------------------------------------------------------------
    // 2. Hash URL Synchronization (#produk, #perusahaan, #history)
    // -----------------------------------------------------------------
    const hashToTab = {
        '#produk': '#tab-produk-btn',
        '#perusahaan': '#tab-perusahaan-btn',
        '#history': '#tab-history-btn'
    };

    function activateTabByHash() {
        const hash = window.location.hash;
        if (hash && hashToTab[hash]) {
            const targetBtn = document.querySelector(hashToTab[hash]);
            if (targetBtn) {
                const tabInstance = bootstrap.Tab.getOrCreateInstance(targetBtn);
                tabInstance.show();
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
            } else if (target === '#tab-history') {
                history.replaceState(null, null, '#history');
            }
        });
    });

    // Pulihkan tab saat halaman pertama kali dibuka / di-refresh / hash diubah
    activateTabByHash();
    window.addEventListener('hashchange', activateTabByHash);

    // -----------------------------------------------------------------
    // 3. Client-side Filter Tahun (Data History Ekspor)
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
});
</script>
<?= $this->endSection() ?>
