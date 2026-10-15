<?= $this->extend('layouts/template'); ?>

<?= $this->section('content'); ?>

<?php
// Ambil ID halaman saat ini dan parent
$page_id = 7;
$parent_id = 3;

// Ambil bahasa aktif
$lang = service('request')->getLocale();

// Koneksi database
$db = \Config\Database::connect();

// Ambil data kategori produk untuk navbar dropdown
$query_kategori = $db->query("SELECT * FROM tb_kategori_produk");
$categoriesProduk = $query_kategori->getResultArray();
$this->setData(['categoriesProduk' => $categoriesProduk]);

// Ambil data halaman berdasarkan id_meta
$query = $db->query("SELECT * FROM tb_meta WHERE id_meta = ?", [$page_id]);
$page = $query->getRow();

// Set konten dinamis
$page_title        = $lang === 'id' ? $page->nama_halaman_id : $page->nama_halaman_en;
$breadcrumb_title  = $lang === 'id' ? $page->nama_halaman_id : $page->nama_halaman_en;
$meta_description  = $lang === 'id' ? $page->meta_desc_id : $page->meta_desc_en;
$background_image  = 'assets/img/header.jpeg';
?>

<!-- Page Header Start -->
<div class="container-fluid page-header mb-5 p-0" style="background-image: url(<?= base_url($background_image) ?>);">
    <div class="container-fluid page-header-inner py-5">
        <div class="container text-center">
            <h2 class="display-3 text-white mb-3 animated slideInDown" style="font-size: 40px; font-weight: bold">
                <?= esc($page_title) ?>
            </h2>
            <nav aria-label="breadcrumb">
                <div class="breadcrumb justify-content-center text-uppercase">
                    <a href="<?= base_url() ?>"><?= lang('bahasa.home') ?></a>
                    <span class="text-white mx-2">/</span>
                    <?php
                    if (isset($parent_id) && $parent_id) {
                        $parent_query = $db->query("SELECT * FROM tb_meta WHERE id_meta = ?", [$parent_id]);
                        $parent_page = $parent_query->getRow();
                        if ($parent_page) {
                            $parent_title = $lang === 'id' ? $parent_page->nama_halaman_id : $parent_page->nama_halaman_en;
                            echo '<a href="' . base_url($lang . '/' . strtolower(str_replace(' ', '-', $parent_title))) . '">' . esc($parent_title) . '</a>';
                            echo '<span class="text-white mx-2">/</span>';
                        }
                    }
                    echo '<span class="text-white">' . esc($breadcrumb_title) . '</span>';
                    ?>
                </div>
            </nav>
        </div>
    </div>
</div>
<!-- Page Header End -->

<!-- Detail Produk -->
<div class="container py-5">
    <div class="title-page text-center mb-4">
        <h6 class="sub-title text-muted">>><em><?= esc($page_title) ?></em><<</h6>
        <h1><?= esc($product['nama_produk_' . $lang] ?? $product['nama_produk_id']) ?></h1>
    </div>

    <div class="detproduct-detail" style="background-color:white; border-radius:20px; padding:20px; display:flex; flex-wrap:wrap; align-items:flex-start; gap:20px;">
        <div class="detproduct-img" style="flex:1 1 45%;">
            <img src="<?= base_url('assets/img/produk/' . esc($product['foto_produk'])) ?>" 
                 alt="<?= esc($product['nama_produk_' . $lang] ?? $product['nama_produk_id']) ?>" 
                 style="width:100%; height:auto; border-radius:10px;"
                 onerror="this.onerror=null; this.src='<?= base_url('assets/img/produk/default.png') ?>';">
        </div>
        <div class="detproduct-info" style="flex:1 1 50%;">
            <p class="detproduct-description" style="margin:0;">
                <?= $product['deskripsi_produk_' . $lang] ?? $product['deskripsi_produk_id'] ?>
            </p>
        </div>
    </div>

    <?php if (!empty($produkLainnya) && is_array($produkLainnya)) : ?>
        <!-- Produk Lainnya Start -->
        <div class="mt-5">
            <div class="text-center mb-4">
                <h6 class="text-primary text-uppercase">>> <?= ($lang === 'id') ? 'PRODUK LAINNYA' : 'OTHER PRODUCTS' ?> <<</h6>
                <h3><?= ($lang === 'id') ? 'Rekomendasi Produk Pilihan' : 'Recommended Products' ?></h3>
            </div>
            <div class="row g-3 justify-content-center">
                <?php foreach ($produkLainnya as $item) : ?>
                    <?php $itemSlug = !empty($item['slug_' . $lang]) ? $item['slug_' . $lang] : $item['slug_id']; ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-item text-center">
                            <div class="product-img-home">
                                <img src="<?= base_url('assets/img/produk/' . esc($item['foto_produk'])) ?>"
                                    alt="<?= esc($item['alt_produk_' . $lang] ?? $item['alt_produk_id']) ?>"
                                    onerror="this.onerror=null; this.src='<?= base_url('assets/img/produk/default.png') ?>';">
                            </div>
                            <div class="product-content">
                                <h5><?= esc($item['nama_produk_' . $lang] ?? $item['nama_produk_id']) ?></h5>
                                <a href="<?= base_url(trim($lang . '/' . ($productLink ?? ($lang === 'id' ? 'produk' : 'product')) . '/' . $itemSlug, '/')) ?>"
                                   class="btn btn-product">
                                   <?= ($lang === 'id') ? 'Info Lengkap' : 'More Info' ?>
                                   <i class="fa fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Produk Lainnya End -->
    <?php endif; ?>

    <div class="text-center mt-5">
        <a href="<?= base_url($lang . '/' . ($productLink ?? 'produk')) ?>" class="btn btn-primary btn-lg">
            <?= ($lang === 'id') ? 'Kembali' : 'Back' ?>
        </a>
    </div>
</div>

<?= $this->endSection(); ?>
