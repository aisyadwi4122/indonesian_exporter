# Laporan Audit Tabel Database: `k4991560_indonesian-exporter`

**Tanggal Audit:** 07 November 2026  
**Aplikasi:** Indonesian Exporter (CodeIgniter 4)  
**Total Entitas Database:** 27 Tabel (0 View)  
**Metode Audit:** Pemeriksaan skema dan perhitungan aktual `COUNT(*)`, penelusuran referensi model (`$table`), query builder (`$db->table()`), query mentah (`FROM`, `JOIN`, `INTO`, `UPDATE`), Controller, Library, Migrations, Seeds, Config, dan View di seluruh direktori proyek.

---

## 1. Tabel Ringkasan Pemetaan 27 Tabel Database

| No | Nama Tabel | Jumlah Baris | Klasifikasi | Dipakai Oleh (Model, Controller, View) | Halaman / Fitur Terkait | Catatan & Analisis Risiko |
| :---: | :--- | :---: | :--- | :--- | :--- | :--- |
| 1 | `commission_click_history` | 17 | **DIPAKAI LANGSUNG** | `CommissionClickHistoryModel`, `SalesActivityModel`, `DashboardController`, `LaporanAktivitas` | Admin: Dashboard & Laporan Klik Komisi; Sales: Klik Info Komisi | **Kritis / Jangan Hapus.** Menyimpan data pelacakan klik komisi sales. |
| 2 | `detail_click_history` | 32 | **DIPAKAI LANGSUNG** | `DetailClickHistoryModel`, `SalesActivityModel`, `DashboardController`, `LaporanAktivitas`, `Sales/Produk` | Admin: Dashboard & Laporan Klik Detail; Sales: Pembukaan detail produk | **Kritis / Jangan Hapus.** Menyimpan log interaksi produk sales. |
| 3 | `login_history` | 16 | **DIPAKAI LANGSUNG** | `LoginHistoryModel`, `SalesActivityModel`, `DashboardController`, `LaporanAktivitas`, `Auth`, `Loginctrl` | Admin: Dashboard & Laporan Login Sales; Auth: Pencatatan sesi login | **Kritis / Jangan Hapus.** Menyimpan audit log login sales & admin. |
| 4 | `migrations` | 16 | **SISTEM / JANGAN HAPUS** | Engine CI4 Migrations (`Config/Migrations.php`, `spark migrate`) | Sistem internal Framework CI4 | **Kritis / Sistem.** Menyimpan rekam jejak versi batch migration database. |
| 5 | `pdf_history` | 14 | **DIPAKAI LANGSUNG** | `PdfHistoryModel`, `SalesActivityModel`, `DashboardController`, `LaporanAktivitas`, `Sales/Produk` | Admin: Dashboard & Laporan Unduh PDF; Sales: Unduh PDF detail & katalog | **Kritis / Jangan Hapus.** Menyimpan rekam jejak cetak PDF oleh sales. |
| 6 | `products` | 5 | **TIDAK DITEMUKAN DI KODE** | Hanya ada di file migration (`CreateProductsTable`) dan seeder (`ProductSeeder`) | Tidak ada rute atau controller aktif yang mengakses tabel ini | **Kandidat Hapus (Risiko Rendah).** Sisa prototipe awal. Kode aktif memakai `tb_produk`. |
| 7 | `search_history` | 12 | **DIPAKAI LANGSUNG** | `SearchHistoryModel`, `SalesActivityModel`, `DashboardController`, `LaporanAktivitas`, `Sales/Produk` | Admin: Dashboard & Laporan Pencarian; Sales: Pencarian produk supplier | **Kritis / Jangan Hapus.** Menyimpan kata kunci pencarian sales. |
| 8 | `tb_aktivitas` | 4 | **DIPAKAI LANGSUNG** | `ActivityModel`, `CategoryActivityModel`, `ActivityController`, `admin/ActivityController` | Publik: `/activity`; Admin: Manajemen Aktivitas | **Aktif / Jangan Hapus.** Menyimpan artikel kegiatan & pameran ekspor perusahaan. |
| 9 | `tb_artikel` | 5 | **DIPAKAI LANGSUNG** | `ArtikelModel`, `ArticleeController`, `admin/ArticleeController`, `Home` | Publik: `/article`, `/article/detail/...`; Admin: Manajemen Artikel | **Aktif / Jangan Hapus.** Menyimpan artikel edukasi & tips ekspor. |
| 10 | `tb_ecp_ekspor` | 117 | **DIPAKAI LANGSUNG** | `EcpEksporModel`, `ProdukSalesModel`, `Sales/Produk`, `admin/ProdukSales` | Sales: `/sales/produk-supplier`; Admin: `/admin/produk_sales` | **Kritis / Jangan Hapus.** Menyimpan 117 data produk katalog binaan ECP. |
| 11 | `tb_history_export_supplier` | 117 | **DIPAKAI LANGSUNG** | `SupplierExportHistoryModel`, `Sales/Produk`, `Supplier/History`, `Supplier/Dashboard` | Sales: Tab 3 Riwayat Ekspor di detail produk; Supplier: `/supplier/history` | **Kritis / Jangan Hapus.** FK ke `tb_supplier`. Menyimpan transaksi ekspor supplier. |
| 12 | `tb_kategori_aktivitas` | 3 | **DIPAKAI LANGSUNG** | `CategoryActivityModel`, `ActivityController`, `admin/CategoryActivityController` | Publik & Admin kategori aktivitas | **Aktif / Jangan Hapus.** Master kategori untuk `tb_aktivitas`. |
| 13 | `tb_kategori_artikel` | 3 | **DIPAKAI LANGSUNG** | `CategoryArtikelModel`, `KategoriModel`, `ArticleeController`, `admin/CategoryArticleController` | Publik & Admin kategori artikel | **Aktif / Jangan Hapus.** Master kategori (FK induk dari `tb_artikel`). |
| 14 | `tb_kategori_produk` | 8 | **DIPAKAI LANGSUNG** | `CategoryProductModel`, `ProductController`, `admin/KategoriProduk` | Publik: Filter kategori di `/product`; Admin: `/admin/kategori_produk` | **Aktif / Jangan Hapus.** Master kategori untuk katalog publik `tb_produk`. |
| 15 | `tb_kontak` | 1 | **DIPAKAI LANGSUNG** | `KontakModel`, `ContactController`, `Home`, `AboutController`, `admin/KontakController` | Publik: Halaman `/contact` dan Footer Web; Admin: `/admin/kontak` | **Aktif / Jangan Hapus.** Menyimpan data narasi kantor Malang, gudang Sumbar, WA, email. |
| 16 | `tb_marketplace` | 0 | **DIPAKAI LANGSUNG** | `MarketplaceModel`, `AboutController`, `Home`, `ProductController`, `admin/MarketplaceController` | Publik: Tautan marketplace; Admin: `/admin/marketplace` | **Aktif (Struktur Dipakai).** Meski 0 baris, dicall aktif di view/controller. Jangan dihapus. |
| 17 | `tb_meta` | 9 | **DIPAKAI LANGSUNG** | `MetaModel`, `admin/MetaController`, dipanggil langsung via raw query di 8 view publik | Publik: Meta Title, Deskripsi & Keyword SEO tiap halaman; Admin: `/admin/meta` | **Kritis / Jangan Hapus.** Menyimpan metadata SEO seluruh halaman publik. |
| 18 | `tb_produk` | 254 | **DIPAKAI LANGSUNG** | `ProductModel`, `CategoryProductModel`, `ProductController`, `admin/ProductController` | Publik: Halaman utama `/product` & `/product/detail/...`; Admin: `/admin/produk` | **Kritis / Jangan Hapus.** Database katalog utama website Indonesian Exporter. |
| 19 | `tb_produk_supplier` | 11 | **DIPAKAI LANGSUNG** | `SupplierProductModel`, `ProdukSalesModel`, `SalesActivityModel`, `Sales/Produk`, `admin/ProdukSales`, `Supplier/Produk` | Sales: Katalog `/sales/produk-supplier`; Admin: `/admin/produk_sales`; Supplier: `/supplier/produk` | **Kritis / Jangan Hapus.** Katalog produk supplier mandiri & sales, memuat kolom `deskripsi`. |
| 20 | `tb_profil` | 2 | **DIPAKAI LANGSUNG** | `ProfilModel`, `SalesActivityModel`, `SearchHistoryModel`, `Loginctrl`, `Auth`, `LoginController` | Admin: Autentikasi Login (`role=user`); Sales: Fallback Login (`role=sales`); Publik: Tentang Kami | **Kritis / Jangan Hapus.** Menyimpan akun admin & profil perusahaan. |
| 21 | `tb_sales` | 1 | **DIPAKAI LANGSUNG** | `SalesModel`, `SalesActivityModel`, `SearchHistoryModel`, `Auth`, `LoginController`, `admin/Akun` | Sales: Autentikasi login sales; Admin: `/admin/akun` | **Kritis / Jangan Hapus.** Menyimpan master akun sales representative (`fernandes`). |
| 22 | `tb_sales_produk` | 8 | **MENCURIGAKAN** | `SalesProductModel` (Hanya didefinisikan, tidak pernah di-instansiasi di controller/view mana pun) | Tidak terhubung ke halaman aktif mana pun (kode mati) | **Kandidat Hapus (Risiko Rendah).** Struktur identik dengan `tb_produk`. Sisa import Excel lama. |
| 23 | `tb_slider` | 3 | **DIPAKAI LANGSUNG** | `SliderModel`, `Home`, `admin/DashboardController`, `admin/Profil`, `admin/Slider` | Publik: Banner slider beranda utama; Admin: `/admin/slider` | **Aktif / Jangan Hapus.** Menyimpan banner visual beranda. |
| 24 | `tb_sosmed` | 0 | **DIPAKAI LANGSUNG** | `SosmedModel`, `AboutController`, `Home`, `ProductController`, `admin/SosmedController` | Publik: Icon media sosial; Admin: `/admin/sosmed` | **Aktif (Struktur Dipakai).** Walau 0 baris, dicall controller. Jangan dihapus tanpa refactor. |
| 25 | `tb_supplier` | 85 | **DIPAKAI LANGSUNG** | `SupplierModel`, `SupplierProductModel`, `ProdukSalesModel`, `SalesActivityModel`, `Sales/Produk`, `admin/Akun`, `Supplier/Perusahaan` | Sales: Nama perusahaan supplier; Admin: `/admin/akun`, `/admin/produk_sales`; Supplier: Dashboard | **Kritis / Jangan Hapus.** Master data profil seluruh perusahaan supplier mitra. |
| 26 | `tb_supplier_user` | 1 | **DIPAKAI LANGSUNG** | `SupplierUserModel`, `Supplier/Perusahaan`, `Supplier/Auth`, `admin/Akun`, `Commands/CreateSupplierUser` | Supplier: Login portal `/supplier/login`; Admin: Tab Akun Supplier di `/admin/akun` | **Kritis / Jangan Hapus.** Menyimpan akun kredensial portal supplier (`supplier1`). |
| 27 | `users` | 2 | **DIPAKAI LANGSUNG** | `UserModel`, `LoginController`, `admin/Loginctrl` | Admin: Autentikasi login tahap 1 (`admin@example.com`, `sales@example.com`) | **Kritis / Jangan Hapus.** Tabel akun pengguna bawaan framework. |

---

## 2. Pengelompokan Tabel dengan Nama Mirip & Analisis Perbedaan Fungsinya

### Kelompok A: Tabel Terkait Produk
Terdapat 5 tabel yang memiliki penamaan seputar kata "produk" atau "product":
1. **`tb_produk` (254 baris)** vs **`tb_produk_supplier` (11 baris)**:
   - **Perbedaan Fungsi:**
     - `tb_produk` adalah katalog produk utama website publik Indonesian Exporter (ditampilkan untuk pengunjung umum di halaman `/product`). Kolomnya mendukung bilingual (`nama_produk_id`, `nama_produk_en`, `slug_id`, `slug_en`).
     - `tb_produk_supplier` adalah katalog produk khusus mitra supplier binaan Indonesian Exporter untuk area Sales dan Supplier (berisi komisi, kapasitas produksi, MOQ, harga EXW, spesifikasi, dan deskripsi khusus).
   - **Duplikasi:** Tidak duplikat. `tb_produk` adalah produk dagang korporat utama, sedangkan `tb_produk_supplier` adalah produk konsinyasi/kemitraan supplier lokal.
2. **`tb_produk_supplier` (11 baris)** vs **`tb_ecp_ekspor` (117 baris)**:
   - **Perbedaan Fungsi:** Keduanya di-unify oleh `ProdukSalesModel::getUnifiedProducts()` menjadi total 128 produk di area Sales. `tb_produk_supplier` adalah produk supplier internal yang bisa ditambah/diedit admin, sedangkan `tb_ecp_ekspor` adalah data statis alumni Export Coaching Program dari Kementerian Perdagangan.
3. **`tb_produk` (254 baris)** vs **`tb_sales_produk` (8 baris)**:
   - **Perbedaan Fungsi & Duplikasi:** Kolom `tb_sales_produk` 100% identik dengan `tb_produk` (17 kolom yang sama persis). Isinya adalah 8 produk awal yang pernah dimasukkan saat eksperimen awal sebelum arsitektur `tb_produk_supplier` dibuat. Saat ini `tb_sales_produk` sudah ditinggalkan dan tidak dipakai oleh fitur mana pun.
4. **`products` (5 baris)** vs **`tb_produk` (254 baris)**:
   - **Perbedaan Fungsi & Duplikasi:** `products` adalah tabel prototipe bawaan migrasi awal (`CreateProductsTable`) dengan kolom Inggris sederhana (`id`, `nama_produk`, `kategori`, `deskripsi`, `gambar_utama`, `status`). Tabel ini tidak pernah dihubungkan ke model mana pun karena aplikasi beralih ke `tb_produk`.

---

### Kelompok B: Tabel Pengguna & Autentikasi
Terdapat 4 tabel penyimpanan kredensial akun:
1. **`users` (2 baris)**:
   - Menyimpan akun sistem berbasis email (`admin@example.com`, `sales@example.com`).
   - Dipakai sebagai autentikasi pertama pada `LoginController::process()`.
2. **`tb_profil` (2 baris)**:
   - Tabel hybrid legacy: menyimpan deskripsi profil perusahaan website sekaligus bertindak sebagai akun autentikasi admin (`username='user'`, `role='user'`) di `admin/Loginctrl::index()` dan fallback login sales di `Auth::attempt()`.
3. **`tb_sales` (1 baris)**:
   - Menyimpan akun khusus tenaga sales (`id_sales`, `username='fernandes'`, `tipe_akses='internal'`).
   - Terhubung secara logis ke seluruh tabel riwayat aktivitas (`user_id`).
4. **`tb_supplier_user` (1 baris)**:
   - Menyimpan akun login khusus mitra supplier (`id_supplier_user`, `username='supplier1'`, `id_supplier=72`).
   - Berelasi dengan foreign key resmi ke `tb_supplier.id_supplier`.

*Rekomendasi:* Meskipun ada 4 tabel pengguna yang berbeda, saat ini **keempatnya aktif dipakai** pada modul masing-masing (jangan dihapus salah satunya tanpa refactoring sistem autentikasi menyeluruh).

---

### Kelompok C: Tabel Terkait Supplier
Terdapat 4 tabel di ekosistem supplier:
1. **`tb_supplier` (85 baris)**: Tabel master profil perusahaan supplier (induk).
2. **`tb_supplier_user` (1 baris)**: Akun login web untuk pemilik perusahaan supplier (anak via FK `id_supplier`).
3. **`tb_produk_supplier` (11 baris)**: Produk-produk yang dihasilkan oleh perusahaan supplier (anak via FK `id_supplier`).
4. **`tb_history_export_supplier` (117 baris)**: Riwayat ekspor nyata yang pernah dibukukan oleh supplier (anak via FK `id_supplier`).

*Status:* Hubungan relasi sangat rapi dan konsisten (1 perusahaan supplier memiliki 1 user login, banyak produk, dan banyak history ekspor).

---

### Kelompok D: Tabel Riwayat Aktivitas Sales
Terdapat 5 tabel history:
1. `search_history`: Kata kunci pencarian & jumlah hasil.
2. `login_history`: IP address, browser User-Agent, dan waktu login.
3. `detail_click_history`: Klik melihat rincian produk (`product_id`, `product_name`).
4. `commission_click_history`: Klik melihat rincian komisi.
5. `pdf_history`: Klik cetak/unduh PDF (`pdf_type`, `product_id`).

*Status:* Kelimanya saling melengkapi dan menjadi basis data untuk Dashboard Admin serta 8 halaman Laporan Aktivitas. Seluruh tabel telah dilengkapi kolom `view_mode` (`internal` / `eksternal`).

---

### Kelompok E: Tabel Konten & Web
1. `tb_artikel` & `tb_kategori_artikel`: Konten blog/edukasi ekspor.
2. `tb_aktivitas` & `tb_kategori_aktivitas`: Konten kegiatan & dokumentasi pameran.
3. `tb_marketplace` & `tb_sosmed`: Link toko online & link akun sosial media resmi.
4. `tb_kontak`: Profil alamat fisik kantor, gudang, telepon, dan link WhatsApp.
5. `tb_meta`: Pengaturan SEO title/description per halaman.
6. `tb_slider`: Foto carousel beranda depan.

---

## 3. Catatan Khusus: "Ghost Models" di Direktori `app/Models/`
Ditemukan 2 file model di dalam kode yang mereferensikan tabel yang **TIDAK ADA** di database MySQL:
1. `app/Models/SupplierProdukModel.php`: Mereferensikan `$table = 'tb_supplier_produk'`.
   - *Fakta:* Tabel di MySQL bernama `tb_produk_supplier` (dikelola oleh `SupplierProductModel.php`). File ini merupakan artefak lama.
2. `app/Models/VisitorLogModel.php`: Mereferensikan `$table = 'tb_visitor_log'`.
   - *Fakta:* Tabel `tb_visitor_log` tidak pernah dibuat di database, dan model ini tidak dipanggil oleh controller mana pun.

---

## 4. Daftar Tabel Kandidat Hapus (Diurutkan dari Risiko Paling Rendah)

Berdasarkan hasil audit menyeluruh, dari 27 tabel yang ada di database, terdapat **2 tabel kandidat hapus**:

### 1. Tabel `products` (Urutan Risiko #1 - Paling Rendah)
- **Jumlah Baris:** 5 baris.
- **Tingkat Risiko:** **SANGAT RENDAH** (1/10).
- **Alasan & Bukti Nyata:**
  1. Tidak ada Model aktif yang menggunakan tabel `products`. Model produk utama aplikasi adalah `ProductModel` yang menggunakan `tb_produk`.
  2. Tidak ada Controller, View, atau Library yang mengeksekusi query `FROM products` atau `->table('products')`.
  3. Hanya ada 2 file di seluruh proyek yang memuat teks tabel ini, yaitu migrasi awal `app/Database/Migrations/2026-10-13-150002_CreateProductsTable.php` dan seeder awal `app/Database/Seeds/ProductSeeder.php`.
  4. Isi datanya hanyalah 5 baris data dummy seeder (Kopi Arabika Gayo, Teh Hijau, dll.) yang tidak terhubung dengan transaksi atau fitur apa pun.
  5. Tidak memiliki Foreign Key masuk maupun keluar.

### 2. Tabel `tb_sales_produk` (Urutan Risiko #2 - Rendah)
- **Jumlah Baris:** 8 baris.
- **Tingkat Risiko:** **RENDAH** (2/10).
- **Alasan & Bukti Nyata:**
  1. Struktur skemanya 100% identik dengan `tb_produk` (17 kolom persis sama).
  2. Model pendampingnya (`app/Models/SalesProductModel.php`) berstatus **Dead Code** (tidak pernah di-instansiasi `new SalesProductModel()` di controller atau service mana pun).
  3. Modul Sales aktif saat ini menggunakan `ProdukSalesModel` dan `SupplierProductModel` yang bersumber dari tabel `tb_produk_supplier` dan `tb_ecp_ekspor`.
  4. 8 baris data di dalamnya merupakan sisa import Excel tahap 1 yang datanya sudah direpresentasikan secara lebih lengkap di `tb_produk_supplier` dan `tb_supplier`.
  5. Tidak ada relasi Foreign Key ke tabel lain.

---

## 5. Kesimpulan & Rekomendasi
- Dari **27 tabel** di database `k4991560_indonesian-exporter`:
  - **24 tabel DIPAKAI LANGSUNG** oleh aplikasi (website publik, dashboard, sales, supplier, maupun admin).
  - **1 tabel SISTEM** (`migrations`) yang wajib dipertahankan untuk integritas CodeIgniter 4.
  - **2 tabel KANDIDAT HAPUS** (`products` dan `tb_sales_produk`) yang merupakan sisa artefak prototipe lama.
- Seluruh 25 tabel lainnya dalam kondisi sehat, aktif terintegrasi, dan memiliki data operasional yang valid.

