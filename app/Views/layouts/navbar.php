<?php
// Ambil bahasa yang disimpan di session
$lang = session()->get('lang') ?? 'id'; // Default ke 'id' jika tidak ada di session

$current_url = uri_string();
$query_string = $_SERVER['QUERY_STRING'] ?? '';
$segments = explode('/', $current_url);
$lang_segment = $segments[0] ?? '';

if ($lang_segment !== 'en' && $lang_segment !== 'id') {
    $lang_segment = 'id';
}

$homeLink    = ($lang_segment === 'en') ? '/' : '/';
$aboutLink   = ($lang_segment === 'en') ? 'about' : 'tentang';
$contactLink = ($lang_segment === 'en') ? 'contact' : 'kontak';
$articleLink = ($lang_segment === 'en') ? 'article' : 'artikel';
$activityLink = ($lang_segment === 'en') ? 'activity' : 'aktivitas';
$productLink = ($lang_segment === 'en') ? 'product' : 'produk';

array_shift($segments);
$url_without_lang = implode('/', $segments);
$new_lang = ($lang_segment === 'en') ? 'id' : 'en';

$replace_map = [
    'tentang' => 'about',
    'kontak' => 'contact',
    'artikel' => 'article',
    'aktivitas' => 'activity',
    'produk' => 'product',
];

foreach ($replace_map as $id => $en) {
    if ($lang_segment === 'en') {
        $url_without_lang = str_replace($en, $id, $url_without_lang);
    } else {
        $url_without_lang = str_replace($id, $en, $url_without_lang);
    }
}

$clean_url = ($url_without_lang !== '') ? "$new_lang/$url_without_lang" : $new_lang;
if (!empty($query_string)) {
    $clean_url .= '?' . $query_string;
}

$english_url = base_url($clean_url);
$indonesia_url = base_url($clean_url);

// 🔹 Tautan Kategori Artikel
$categoryLinks = [];
if (!empty($categories)) {
    foreach ($categories as $cat) {
        $slug = ($lang === 'id') ? $cat['slug_kategori_id'] : $cat['slug_kategori_en'];
        $name = ($lang === 'id') ? $cat['nama_kategori_id'] : $cat['nama_kategori_en'];
        $categoryLinks[] = [
            'url' => base_url("$lang/$articleLink/$slug"),
            'name' => $name
        ];
    }
}

// 🔹 Tautan Kategori Aktivitas
$kategoriAktivitasLinks = [];
if (!empty($categoriesAktivitas)) {
    foreach ($categoriesAktivitas as $cat) {
        $slug = ($lang === 'id') ? $cat['slug_kategori_id'] : $cat['slug_kategori_en'];
        $name = ($lang === 'id') ? $cat['nama_kategori_id'] : $cat['nama_kategori_en'];
        $kategoriAktivitasLinks[] = [
            'url' => base_url("$lang/$activityLink/$slug"),
            'name' => $name
        ];
    }
}

// 🔹 Tautan Kategori Produk
$kategoriProdukLinks = [];
if (!empty($categoriesProduk)) {
    foreach ($categoriesProduk as $cat) {
        $slug = ($lang === 'id') ? $cat['slug_kategori_id'] : $cat['slug_kategori_en'];
        $name = ($lang === 'id') ? $cat['nama_kategori_id'] : $cat['nama_kategori_en'];
        $kategoriProdukLinks[] = [
            'url' => base_url("$lang/$productLink/$slug"),
            'name' => $name,
            'slug_kategori_id' => $cat['slug_kategori_id'], // Pastikan data slug_kategori_id ada
            'nama_kategori_id' => $cat['nama_kategori_id'], // Pastikan data nama_kategori_id ada
        ];
    }
}
?>

<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid">
        <div class="header-logo-container">
            <a class="navbar-brand me-auto" href="#">
                <img src="<?= base_url('assets/img/logo/' . $profil['logo_perusahaan']); ?>"
                    alt="<?= $lang == 'id' ? $profil['alt_logo_perusahaan_id'] : $profil['alt_logo_perusahaan_en']; ?>"
                    class="header-logo-img">
            </a>
        </div>
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar">
            <div class="offcanvas-header">
                <div class="header-logo-container-offcanvas">
                    <a class="navbar-brand me-auto" href="#">
                        <img src="<?= base_url('assets/img/logo/' . $profil['logo_perusahaan']); ?>"
                            alt="<?= $lang == 'id' ? $profil['alt_logo_perusahaan_id'] : $profil['alt_logo_perusahaan_en']; ?>"
                            class="header-logo-img">
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav justify-content-center flex-grow-1 pe-3">

                    <!-- Home -->
                    <li class="nav-item">
                        <a class="nav-link mx-lg-2 <?= isset($data['activeMenu']) && $data['activeMenu'] === 'home' ? 'active' : '' ?>" href="<?= base_url($lang . '/') ?>">
                            <?= lang('bahasa.home'); ?>
                        </a>
                    </li>

                    <!-- About -->
                    <li class="nav-item">
                        <a class="nav-link mx-lg-2 <?= isset($data['activeMenu']) && $data['activeMenu'] === 'about' ? 'active' : '' ?>" href="<?= base_url($lang . '/' . $aboutLink) ?>">
                            <?= lang('bahasa.about'); ?>
                        </a>
                    </li>

                    <!-- Dropdown Produk -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle mx-lg-2 <?= isset($data['activeMenu']) && $data['activeMenu'] === 'product' ? 'active' : '' ?>" 
       href="#" id="productDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <?= lang('bahasa.product'); ?>
    </a>

    <ul class="dropdown-menu" aria-labelledby="productDropdown">
        <!-- Semua Produk -->
        <li>
            <a class="dropdown-item" href="<?= base_url($lang . '/' . $productLink) ?>">
                <?= $lang === 'id' ? 'Semua Produk' : 'All Products'; ?>
            </a>
        </li>

        <!-- Daftar Kategori -->
        <?php if (!empty($kategoriProdukLinks)): ?>
            <?php foreach ($kategoriProdukLinks as $cat): ?>
                <li>
                    <a class="dropdown-item" href="<?= $cat['url']; ?>">
                        <?= esc($cat['name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li><a class="dropdown-item">
                <?= $lang === 'id' ? 'Tidak ada kategori' : 'No Categories'; ?>
            </a></li>
        <?php endif; ?>
    </ul>
</li>



                    <!-- Aktivitas -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle mx-lg-2 <?= isset($data['activeMenu']) && $data['activeMenu'] === 'activity' ? 'active' : '' ?>" href="#" id="activityDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= lang('bahasa.activity'); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="activityDropdown">
                            <li><a class="dropdown-item" href="<?= base_url($lang . '/' . $activityLink) ?>">
                                    <?= $lang == 'id' ? 'Semua Aktivitas' : 'All Activities'; ?>
                                </a></li>
                            <?php if (!empty($kategoriAktivitasLinks)): ?>
                                <?php foreach ($kategoriAktivitasLinks as $link): ?>
                                    <li><a class="dropdown-item" href="<?= $link['url']; ?>"><?= $link['name']; ?></a></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><a class="dropdown-item"><?= $lang == 'id' ? 'Tidak ada kategori' : 'No Categories available'; ?></a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <!-- Artikel -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle mx-lg-2 <?= isset($data['activeMenu']) && $data['activeMenu'] === 'article' ? 'active' : '' ?>" href="#" id="articlesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= lang('bahasa.article'); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="articlesDropdown">
                            <li><a class="dropdown-item" href="<?= base_url($lang . '/' . $articleLink) ?>">
                                    <?= $lang == 'id' ? 'Semua Artikel' : 'All Articles'; ?>
                                </a></li>
                            <?php if (!empty($categoryLinks)): ?>
                                <?php foreach ($categoryLinks as $link): ?>
                                    <li><a class="dropdown-item" href="<?= $link['url']; ?>"><?= $link['name']; ?></a></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><a class="dropdown-item"><?= $lang == 'id' ? 'Tidak ada kategori' : 'No Categories available'; ?></a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <!-- Kontak -->
                    <li class="nav-item">
                        <a class="nav-link mx-lg-2 <?= isset($data['activeMenu']) && $data['activeMenu'] === 'contact' ? 'active' : '' ?>" href="<?= base_url($lang . '/' . $contactLink) ?>">
                            <?= lang('bahasa.contact'); ?>
                        </a>
                    </li>

                    <!-- Bahasa -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle mx-lg-2" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= ($lang === 'en') ? 'English' : 'Indonesia'; ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                            <li><a class="dropdown-item" href="<?= $indonesia_url; ?>"><img src="<?= base_url('assets/img/flags/id.png') ?>" style="width: 20px;"> Indonesia</a></li>
                            <li><a class="dropdown-item" href="<?= $english_url; ?>"><img src="<?= base_url('assets/img/flags/en.png') ?>" style="width: 20px;"> English</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <button class="navbar-toggler p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>
<!-- Navbar End -->