<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'nama_produk'        => 'Kopi Arabika Gayo Specialty',
                'kategori'           => 'Minuman & Kopi',
                'deskripsi'          => 'Kopi Arabika single origin dari dataran tinggi Gayo Aceh dengan proses full washed, menghasilkan aroma floral dan citarasa fruity yang seimbang untuk pasar specialty coffee global.',
                'gambar_utama'       => 'assets/img/produk/kopi.jpg',
                'domisili_usaha'     => 'Aceh Tengah, D.I. Aceh',
                'jenis_perusahaan'   => 'PT',
                'jumlah_pegawai'     => '21-50',
                'kapasitas_produksi' => '25 Ton per Bulan',
                'produk_dipasarkan'  => json_encode(['Green Beans', 'Roasted Beans', 'Ground Coffee']),
                'produk_siap_ekspor' => json_encode(['Grade 1 Specialty Arabica', 'Organic Certified Beans']),
                'frekuensi_ekspor'   => 'Rutin bulanan',
                'status'             => 'published',
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'nama_produk'        => 'Biji Kopi Robusta Lampung Premium',
                'kategori'           => 'Minuman & Kopi',
                'deskripsi'          => 'Biji kopi Robusta petik merah pilihan dari perkebunan lereng pegunungan Lampung. Karakter body tebal, aroma cokelat karamel khas, sangat diminati oleh industri roasting Eropa dan Asia.',
                'gambar_utama'       => 'assets/img/produk/biji-kopi.jpg',
                'domisili_usaha'     => 'Lampung Barat, Lampung',
                'jenis_perusahaan'   => 'CV',
                'jumlah_pegawai'     => '51-100',
                'kapasitas_produksi' => '50 Ton per Bulan',
                'produk_dipasarkan'  => json_encode(['Green Beans Fine Robusta', 'Medium Dark Roast']),
                'produk_siap_ekspor' => json_encode(['Grade 1 Fine Robusta', 'Grade 2 Commercial Beans']),
                'frekuensi_ekspor'   => '>5x per tahun',
                'status'             => 'published',
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'nama_produk'        => 'Madu Murni Akasia Organik',
                'kategori'           => 'Hasil Hutan & Pangan Alami',
                'deskripsi'          => 'Madu murni alami yang dipanen dari nektar bunga pohon akasia (Acacia mangium) di hutan Sumatra. Kadar air terkontrol di bawah 18%, tanpa pemanasan dan tanpa pengawet.',
                'gambar_utama'       => 'assets/img/produk/madu-murni-akasia.jpg',
                'domisili_usaha'     => 'Riau, Sumatra',
                'jenis_perusahaan'   => 'Koperasi',
                'jumlah_pegawai'     => '6-20',
                'kapasitas_produksi' => '10 Ton per Bulan',
                'produk_dipasarkan'  => json_encode(['Madu Kemasan Jar 500g', 'Madu Bulk Drum 300kg']),
                'produk_siap_ekspor' => json_encode(['Raw Acacia Honey Grade A', 'Halal & Lab Certified Honey']),
                'frekuensi_ekspor'   => '3-5x per tahun',
                'status'             => 'published',
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'nama_produk'        => 'Kelapa Parut Kering (Desiccated Coconut)',
                'kategori'           => 'Komoditas Olahan Kelapa',
                'deskripsi'          => 'Kelapa parut kering kualitas ekspor yang diproses secara higienis dari kelapa segar matang. Bebas dari kontaminasi dan pengawet, tersedia dalam varian Fine Grade dan Medium Grade.',
                'gambar_utama'       => 'assets/img/produk/kelapa-parut-kering.jpg',
                'domisili_usaha'     => 'Minahasa Selatan, Sulawesi Utara',
                'jenis_perusahaan'   => 'PT',
                'jumlah_pegawai'     => '100+',
                'kapasitas_produksi' => '120 Ton per Bulan',
                'produk_dipasarkan'  => json_encode(['Fine Grade High Fat', 'Medium Grade Reduced Fat']),
                'produk_siap_ekspor' => json_encode(['Desiccated Coconut 25kg Kraft Bag', 'Bulk Container Pack']),
                'frekuensi_ekspor'   => 'Rutin bulanan',
                'status'             => 'published',
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'nama_produk'        => 'Wallpanel Kayu Jati Daur Ulang (Recycled Teak)',
                'kategori'           => 'Furniture & Dekorasi Interior',
                'deskripsi'          => 'Panel dinding 3D artistik ramah lingkungan yang diproduksi dari potongan kayu jati daur ulang bekas bangunan tua (reclaimed teak). Finishing natural tanpa bahan kimia berbahaya.',
                'gambar_utama'       => 'assets/img/produk/wallpanel-recycled-teak.jpg',
                'domisili_usaha'     => 'Jepara, Jawa Tengah',
                'jenis_perusahaan'   => 'CV',
                'jumlah_pegawai'     => '21-50',
                'kapasitas_produksi' => '1.500 m2 per Bulan',
                'produk_dipasarkan'  => json_encode(['Wallpanel Interlock 20x50cm', 'Teak Mosaic Tile']),
                'produk_siap_ekspor' => json_encode(['Export Box Pack (1 m2/box)', 'FSC/SVLK Certified Wood']),
                'frekuensi_ekspor'   => '3-5x per tahun',
                'status'             => 'published',
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($products as $p) {
            $existing = $this->db->table('products')->where('nama_produk', $p['nama_produk'])->get()->getRowArray();
            if ($existing) {
                $this->db->table('products')->where('id', $existing['id'])->update($p);
            } else {
                $this->db->table('products')->insert($p);
            }
        }
    }
}
