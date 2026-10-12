<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\CategoryProductModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Produk extends BaseController
{
    protected $productModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryProductModel();
        helper(['url', 'form']);
    }

    /**
     * Halaman daftar produk
     * Route: GET /sales/produk
     */
    public function index()
    {
        $keyword  = trim($this->request->getGet('keyword') ?? '');
        $kategori = trim($this->request->getGet('kategori') ?? '');

        $builder = $this->productModel
            ->select('tb_produk.*, tb_kategori_produk.nama_kategori_id, tb_kategori_produk.nama_kategori_en')
            ->join('tb_kategori_produk', 'tb_kategori_produk.id_kategori_produk = tb_produk.id_kategori_produk', 'left');

        // Filter pencarian berdasarkan nama produk (ID atau EN)
        if ($keyword !== '') {
            $builder->groupStart()
                ->like('tb_produk.nama_produk_id', $keyword)
                ->orLike('tb_produk.nama_produk_en', $keyword)
                ->groupEnd();
        }

        // Filter kategori produk
        if ($kategori !== '') {
            $builder->where('tb_produk.id_kategori_produk', $kategori);
        }

        // Pagination 10 per halaman
        $products = $builder->orderBy('tb_produk.id_produk', 'DESC')->paginate(10, 'produk');
        $pager    = $this->productModel->pager;

        // Ambil semua kategori untuk opsi filter
        $categories = $this->categoryModel->findAll();

        $page = (int) ($this->request->getGet('page_produk') ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $data = [
            'title'        => 'Kelola Produk - Sales Indonesian Exporter',
            'page_title'   => 'Daftar Produk',
            'activeMenu'   => 'produk',
            'products'     => $products,
            'pager'        => $pager,
            'categories'   => $categories,
            'keyword'      => $keyword,
            'kategori'     => $kategori,
            'currentPage'  => $page,
            'perPage'      => 10,
        ];

        return view('sales/produk/index', $data);
    }

    /**
     * Form Tambah Produk
     * Route: GET /sales/produk/tambah
     */
    public function tambah()
    {
        $categories = $this->categoryModel->findAll();

        $data = [
            'title'       => 'Tambah Produk Baru - Sales',
            'page_title'  => 'Tambah Produk',
            'activeMenu'  => 'produk',
            'action'      => base_url('sales/produk/simpan'),
            'categories'  => $categories,
            'product'     => null,
            'validation'  => \Config\Services::validation(),
        ];

        return view('sales/produk/form', $data);
    }

    /**
     * Simpan Produk Baru (POST)
     * Route: POST /sales/produk/simpan
     */
    public function simpan()
    {
        // Aturan validasi input
        $rules = [
            'nama_produk_id' => [
                'rules'  => 'required|max_length[200]',
                'errors' => [
                    'required'   => 'Nama produk (Indonesia) wajib diisi.',
                    'max_length' => 'Nama produk (Indonesia) maksimal 200 karakter.',
                ]
            ],
            'nama_produk_en' => [
                'rules'  => 'required|max_length[200]',
                'errors' => [
                    'required'   => 'Nama produk (Inggris) wajib diisi.',
                    'max_length' => 'Nama produk (Inggris) maksimal 200 karakter.',
                ]
            ],
            'id_kategori_produk' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Kategori produk wajib dipilih.',
                ]
            ],
            'deskripsi_produk_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Deskripsi produk (Indonesia) wajib diisi.',
                ]
            ],
            'deskripsi_produk_en' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Deskripsi produk (Inggris) wajib diisi.',
                ]
            ],
            'foto_produk' => [
                'rules'  => 'uploaded[foto_produk]|is_image[foto_produk]|mime_in[foto_produk,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto_produk,2048]',
                'errors' => [
                    'uploaded' => 'Foto produk wajib diunggah.',
                    'is_image' => 'File harus berupa gambar.',
                    'mime_in'  => 'Format gambar harus berupa JPG, JPEG, PNG, atau WEBP.',
                    'max_size' => 'Ukuran gambar maksimal 2MB.',
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Upload gambar dengan random name
        $fileFoto = $this->request->getFile('foto_produk');
        $uploadDir = FCPATH . 'assets/img/produk/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = $fileFoto->getRandomName();
        $fileFoto->move($uploadDir, $newFileName);

        // Siapkan field teks dan otomatisasi slug / SEO
        $namaId = trim($this->request->getPost('nama_produk_id'));
        $namaEn = trim($this->request->getPost('nama_produk_en'));

        $slugId = trim($this->request->getPost('slug_id') ?? '');
        if ($slugId === '') {
            $slugId = $this->generateSlug($namaId);
        }

        $slugEn = trim($this->request->getPost('slug_en') ?? '');
        if ($slugEn === '') {
            $slugEn = $this->generateSlug($namaEn);
        }

        $altId = trim($this->request->getPost('alt_produk_id') ?? '');
        if ($altId === '') {
            $altId = $namaId;
        }

        $altEn = trim($this->request->getPost('alt_produk_en') ?? '');
        if ($altEn === '') {
            $altEn = $namaEn;
        }

        $titleId = trim($this->request->getPost('title_id') ?? '');
        if ($titleId === '') {
            $titleId = mb_substr($namaId, 0, 59);
        } else {
            $titleId = mb_substr($titleId, 0, 59);
        }

        $titleEn = trim($this->request->getPost('title_en') ?? '');
        if ($titleEn === '') {
            $titleEn = mb_substr($namaEn, 0, 59);
        } else {
            $titleEn = mb_substr($titleEn, 0, 59);
        }

        $metaDescId = trim($this->request->getPost('meta_desc_id') ?? '');
        if ($metaDescId === '') {
            $metaDescId = mb_substr(strip_tags($this->request->getPost('deskripsi_produk_id')), 0, 159);
        } else {
            $metaDescId = mb_substr($metaDescId, 0, 159);
        }

        $metaDescEn = trim($this->request->getPost('meta_desc_en') ?? '');
        if ($metaDescEn === '') {
            $metaDescEn = mb_substr(strip_tags($this->request->getPost('deskripsi_produk_en')), 0, 159);
        } else {
            $metaDescEn = mb_substr($metaDescEn, 0, 159);
        }

        $insertData = [
            'id_kategori_produk'  => $this->request->getPost('id_kategori_produk'),
            'nama_produk_id'      => $namaId,
            'nama_produk_en'      => $namaEn,
            'deskripsi_produk_id' => $this->request->getPost('deskripsi_produk_id'),
            'deskripsi_produk_en' => $this->request->getPost('deskripsi_produk_en'),
            'foto_produk'         => $newFileName,
            'alt_produk_id'       => $altId,
            'alt_produk_en'       => $altEn,
            'title_id'            => $titleId,
            'title_en'            => $titleEn,
            'meta_desc_id'        => $metaDescId,
            'meta_desc_en'        => $metaDescEn,
            'slug_id'             => $slugId,
            'slug_en'             => $slugEn,
        ];

        $this->productModel->insert($insertData);

        session()->setFlashdata('success', 'Produk berhasil ditambahkan.');
        return redirect()->to(base_url('sales/produk'));
    }

    /**
     * Form Edit Produk
     * Route: GET /sales/produk/edit/(:num)
     */
    public function edit($id = null)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            throw PageNotFoundException::forPageNotFound('Data produk tidak ditemukan.');
        }

        $categories = $this->categoryModel->findAll();

        $data = [
            'title'       => 'Edit Produk - Sales',
            'page_title'  => 'Edit Produk',
            'activeMenu'  => 'produk',
            'action'      => base_url('sales/produk/update/' . $id),
            'categories'  => $categories,
            'product'     => $product,
            'validation'  => \Config\Services::validation(),
        ];

        return view('sales/produk/form', $data);
    }

    /**
     * Proses Update Produk (POST)
     * Route: POST /sales/produk/update/(:num)
     */
    public function update($id = null)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            session()->setFlashdata('error', 'Produk tidak ditemukan.');
            return redirect()->to(base_url('sales/produk'));
        }

        $rules = [
            'nama_produk_id' => [
                'rules'  => 'required|max_length[200]',
                'errors' => [
                    'required'   => 'Nama produk (Indonesia) wajib diisi.',
                    'max_length' => 'Nama produk (Indonesia) maksimal 200 karakter.',
                ]
            ],
            'nama_produk_en' => [
                'rules'  => 'required|max_length[200]',
                'errors' => [
                    'required'   => 'Nama produk (Inggris) wajib diisi.',
                    'max_length' => 'Nama produk (Inggris) maksimal 200 karakter.',
                ]
            ],
            'id_kategori_produk' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Kategori produk wajib dipilih.',
                ]
            ],
            'deskripsi_produk_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Deskripsi produk (Indonesia) wajib diisi.',
                ]
            ],
            'deskripsi_produk_en' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Deskripsi produk (Inggris) wajib diisi.',
                ]
            ],
        ];

        // Validasi foto hanya jika file baru diunggah
        $fileFoto = $this->request->getFile('foto_produk');
        if ($fileFoto && $fileFoto->isValid()) {
            $rules['foto_produk'] = [
                'rules'  => 'is_image[foto_produk]|mime_in[foto_produk,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto_produk,2048]',
                'errors' => [
                    'is_image' => 'File harus berupa gambar.',
                    'mime_in'  => 'Format gambar harus berupa JPG, JPEG, PNG, atau WEBP.',
                    'max_size' => 'Ukuran gambar maksimal 2MB.',
                ]
            ];
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $fileName = $product['foto_produk'];
        $uploadDir = FCPATH . 'assets/img/produk/';

        // Jika ada unggahan gambar baru
        if ($fileFoto && $fileFoto->isValid() && !$fileFoto->hasMoved()) {
            // Hapus file lama jika ada
            $oldFilePath = $uploadDir . $product['foto_produk'];
            if (!empty($product['foto_produk']) && file_exists($oldFilePath) && is_file($oldFilePath)) {
                unlink($oldFilePath);
            }

            // Simpan gambar baru dengan random name
            $fileName = $fileFoto->getRandomName();
            $fileFoto->move($uploadDir, $fileName);
        }

        $namaId = trim($this->request->getPost('nama_produk_id'));
        $namaEn = trim($this->request->getPost('nama_produk_en'));

        $slugId = trim($this->request->getPost('slug_id') ?? '');
        if ($slugId === '') {
            $slugId = $this->generateSlug($namaId);
        }

        $slugEn = trim($this->request->getPost('slug_en') ?? '');
        if ($slugEn === '') {
            $slugEn = $this->generateSlug($namaEn);
        }

        $altId = trim($this->request->getPost('alt_produk_id') ?? '');
        if ($altId === '') {
            $altId = $namaId;
        }

        $altEn = trim($this->request->getPost('alt_produk_en') ?? '');
        if ($altEn === '') {
            $altEn = $namaEn;
        }

        $titleId = trim($this->request->getPost('title_id') ?? '');
        if ($titleId === '') {
            $titleId = mb_substr($namaId, 0, 59);
        } else {
            $titleId = mb_substr($titleId, 0, 59);
        }

        $titleEn = trim($this->request->getPost('title_en') ?? '');
        if ($titleEn === '') {
            $titleEn = mb_substr($namaEn, 0, 59);
        } else {
            $titleEn = mb_substr($titleEn, 0, 59);
        }

        $metaDescId = trim($this->request->getPost('meta_desc_id') ?? '');
        if ($metaDescId === '') {
            $metaDescId = mb_substr(strip_tags($this->request->getPost('deskripsi_produk_id')), 0, 159);
        } else {
            $metaDescId = mb_substr($metaDescId, 0, 159);
        }

        $metaDescEn = trim($this->request->getPost('meta_desc_en') ?? '');
        if ($metaDescEn === '') {
            $metaDescEn = mb_substr(strip_tags($this->request->getPost('deskripsi_produk_en')), 0, 159);
        } else {
            $metaDescEn = mb_substr($metaDescEn, 0, 159);
        }

        $updateData = [
            'id_kategori_produk'  => $this->request->getPost('id_kategori_produk'),
            'nama_produk_id'      => $namaId,
            'nama_produk_en'      => $namaEn,
            'deskripsi_produk_id' => $this->request->getPost('deskripsi_produk_id'),
            'deskripsi_produk_en' => $this->request->getPost('deskripsi_produk_en'),
            'foto_produk'         => $fileName,
            'alt_produk_id'       => $altId,
            'alt_produk_en'       => $altEn,
            'title_id'            => $titleId,
            'title_en'            => $titleEn,
            'meta_desc_id'        => $metaDescId,
            'meta_desc_en'        => $metaDescEn,
            'slug_id'             => $slugId,
            'slug_en'             => $slugEn,
        ];

        $this->productModel->update($id, $updateData);

        session()->setFlashdata('success', 'Produk berhasil diperbarui.');
        return redirect()->to(base_url('sales/produk'));
    }

    /**
     * Hapus Produk (POST)
     * Route: POST /sales/produk/hapus/(:num)
     */
    public function hapus($id = null)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            session()->setFlashdata('error', 'Produk tidak ditemukan.');
            return redirect()->to(base_url('sales/produk'));
        }

        // Hapus file fisik gambar jika ada
        $uploadDir = FCPATH . 'assets/img/produk/';
        $filePath  = $uploadDir . $product['foto_produk'];
        if (!empty($product['foto_produk']) && file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
        }

        // Hapus data dari database
        $this->productModel->delete($id);

        session()->setFlashdata('success', 'Produk dan gambar berhasil dihapus.');
        return redirect()->to(base_url('sales/produk'));
    }

    /**
     * Helper membuat URL slug
     */
    protected function generateSlug(string $text): string
    {
        // Ubah ke huruf kecil
        $slug = mb_strtolower($text, 'UTF-8');
        // Hapus karakter non-alfanumerik kecuali spasi
        $slug = preg_replace('/[^a-z0-9\s-]/u', '', $slug);
        // Ganti spasi ganda dan spasi dengan strip
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }
}
