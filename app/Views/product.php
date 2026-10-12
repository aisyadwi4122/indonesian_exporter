<?= $this->extend('layouts/template'); ?>

<?= $this->section('content'); ?>

<?php
// FIX META DINAMIS
$useMeta = isset($metaCategory) ? $metaCategory : $meta;

$meta_title = $lang === 'id'
    ? ($useMeta['title_id'] ?? $useMeta['nama_halaman_id'])
    : ($useMeta['title_en'] ?? $useMeta['nama_halaman_en']);

$meta_description = $lang === 'id'
    ? ($useMeta['meta_desc_id'] ?? $useMeta['deskripsi_halaman_id'])
    : ($useMeta['meta_desc_en'] ?? $useMeta['deskripsi_halaman_en']);
?>

<?php
// Ambil data halaman dari database berdasarkan id_meta (contoh: halaman Contact)
$page_id = 3; // ID untuk halaman Contact
$lang = service('request')->getLocale(); // Ambil bahasa aktif, bisa 'id' atau 'en'

// Ambil query builder service
$db = \Config\Database::connect();

// Query untuk mendapatkan data halaman
$query = $db->query("SELECT * FROM tb_meta WHERE id_meta = ?", [$page_id]);
$page = $query->getRow(); // Ambil baris data halaman

// Set konten dinamis
$page_title = $lang === 'id' ? $page->nama_halaman_id : $page->nama_halaman_en;
$breadcrumb_title = $lang === 'id' ? $page->nama_halaman_id : $page->nama_halaman_en;
$meta_description = $lang === 'id' ? $page->meta_desc_id : $page->meta_desc_en;
$background_image = 'assets/img/header.jpeg'; // Sesuaikan jika perlu mengganti gambar berdasarkan halaman
?>

<!-- Page Header Start -->
<div class="container-fluid page-header mb-5 p-0" style="background-image: url(<?= base_url($background_image) ?>);">
    <div class="container-fluid page-header-inner py-5">
        <div class="container text-center">
        <h2 class="display-3 text-white mb-3 animated slideInDown" style="font-size: 40px; font-weight: bold"><?= $page_title ?></h2>
            <nav aria-label="breadcrumb">
                <div class="breadcrumb justify-content-center text-uppercase">
                    <a href="<?= base_url() ?>"><?= lang('bahasa.home') ?></a>
                    <span class="text-white mx-2">/</span>
                    <!-- Menambahkan breadcrumb dinamis lebih dari satu level -->
                    <span class="text-white"><?= $breadcrumb_title ?></span>
                    <!-- Cek apakah ada subkategori atau breadcrumb lebih lanjut -->
                    <?php if (isset($sub_category_title)): ?>
                        <span class="text-white mx-2">/</span>
                        <span class="text-white"><?= $sub_category_title ?></span>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>
</div>
<!-- Page Header End -->
 
<!-- Product Start -->
<div class="title-page">
    <h6 class="sub-title">>><?= $lang == 'id' ? $meta['nama_halaman_id'] : $meta['nama_halaman_en']; ?><<</h6>
    <h1><?= $lang == 'id' ? $meta['deskripsi_halaman_id'] : $meta['deskripsi_halaman_en']; ?></h1>
</div>

<div class="container mb-5" style="padding: 20px; background-color: white; border-radius:20px;">
    <div id="produk-container" class="row g-4">
        <?php if (!empty($product) && is_array($product)) : ?>
            <?php foreach ($product as $product_item) : ?>
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 shadow-sm">
                    <img src="<?= base_url('assets/img/produk/' . esc($product_item['foto_produk'])) ?>" 
                         class="card-img-top" 
                         alt="<?= esc($product_item['alt_produk_' . $lang] ?? $product_item['alt_produk_id']) ?>" 
                         >
                    <div class="card-body">
                        <h5 class="card-title"><?= esc($product_item['nama_produk_' . $lang] ?? $product_item['nama_produk_id']) ?></h5>
                        <?php
                        $deskripsi = strip_tags($product_item['deskripsi_produk_' . $lang] ?? $product_item['deskripsi_produk_id']);
                        $kalimat = explode('.', $deskripsi);
                        $potongan = isset($kalimat[1]) ? $kalimat[0] . '.' . $kalimat[1] . '.' : $kalimat[0] . '.';
                        ?>
                        <p class="card-text" style="font-size:14px;"><?= esc($potongan, 'raw') ?></p>
                        <a href="<?= base_url(trim($lang . '/' . $productLink . '/' . $product_item['slug_' . $lang], '/')) ?>" 
                           class="btn btn-primary btn-sm"> <?= lang('bahasa.buttonProduk') ?> </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-center">Belum ada produk yang tersedia.</p>
        <?php endif; ?>
    </div>
</div>
<!-- Product End -->


<?= $this->endSection(); ?>
