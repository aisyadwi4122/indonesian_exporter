<?php

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
// $routes->get('/about', 'AboutController::index');


// SALES AUTH ROUTES
$routes->get('sales', 'Auth::login');
$routes->post('sales', 'Auth::attempt');
$routes->get('sales/login', function () {
    return redirect()->to(base_url('sales'));
});
$routes->post('sales/login', 'Auth::attempt');
$routes->get('sales/logout', 'Auth::logout');

// SALES PROTECTED ROUTES
$routes->group('sales', ['filter' => 'salesauth'], function ($routes) {
    $routes->get('dashboard', 'Sales\Dashboard::index');
    $routes->get('produk-supplier', 'Sales\Produk::index');
    $routes->get('produk-supplier/pdf', 'Sales\Produk::exportPdf');
    $routes->get('produk-supplier/detail/(:any)/pdf', 'Sales\Produk::detailPdf/$1');
    $routes->get('produk-supplier/detail/(:any)', 'Sales\Produk::detail/$1');
    $routes->get('produk-supplier/(:any)/pdf', 'Sales\Produk::detailPdf/$1');
    $routes->get('produk-supplier/(:any)', 'Sales\Produk::detail/$1');
    $routes->post('activity/commission-click', 'Sales\Produk::recordCommissionClick');
});

// Redirect 301 Permanen dari URL lama /sales/produk/* ke /sales/produk-supplier/*
$routes->get('sales/produk', function () {
    $q = $_SERVER['QUERY_STRING'] ?? '';
    return redirect()->to(base_url('sales/produk-supplier' . ($q !== '' ? '?' . $q : '')), 301);
});
$routes->get('sales/produk/pdf', function () {
    $q = $_SERVER['QUERY_STRING'] ?? '';
    return redirect()->to(base_url('sales/produk-supplier/pdf' . ($q !== '' ? '?' . $q : '')), 301);
});
$routes->get('sales/produk/detail/(:any)/pdf', function ($id) {
    $q = $_SERVER['QUERY_STRING'] ?? '';
    return redirect()->to(base_url('sales/produk-supplier/detail/' . $id . '/pdf' . ($q !== '' ? '?' . $q : '')), 301);
});
$routes->get('sales/produk/detail/(:any)', function ($id) {
    $q = $_SERVER['QUERY_STRING'] ?? '';
    return redirect()->to(base_url('sales/produk-supplier/detail/' . $id . ($q !== '' ? '?' . $q : '')), 301);
});
$routes->get('sales/produk/(:any)/pdf', function ($id) {
    $q = $_SERVER['QUERY_STRING'] ?? '';
    return redirect()->to(base_url('sales/produk-supplier/' . $id . '/pdf' . ($q !== '' ? '?' . $q : '')), 301);
});
$routes->get('sales/produk/(:any)', function ($id) {
    $q = $_SERVER['QUERY_STRING'] ?? '';
    return redirect()->to(base_url('sales/produk-supplier/' . $id . ($q !== '' ? '?' . $q : '')), 301);
});

$routes->group('sales', ['filter' => 'role:sales'], function ($r) {
    $r->get('products', 'Sales\ProductController::index');
    $r->get('products/detail/(:num)', 'Sales\ProductController::detail/$1');
    $r->get('products/pdf/(:num)', 'Sales\ProductController::downloadPdf/$1');
});

// SUPPLIER ROUTES
$routes->get('supplier', 'Supplier\Auth::login');
$routes->get('supplier/login', 'Supplier\Auth::login');
$routes->post('supplier/login', 'Supplier\Auth::attempt');
$routes->get('supplier/logout', 'Supplier\Auth::logout');
$routes->group('supplier', ['filter' => 'supplierauth'], function ($routes) {
    $routes->get('dashboard', 'Supplier\Dashboard::index');
    $routes->get('perusahaan', 'Supplier\Perusahaan::index');
    $routes->post('perusahaan/simpan', 'Supplier\Perusahaan::simpan');
    $routes->post('perusahaan/hapus', 'Supplier\Perusahaan::hapus');
    $routes->get('produk', 'Supplier\Produk::index');
    $routes->get('produk/index', 'Supplier\Produk::index');
    $routes->get('produk/tambah', 'Supplier\Produk::tambah');
    $routes->post('produk/proses_tambah', 'Supplier\Produk::proses_tambah');
    $routes->get('produk/edit/(:num)', 'Supplier\Produk::edit/$1');
    $routes->post('produk/proses_edit/(:num)', 'Supplier\Produk::proses_edit/$1');
    $routes->post('produk/delete/(:num)', 'Supplier\Produk::delete/$1');
    $routes->get('history', 'Supplier\History::index');
});

// ADMIN ROUTES
$routes->get('login', 'LoginController::index');
$routes->post('login/process', 'LoginController::process');
$routes->get('logout', 'LoginController::logout');

$routes->get('admin/dashboard', 'admin\DashboardController::index');

// ADMIN PROFILE
$routes->get('admin/profil/edit', 'admin\Profil::edit');
$routes->post('admin/profil/proses_edit', 'admin\Profil::proses_edit');

// ADMIN CATEGORY PRODUCT
$routes->get('admin/kategori_produk/index', 'admin\KategoriProduk::index');
$routes->get('admin/kategori_produk/tambah', 'admin\KategoriProduk::tambah');
$routes->post('admin/kategori_produk/proses_tambah', 'admin\KategoriProduk::proses_tambah');
$routes->get('admin/kategori_produk/edit/(:num)', 'admin\KategoriProduk::edit/$1');
$routes->post('admin/kategori_produk/proses_edit/(:num)', 'admin\KategoriProduk::proses_edit/$1');
$routes->get('admin/kategori_produk/delete/(:any)', 'admin\KategoriProduk::delete/$1');

// ADMIN PRODUCTS
$routes->get('admin/produk/index', 'admin\Produk::index');
$routes->get('admin/produk/tambah', 'admin\Produk::tambah');
$routes->post('admin/produk/proses_tambah', 'admin\Produk::proses_tambah');
$routes->get('admin/produk/edit/(:num)', 'admin\Produk::edit/$1');
$routes->post('admin/produk/proses_edit/(:num)', 'admin\Produk::proses_edit/$1');
$routes->get('admin/produk/delete/(:any)', 'admin\Produk::delete/$1');

// ADMIN SLIDER
$routes->get('admin/slider/index', 'admin\Slider::index');
$routes->get('admin/slider/tambah', 'admin\Slider::tambah');
$routes->post('admin/slider/proses_tambah', 'admin\Slider::proses_tambah');
$routes->get('admin/slider/edit/(:num)', 'admin\Slider::edit/$1');
$routes->post('admin/slider/proses_edit/(:num)', 'admin\Slider::proses_edit/$1');
$routes->get('admin/slider/delete/(:any)', 'admin\Slider::delete/$1');

// ADMIN CATEGORY ACTIVITIES
$routes->get('admin/kategoriAktivitas/index', 'admin\KategoriAktivitas::index');
$routes->get('admin/kategoriAktivitas/tambah', 'admin\KategoriAktivitas::tambah');
$routes->post('admin/kategoriAktivitas/proses_tambah', 'admin\KategoriAktivitas::proses_tambah');
$routes->get('admin/kategoriAktivitas/edit/(:num)', 'admin\KategoriAktivitas::edit/$1');
$routes->post('admin/kategoriAktivitas/proses_edit/(:num)', 'admin\KategoriAktivitas::proses_edit/$1');
$routes->get('admin/kategoriAktivitas/delete/(:any)', 'admin\KategoriAktivitas::delete/$1');

// ADMIN ACTIVITIES
$routes->get('admin/aktivitas/index', 'admin\Aktivitas::index');
$routes->get('admin/aktivitas/tambah', 'admin\Aktivitas::tambah');
$routes->post('admin/aktivitas/proses_tambah', 'admin\Aktivitas::proses_tambah');
$routes->get('admin/aktivitas/edit/(:num)', 'admin\Aktivitas::edit/$1');
$routes->post('admin/aktivitas/proses_edit/(:num)', 'admin\Aktivitas::proses_edit/$1');
$routes->get('admin/aktivitas/delete/(:any)', 'admin\Aktivitas::delete/$1');

// ADMIN CATEGORY ARTICLES
$routes->get('admin/kategoriArtikel/index', 'admin\KategoriArtikel::index');
$routes->get('admin/kategoriArtikel/tambah', 'admin\KategoriArtikel::tambah');
$routes->post('admin/kategoriArtikel/proses_tambah', 'admin\KategoriArtikel::proses_tambah');
$routes->get('admin/kategoriArtikel/edit/(:num)', 'admin\KategoriArtikel::edit/$1');
$routes->post('admin/kategoriArtikel/proses_edit/(:num)', 'admin\KategoriArtikel::proses_edit/$1');
$routes->get('admin/kategoriArtikel/delete/(:any)', 'admin\KategoriArtikel::delete/$1');

// ADMIN ARTICLES CATEGORIES
$routes->get('admin/kategoriartikel', 'admin\ArticleCategoryController::index');
$routes->get('admin/kategoriartikel/create', 'admin\ArticleCategoryController::create');
$routes->post('admin/kategoriartikel/store', 'admin\ArticleCategoryController::store');
$routes->get('admin/kategoriartikel/edit/(:num)', 'admin\ArticleCategoryController::edit/$1');
$routes->post('admin/kategoriartikel/update/(:num)', 'admin\ArticleCategoryController::update/$1');
$routes->get('admin/kategoriartikel/delete/(:any)', 'admin\ArticleCategoryController::delete/$1');

// ADMIN ARTICLES
$routes->get('admin/artikel/index', 'admin\ArtikelController::index');
$routes->get('admin/artikel/tambah', 'admin\ArtikelController::tambah');
$routes->post('admin/artikel/proses_tambah', 'admin\ArtikelController::proses_tambah');
$routes->get('admin/artikel/edit/(:num)', 'admin\ArtikelController::edit/$1');
$routes->post('admin/artikel/proses_edit/(:num)', 'admin\ArtikelController::proses_edit/$1');
$routes->get('admin/artikel/delete/(:any)', 'admin\ArtikelController::delete/$1');

// ADMIN ARTICLES CATEGORIES
$routes->get('admin/kategoriartikel', 'admin\ArticleCategoryController::index');
$routes->get('admin/kategoriartikel/create', 'admin\ArticleCategoryController::create');
$routes->post('admin/kategoriartikel/store', 'admin\ArticleCategoryController::store');
$routes->get('admin/kategoriartikel/edit/(:num)', 'admin\ArticleCategoryController::edit/$1');
$routes->post('admin/kategoriartikel/update/(:num)', 'admin\ArticleCategoryController::update/$1');
$routes->get('admin/kategoriartikel/delete/(:any)', 'admin\ArticleCategoryController::delete/$1');

// ADMIN MARKETPLACE
$routes->get('admin/marketplace', 'admin\MarketplaceController::index');
$routes->get('admin/marketplace/create', 'admin\MarketplaceController::create');
$routes->post('admin/marketplace/store', 'admin\MarketplaceController::store');
$routes->get('admin/marketplace/edit/(:num)', 'admin\MarketplaceController::edit/$1');
$routes->post('admin/marketplace/update/(:num)', 'admin\MarketplaceController::update/$1');
$routes->get('admin/marketplace/delete/(:any)', 'admin\MarketplaceController::delete/$1');

// ADMIN KONTAK
$routes->get('admin/kontak', 'admin\KontakController::edit');
$routes->post('admin/kontak/update/(:num)', 'admin\KontakController::update/$1');

// ADMIN SOSMED
$routes->get('admin/sosmed', 'admin\SosmedController::index');
$routes->get('admin/sosmed/create', 'admin\SosmedController::create');
$routes->post('admin/sosmed/store', 'admin\SosmedController::store');
$routes->get('admin/sosmed/edit/(:num)', 'admin\SosmedController::edit/$1');
$routes->post('admin/sosmed/update/(:num)', 'admin\SosmedController::update/$1');
$routes->get('admin/sosmed/delete/(:any)', 'admin\SosmedController::delete/$1');

// ADMIN META
$routes->get('admin/meta/index', 'admin\MetaController::index');
$routes->get('admin/meta/tambah', 'admin\MetaController::tambah');
$routes->post('admin/meta/proses_tambah', 'admin\MetaController::proses_tambah');
$routes->get('admin/meta/edit/(:num)', 'admin\MetaController::edit/$1');
$routes->post('admin/meta/proses_edit/(:num)', 'admin\MetaController::proses_edit/$1');
$routes->get('admin/meta/delete/(:any)', 'admin\MetaController::delete/$1');

// ADMIN PRODUK SALES
$routes->get('admin/produk_sales', 'admin\ProdukSales::index');
$routes->get('admin/produk_sales/index', 'admin\ProdukSales::index');
$routes->get('admin/produk_sales/tambah', 'admin\ProdukSales::tambah');
$routes->post('admin/produk_sales/proses_tambah', 'admin\ProdukSales::proses_tambah');
$routes->get('admin/produk_sales/edit/(:num)', 'admin\ProdukSales::edit/$1');
$routes->post('admin/produk_sales/proses_edit/(:num)', 'admin\ProdukSales::proses_edit/$1');
$routes->post('admin/produk_sales/delete/(:num)', 'admin\ProdukSales::delete/$1');

// ADMIN MANAJEMEN AKUN (SALES & SUPPLIER)
$routes->get('admin/akun', 'admin\Akun::index');
$routes->get('admin/akun/index', 'admin\Akun::index');
$routes->get('admin/akun/tambah', 'admin\Akun::tambah');
$routes->post('admin/akun/proses_tambah', 'admin\Akun::proses_tambah');
$routes->get('admin/akun/edit/(:segment)/(:num)', 'admin\Akun::edit/$1/$2');
$routes->post('admin/akun/proses_edit/(:segment)/(:num)', 'admin\Akun::proses_edit/$1/$2');
$routes->post('admin/akun/toggle/(:segment)/(:num)', 'admin\Akun::toggle/$1/$2');
$routes->post('admin/akun/delete/(:segment)/(:num)', 'admin\Akun::delete/$1/$2');

// REDIRECT ROUTE LAMA AKUN SALES KE MODUL BARU
$routes->get('admin/akun_sales', function () {
    return redirect()->to(base_url('admin/akun/index?tipe=sales'));
});
$routes->get('admin/akun_sales/index', function () {
    return redirect()->to(base_url('admin/akun/index?tipe=sales'));
});
$routes->get('admin/akun_sales/tambah', function () {
    return redirect()->to(base_url('admin/akun/tambah?tipe=sales'));
});
$routes->get('admin/akun_sales/edit/(:num)', function ($id) {
    return redirect()->to(base_url('admin/akun/edit/sales/' . $id));
});

// ADMIN LAPORAN AKTIVITAS
$routes->get('admin/laporan-aktivitas/pencarian', 'admin\LaporanAktivitas::pencarian');
$routes->get('admin/laporan-aktivitas/pencarian/export', 'admin\LaporanAktivitas::exportPencarian');
$routes->get('admin/laporan-aktivitas/login-sales', 'admin\LaporanAktivitas::loginSales');
$routes->get('admin/laporan-aktivitas/login-sales/export', 'admin\LaporanAktivitas::exportLoginSales');
$routes->get('admin/laporan-aktivitas/detail', 'admin\LaporanAktivitas::detail');
$routes->get('admin/laporan-aktivitas/detail/export', 'admin\LaporanAktivitas::exportDetail');
$routes->get('admin/laporan-aktivitas/info-komisi', 'admin\LaporanAktivitas::infoKomisi');
$routes->get('admin/laporan-aktivitas/info-komisi/export', 'admin\LaporanAktivitas::exportInfoKomisi');
$routes->get('admin/laporan-aktivitas/pdf', 'admin\LaporanAktivitas::pdf');
$routes->get('admin/laporan-aktivitas/pdf/export', 'admin\LaporanAktivitas::exportPdf');
$routes->get('admin/laporan-aktivitas/produk-terlaris', 'admin\LaporanAktivitas::produkTerlaris');
$routes->get('admin/laporan-aktivitas/produk-terlaris/export', 'admin\LaporanAktivitas::exportProdukTerlaris');
$routes->get('admin/laporan-aktivitas/sales-teraktif', 'admin\LaporanAktivitas::salesTeraktif');
$routes->get('admin/laporan-aktivitas/sales-teraktif/export', 'admin\LaporanAktivitas::exportSalesTeraktif');
$routes->get('admin/laporan-aktivitas/aktivitas-sales', 'admin\LaporanAktivitas::aktivitasSales');
$routes->get('admin/laporan-aktivitas/aktivitas-sales/export', 'admin\LaporanAktivitas::exportAktivitasSales');


$routes->get('/', function () {
    return redirect()->to('/en/'); // Default redirect ke /en/home
});

// $routes->set404Override('App\Controllers\Home::notFound');


$routes->group('id', function ($routes) {
    $routes->get('/', 'Home::index');
    $routes->get('kontak', 'ContactController::index');
    $routes->get('tentang', 'AboutController::index');
    $routes->get('artikel', 'ArticleeController::index');
    $routes->get('artikel/(:segment)', 'ArticleeController::index/$1');
    $routes->get('artikel/(:segment)/(:segment)', 'ArticleeController::detail/$1/$2');

    $routes->get('aktivitas', 'ActivityController::index');
    $routes->get('aktivitas/(:segment)', 'ActivityController::index/$1');
    $routes->get('aktivitas/(:segment)/(:segment)', 'ActivityController::detail/$1/$2');

    // 🔹 Produk
    $routes->get('produk', 'ProductController::index'); // Semua produk
    $routes->get('produk/(:segment)', 'ProductController::index/$1'); // Kategori produk

    // 🔹 Content pages
    $routes->get('(:segment)', 'ContentController::index');
    $routes->get('(:segment)/(:segment)', 'ContentController::category');
    $routes->get('(:segment)/(:segment)/(:segment)', 'ContentController::detail');
});

$routes->group('en', function ($routes) {
    $routes->get('/', 'Home::index');
    $routes->get('contact', 'ContactController::index');
    $routes->get('about', 'AboutController::index');
    $routes->get('article', 'ArticleeController::index');
    $routes->get('article/(:segment)', 'ArticleeController::index/$1');
    $routes->get('article/(:segment)/(:segment)', 'ArticleeController::detail/$1/$2');

    $routes->get('activity', 'ActivityController::index');
    $routes->get('activity/(:segment)', 'ActivityController::index/$1');
    $routes->get('activity/(:segment)/(:segment)', 'ActivityController::detail/$1/$2');

    // 🔹 Product
    $routes->get('product', 'ProductController::index'); // All products
    $routes->get('product/(:segment)', 'ProductController::index/$1'); // Product category

    // 🔹 Content pages
    $routes->get('(:segment)', 'ContentController::index');
    $routes->get('(:segment)/(:segment)', 'ContentController::category');
    $routes->get('(:segment)/(:segment)/(:segment)', 'ContentController::detail');
});
