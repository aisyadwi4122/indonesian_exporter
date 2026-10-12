<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CategoryProductModel;
use App\Models\ProductModel;
use App\Models\MetaModel;
use App\Models\ProfilModel;
use App\Models\SosmedModel;
use App\Models\MarketplaceModel;
use App\Models\KontakModel;
use App\Models\CategoryActivityModel;
use App\Models\CategoryArtikelModel;

class ProductController extends BaseController
{
    public function index($slug = null)
    {
        $lang = session()->get('lang') ?? 'id';
        $productSegment = ($lang === 'id') ? 'produk' : 'product';

        // Model
        $kategoriProdukModel = new CategoryProductModel();
        $productModel = new ProductModel();
        $metaModel = new MetaModel();
        $profilModel = new ProfilModel();
        $kategoriAktivitasModel = new CategoryActivityModel();
        $kategoriArtikelModel = new CategoryArtikelModel();
        $sosmedModel = new SosmedModel();
        $marketplaceModel = new MarketplaceModel();
        $kontakModel = new KontakModel();

        // Data umum
        $dataProfil = $profilModel->where('role', 'user')->first();
        $dataMeta = $metaModel->where('id_meta', '3')->first();
        $sosmed = $sosmedModel->findAll();
        $marketplace = $marketplaceModel->findAll();
        $kontak = $kontakModel->first();
        $categoriesProduk = $kategoriProdukModel->findAll();
        $categoriesAktivitas = $kategoriAktivitasModel->findAll();
        $categories = $kategoriArtikelModel->findAll();
        $kategori_teratas = $kategoriArtikelModel->getKategoriTerbanyak();

        // CASE 1: Semua produk
        if (!$slug) {
            $products = $productModel->findAll();

            return view('product', [
                'lang' => $lang,
                'canonical' => base_url("$lang/$productSegment"),
                'meta' => $dataMeta,
                // 'metaCategory' => null,
                'product' => $products,
                'categoriesProduk' => $categoriesProduk,
                'productLink' => $productSegment,
                'activeMenu' => 'product',
                'profil' => $dataProfil,
                'sosmed' => $sosmed,
                'marketplace' => $marketplace,
                'kontak' => $kontak,
                'categoriesAktivitas' => $categoriesAktivitas,
                'kategori_teratas' => $kategori_teratas,
                'categories' => $categories,
                'category' => null,
            ]);
        }

        // CASE 2: Slug adalah kategori
        $category = $kategoriProdukModel->getCategoryBySlug($slug);
        if ($category) {
            $categoryId = $category['id_kategori_produk'];
            $products = $productModel->where('id_kategori_produk', $categoryId)->findAll();

            $metaCategory = [
                'title_id' => $category['title_kategori_id'] ?? '',
                'title_en' => $category['title_kategori_en'] ?? '',
                'meta_desc_id' => $category['meta_desc_id'] ?? '',
                'meta_desc_en' => $category['meta_desc_en'] ?? ''
            ];

            return view('product', [
                'lang' => $lang,
                'canonical' => base_url("$lang/$productSegment/$slug"),
                'meta' => $dataMeta,
                'metaCategory' => $metaCategory,
                'product' => $products,
                'categoriesProduk' => $categoriesProduk,
                'productLink' => $productSegment,
                'activeMenu' => 'product',
                'profil' => $dataProfil,
                'sosmed' => $sosmed,
                'marketplace' => $marketplace,
                'kontak' => $kontak,
                'categoriesAktivitas' => $categoriesAktivitas,
                'kategori_teratas' => $kategori_teratas,
                'categories' => $categories,
                'category' => $category,
            ]);
        }

        // CASE 3: Slug adalah produk
        return $this->detail($slug);
    }

    // DETAIL PRODUK
    public function detail($slug)
    {
        $lang = session()->get('lang') ?? 'id';
        $productModel = new ProductModel();
        $profilModel = new ProfilModel();
        $kategoriModel = new CategoryArtikelModel();
        $kategoriAktivitasModel = new CategoryActivityModel();
        $sosmedModel = new SosmedModel();
        $marketplaceModel = new MarketplaceModel();
        $kontakModel = new KontakModel();

        // Ambil produk berdasarkan slug
        $product = $productModel
            ->groupStart()
            ->where('slug_id', $slug)
            ->orWhere('slug_en', $slug)
            ->groupEnd()
            ->first();

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Ambil data profil dan pendukung
        $dataProfil = $profilModel->where('role', 'user')->first();
        $kategoriTeratas = $kategoriModel->getKategoriTerbanyak();
        $categories = $kategoriModel->findAll();
        $categoriesAktivitas = $kategoriAktivitasModel->findAll();
        $sosmed = $sosmedModel->findAll();
        $marketplace = $marketplaceModel->findAll();
        $kontak = $kontakModel->first();

        // Set metaCategory untuk template dari title_id/title_en + " Export Indonesia"
        $metaCategory = [
            'title_id'     => ($product['title_id'] ?? '') . ' Export Indonesia',
            'title_en'     => ($product['title_en'] ?? '') . ' Export Indonesia',
            'meta_desc_id' => !empty($product['deskripsi_produk_id']) ? strip_tags(substr($product['deskripsi_produk_id'], 0, 160)) : '',
            'meta_desc_en' => !empty($product['deskripsi_produk_en']) ? strip_tags(substr($product['deskripsi_produk_en'], 0, 160)) : '',
        ];

        // Canonical URL
        $slugCheck = ($lang === 'id') ? $product['slug_id'] : $product['slug_en'];
        $productSegment = ($lang === 'id') ? 'produk' : 'product';
        $canonical = base_url("$lang/$productSegment/$slugCheck");

        if (current_url() !== $canonical) {
            return redirect()->to($canonical);
        }

        // Ambil 8 produk lainnya (exclude produk saat ini)
        $produkLainnya = $productModel->getRandomProducts(8, $product['id_produk']);

        // Kirim data ke view
        $data = [
            'canonical' => $canonical,
            'product' => $product,
            'produkLainnya' => $produkLainnya,
            'productLink' => $productSegment,
            'lang' => $lang,
            'metaCategory' => $metaCategory,
            'activeMenu' => 'product',
            'profil' => $dataProfil,
            'kategori_teratas' => $kategoriTeratas,
            'sosmed' => $sosmed,
            'marketplace' => $marketplace,
            'kontak' => $kontak,
            'categories' => $categories,
            'categoriesAktivitas' => $categoriesAktivitas,
        ];

        // ❗ FIX PENTING — TANPA INI META SELALU SALAH
        $data['meta'] = $metaCategory;

        return view('detail_product', $data);

    }
}
