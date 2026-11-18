# Daftar Perubahan Kode

- **Branch**: `fitur-produk-lain`
- **Commit pembanding terakhir**: `2f45ac1` (`fix`)
- **Tanggal ekspor**: 2026-11-18
- **Total file berubah**: 4 file (4 ubah, 0 baru, 0 hapus)

## Tabel Perubahan File

| Path File | Status | Penjelasan Singkat |
| :--- | :---: | :--- |
| `app/Controllers/Sales/Produk.php` | ubah | Mengganti sumber `$produkLain` pada `buildDetailData()` (jalur produk supplier dan jalur produk ECP) agar memanggil `ProdukSalesModel::getOtherUnifiedProductsByCompany()` — fitur **Produk Lain Gabungan (Supplier + ECP)** di halaman detail produk Sales. |
| `app/Models/ProdukSalesModel.php` | ubah | Menambahkan method `getOtherUnifiedProductsByCompany()` untuk menggabungkan produk lain dari `tb_produk_supplier` dan `tb_ecp_ekspor` menggunakan normalisasi nama perusahaan (`SupplierModel::makeKey()`) di kedua sisi — fitur **Produk Lain Gabungan (Supplier + ECP)** di halaman detail produk Sales. |
| `app/Views/admin/produk_sales/index.php` | ubah | Menghapus tombol hijau besar `"+ Tambah Produk Sales"` beserta wrapper-nya di header halaman sambil mempertahankan ikon `+` di kolom Aksi — penyederhanaan tombol tambah di halaman **Admin Produk Supplier**. |
| `app/Views/sales/produk/detail.php` | ubah | Menggunakan `foto_url` dari data seragam `$produkLain` dan merapikan `esc($pLainDetailUrl)` pada atribut `href` di blok "Produk Lain" — fitur **Produk Lain Gabungan (Supplier + ECP)** di halaman detail produk Sales. |

## Perubahan Struktur Database

**Tidak ada perubahan struktur database** (tidak ada migration baru, tabel baru, kolom baru, maupun perintah SQL manual terhadap skema database pada rangkaian perubahan ini).

