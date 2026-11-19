<?php

namespace App\Libraries;

/**
 * Helper pemformatan tampilan lokasi dan pemetaan daerah (kota/kabupaten -> provinsi)
 * khusus untuk halaman Admin Produk Supplier tanpa mengubah struktur maupun data database.
 */
class LocationHelper
{
    /**
     * Daftar provinsi dan kata kunci kota/kabupaten/kecamatan/alias yang termasuk di dalamnya.
     * Semua nilai dalam huruf kecil.
     *
     * @var array<string, string[]>
     */
    private const PROVINCE_KEYWORDS = [
        'jawa timur' => [
            'jawa timur', 'jatim',
            'surabaya', 'malang', 'kedungkandang', 'banyuwangi', 'sidoarjo',
            'mojokerto', 'gresik', 'cerme', 'sumenep', 'pragaan', 'pasuruan',
            'panggungrejo', 'blitar', 'situbondo', 'jember', 'kediri', 'madiun',
            'probolinggo', 'tulungagung', 'lumajang', 'bondowoso', 'bojonegoro',
            'tuban', 'lamongan', 'jombang', 'nganjuk', 'ponorogo', 'pacitan',
            'trenggalek', 'magetan', 'ngawi', 'bangkalan', 'sampang', 'pamekasan',
            'batu',
        ],
        'jawa barat' => [
            'jawa barat', 'jabar',
            'bandung', 'bandung barat', 'buahbatu', 'bandung kidul', 'regol',
            'balong gede', 'cisarua', 'parongpong', 'paseh', 'ibun',
            'bogor', 'cibinong', 'tenjolaya', 'cirebon', 'plered', 'cianjur',
            'depok', 'purwakarta', 'sukabumi', 'cibitung', 'sumedang',
            'bekasi', 'karawang', 'garut', 'tasikmalaya', 'ciamis', 'kuningan',
            'majalengka', 'indramayu', 'subang', 'cimahi', 'banjar', 'pangandaran',
        ],
        'jawa tengah' => [
            'jawa tengah', 'jateng',
            'semarang', 'klaten', 'jepara', 'pakis aji', 'magelang', 'pekalongan',
            'purworejo', 'pati', 'sukoharjo', 'kartasura', 'surakarta', 'solo',
            'karanganyar', 'gondangrejo', 'cilacap', 'banyumas', 'purwokerto',
            'purbalingga', 'banjarnegara', 'kebumen', 'wonosobo', 'boyolali',
            'wonogiri', 'sragen', 'grobogan', 'blora', 'rembang', 'kudus',
            'demak', 'temanggung', 'kendal', 'batang', 'pemalang', 'tegal',
            'brebes', 'salatiga',
        ],
        'daerah istimewa yogyakarta' => [
            'daerah istimewa yogyakarta', 'yogyakarta', 'jogjakarta', 'jogja', 'diy',
            'bantul', 'pundong', 'sleman', 'kulon progo', 'gunungkidul',
        ],
        'dki jakarta' => [
            'dki jakarta', 'jakarta', 'jakarta pusat', 'jakarta utara',
            'jakarta barat', 'jakarta selatan', 'jakarta timur',
            'grogol petamburan', 'mampang prapatan', 'pondok indah',
            'kepulauan seribu',
        ],
        'banten' => [
            'banten', 'tangerang', 'tangerang selatan', 'lebak', 'rangkasbitung',
            'serang', 'cilegon', 'pandeglang',
        ],
        'riau' => [
            'riau', 'pekanbaru', 'siak', 'siak sri indrapura', 'dayun', 'tualang',
            'bengkalis', 'bantan', 'indragiri hilir', 'indragiri hikir',
            'indragiri hulu', 'kampar', 'tapung', 'kepulauan meranti',
            'kepulaun meranti', 'rangsang pesisir', 'dumai', 'pelalawan',
            'rokan hilir', 'rokan hulu', 'kuantan singingi',
        ],
        'sumatera barat' => [
            'sumatera barat', 'sumbar', 'padang', 'padang pariaman', 'pariaman',
            'bukittinggi', 'payakumbuh', 'solok', 'sawahlunto', 'padang panjang',
            'agam', 'tanah datar', 'lima puluh kota', 'pesisir selatan',
            'pasaman', 'sijunjung', 'dharmasraya', 'mentawai',
        ],
        'kalimantan barat' => [
            'kalimantan barat', 'kalbar', 'pontianak', 'kubu raya', 'singkawang',
            'sambas', 'mempawah', 'sanggau', 'ketapang', 'sintang', 'kapuas hulu',
            'bengkayang', 'landak', 'sekadau', 'melawi', 'kayong utara',
        ],
        'aceh' => [
            'aceh', 'banda aceh', 'sabang', 'lhokseumawe', 'langsa', 'subulussalam',
        ],
        'sumatera utara' => [
            'sumatera utara', 'sumut', 'medan', 'binjai', 'tebing tinggi',
            'pematangsiantar', 'tanjungbalai', 'sibolga', 'padangsidimpuan',
            'deli serdang', 'karo', 'simalungun', 'asahan', 'labuhanbatu', 'nias',
        ],
        'sumatera selatan' => [
            'sumatera selatan', 'sumsel', 'palembang', 'prabumulih', 'pagar alam',
            'lubuklinggau', 'banyuasin', 'ogan ilir', 'muara enim', 'lahat',
        ],
        'jambi' => [
            'jambi', 'sungai penuh', 'batanghari', 'bungo', 'kerinci', 'merangin',
            'muaro jambi', 'sarolangun', 'tanjung jabung', 'tebo',
        ],
        'bengkulu' => [
            'bengkulu', 'curup', 'rejang lebong', 'mukomuko', 'seluma', 'kaur',
        ],
        'lampung' => [
            'lampung', 'bandar lampung', 'metro', 'pesawaran', 'pringsewu', 'tanggamus',
        ],
        'kepulauan bangka belitung' => [
            'bangka belitung', 'babel', 'pangkalpinang', 'bangka', 'belitung',
        ],
        'kepulauan riau' => [
            'kepulauan riau', 'kepri', 'batam', 'tanjungpinang', 'bintan', 'karimun', 'natuna',
        ],
        'bali' => [
            'bali', 'denpasar', 'badung', 'gianyar', 'tabanan', 'buleleng',
            'klungkung', 'bangli', 'karangasem', 'jembrana',
        ],
        'nusa tenggara barat' => [
            'nusa tenggara barat', 'ntb', 'mataram', 'bima', 'lombok', 'sumbawa', 'dompu',
        ],
        'nusa tenggara timur' => [
            'nusa tenggara timur', 'ntt', 'kupang', 'flores', 'sumba', 'alor', 'ende', 'manggarai',
        ],
        'kalimantan tengah' => [
            'kalimantan tengah', 'kalteng', 'palangka raya', 'palangkaraya',
            'kotawaringin', 'kapuas', 'barito',
        ],
        'kalimantan selatan' => [
            'kalimantan selatan', 'kalsel', 'banjarmasin', 'banjarbaru',
            'martapura', 'kotabaru', 'tabalong', 'tanah bumbu', 'tanah laut',
        ],
        'kalimantan timur' => [
            'kalimantan timur', 'kaltim', 'samarinda', 'balikpapan', 'bontang',
            'kutai', 'berau', 'paser',
        ],
        'kalimantan utara' => [
            'kalimantan utara', 'kaltara', 'tarakan', 'bulungan', 'malinau', 'nunukan',
        ],
        'sulawesi utara' => [
            'sulawesi utara', 'sulut', 'manado', 'bitung', 'tomohon', 'kotamobagu', 'minahasa',
        ],
        'sulawesi tengah' => [
            'sulawesi tengah', 'sulteng', 'palu', 'donggala', 'poso', 'tolitoli', 'morowali', 'banggai',
        ],
        'sulawesi selatan' => [
            'sulawesi selatan', 'sulsel', 'makassar', 'parepare', 'palopo',
            'gowa', 'maros', 'bone', 'bulukumba', 'toraja', 'wajo', 'pinrang',
        ],
        'sulawesi tenggara' => [
            'sulawesi tenggara', 'sultra', 'kendari', 'baubau', 'konawe', 'kolaka', 'muna', 'buton',
        ],
        'gorontalo' => [
            'gorontalo', 'boalemo', 'bone bolango', 'pohuwato',
        ],
        'sulawesi barat' => [
            'sulawesi barat', 'sulbar', 'mamuju', 'majene', 'polewali mandar', 'mamasa',
        ],
        'maluku' => [
            'maluku', 'ambon', 'tual', 'seram', 'buru',
        ],
        'maluku utara' => [
            'maluku utara', 'malut', 'ternate', 'tidore', 'halmahera',
        ],
        'papua' => [
            'papua', 'jayapura', 'sorong', 'manokwari', 'merauke', 'timika', 'mimika', 'biak', 'nabire',
        ],
    ];

    /**
     * Alias nama provinsi singkat/alternatif -> nama kanonik provinsi (key pada PROVINCE_KEYWORDS).
     *
     * @var array<string, string>
     */
    private const PROVINCE_ALIASES = [
        'jatim'                      => 'jawa timur',
        'jawa timur'                 => 'jawa timur',
        'jabar'                      => 'jawa barat',
        'jawa barat'                 => 'jawa barat',
        'jateng'                     => 'jawa tengah',
        'jawa tengah'                => 'jawa tengah',
        'diy'                        => 'daerah istimewa yogyakarta',
        'daerah istimewa yogyakarta' => 'daerah istimewa yogyakarta',
        'dki jakarta'                => 'dki jakarta',
        'banten'                     => 'banten',
        'riau'                       => 'riau',
        'provinsi riau'              => 'riau',
        'sumbar'                     => 'sumatera barat',
        'sumatera barat'             => 'sumatera barat',
        'kalbar'                     => 'kalimantan barat',
        'kalimantan barat'           => 'kalimantan barat',
        'aceh'                       => 'aceh',
        'sumut'                      => 'sumatera utara',
        'sumatera utara'             => 'sumatera utara',
        'sumsel'                     => 'sumatera selatan',
        'sumatera selatan'           => 'sumatera selatan',
        'jambi'                      => 'jambi',
        'bengkulu'                   => 'bengkulu',
        'lampung'                    => 'lampung',
        'babel'                      => 'kepulauan bangka belitung',
        'bangka belitung'            => 'kepulauan bangka belitung',
        'kepulauan bangka belitung'  => 'kepulauan bangka belitung',
        'kepri'                      => 'kepulauan riau',
        'kepulauan riau'             => 'kepulauan riau',
        'bali'                       => 'bali',
        'ntb'                        => 'nusa tenggara barat',
        'nusa tenggara barat'        => 'nusa tenggara barat',
        'ntt'                        => 'nusa tenggara timur',
        'nusa tenggara timur'        => 'nusa tenggara timur',
        'kalteng'                    => 'kalimantan tengah',
        'kalimantan tengah'          => 'kalimantan tengah',
        'kalsel'                     => 'kalimantan selatan',
        'kalimantan selatan'         => 'kalimantan selatan',
        'kaltim'                     => 'kalimantan timur',
        'kalimantan timur'           => 'kalimantan timur',
        'kaltara'                    => 'kalimantan utara',
        'kalimantan utara'           => 'kalimantan utara',
        'sulut'                      => 'sulawesi utara',
        'sulawesi utara'             => 'sulawesi utara',
        'sulteng'                    => 'sulawesi tengah',
        'sulawesi tengah'            => 'sulawesi tengah',
        'sulsel'                     => 'sulawesi selatan',
        'sulawesi selatan'           => 'sulawesi selatan',
        'sultra'                     => 'sulawesi tenggara',
        'sulawesi tenggara'          => 'sulawesi tenggara',
        'gorontalo'                  => 'gorontalo',
        'sulbar'                     => 'sulawesi barat',
        'sulawesi barat'             => 'sulawesi barat',
        'maluku'                     => 'maluku',
        'malut'                      => 'maluku utara',
        'maluku utara'               => 'maluku utara',
        'papua'                      => 'papua',
    ];

    /**
     * Menghilangkan awalan/kata berdiri sendiri "Kabupaten", "Kab.", "Kab", dan "Kota"
     * HANYA untuk kebutuhan tampilan di halaman Admin Produk Supplier.
     * Tidak merusak nama wilayah yang memuat kata tersebut di tengah kata (mis. "Kotabaru").
     */
    public static function formatDisplayLocation(?string $lokasi): string
    {
        $lokasi = trim((string)$lokasi);
        if ($lokasi === '' || $lokasi === '-') {
            return $lokasi;
        }

        // Hapus kata berdiri sendiri "Kabupaten", "Kab.", "Kab", "Kota" (case-insensitive)
        $clean = preg_replace('/\b(?:kabupaten|kab\.|kab|kota)\b\.?\s*/iu', '', $lokasi);
        // Rapikan spasi di sekitar koma & slash serta spasi ganda
        $clean = preg_replace('/\s*,\s*/', ', ', (string)$clean);
        $clean = preg_replace('/\s*\/\s*/', '/', (string)$clean);
        $clean = preg_replace('/\s{2,}/', ' ', (string)$clean);
        $clean = trim((string)$clean, " \t\n\r\0\x0B,/");

        return $clean !== '' ? $clean : $lokasi;
    }

    /**
     * Mengembalikan nama provinsi kanonik jika teks yang diklik/difilter
     * adalah nama provinsi (mis. "Jawa Timur", "Jawa Barat", "DKI Jakarta").
     * Mengembalikan null jika bukan provinsi (mis. kota/kabupaten/alamat spesifik).
     */
    public static function resolveExactProvince(string $daerah): ?string
    {
        $norm = self::normalizeText(self::formatDisplayLocation($daerah));
        $norm = preg_replace('/^(?:provinsi|propinsi|prov\.?)\s+/u', '', $norm);
        $norm = trim((string)$norm);

        return self::PROVINCE_ALIASES[$norm] ?? null;
    }

    /**
     * Mendeteksi provinsi dari teks lokasi bebas (bisa berupa provinsi, kota, kabupaten, atau alamat).
     */
    public static function detectProvinceFromLocation(string $lokasi): ?string
    {
        $norm = self::normalizeText($lokasi);
        if ($norm === '' || $norm === '-') {
            return null;
        }

        // 1. Prioritas pertama: nama provinsi eksplisit yang muncul di teks (mis. "Klaten, Jawa Tengah")
        foreach (array_keys(self::PROVINCE_KEYWORDS) as $provName) {
            if (preg_match('/\b' . preg_quote($provName, '/') . '\b/u', $norm)) {
                return $provName;
            }
        }

        // 2. Prioritas kedua: kecocokan kata kunci kota/kabupaten/kecamatan/alias -> provinsi
        foreach (self::PROVINCE_KEYWORDS as $provName => $keywords) {
            foreach ($keywords as $kw) {
                if (preg_match('/\b' . preg_quote($kw, '/') . '\b/u', $norm)) {
                    return $provName;
                }
            }
        }

        return null;
    }

    /**
     * Memeriksa apakah suatu item produk cocok dengan filter daerah yang dipilih.
     * - Jika $daerahFilter adalah nama provinsi -> cocokkan semua produk di provinsi tsb
     *   (termasuk kota/kabupaten di bawah provinsi itu).
     * - Jika $daerahFilter adalah kota/lokasi spesifik -> cocokkan nilai bersih secara tepat
     *   atau sebagai frasa kata utuh, mendukung baik nilai asli DB maupun nilai tampil.
     */
    public static function matchesRegion(array $item, string $daerahFilter): bool
    {
        $daerahFilter = trim($daerahFilter);
        if ($daerahFilter === '' || $daerahFilter === '-') {
            return true;
        }

        $primaryRaw   = trim((string)($item['lokasi_barang'] ?: ($item['domisili_usaha'] ?? '')));
        $primaryClean = self::formatDisplayLocation($primaryRaw);
        if ($primaryRaw === '' || $primaryRaw === '-') {
            return false;
        }

        // 1. Cek apakah filter adalah nama Provinsi
        $targetProvince = self::resolveExactProvince($daerahFilter);
        if ($targetProvince !== null) {
            $itemProvince = self::detectProvinceFromLocation($primaryRaw);
            return $itemProvince === $targetProvince;
        }

        // 2. Filter kota / lokasi spesifik
        $filterClean = self::normalizeText(self::formatDisplayLocation($daerahFilter));
        $itemNorm    = self::normalizeText($primaryClean);
        $itemRawNorm = self::normalizeText($primaryRaw);

        if ($filterClean === '') {
            return true;
        }

        // Sama persis (baik format tampilan maupun format mentah DB)
        if ($itemNorm === $filterClean || $itemRawNorm === self::normalizeText($daerahFilter)) {
            return true;
        }

        // Kasus khusus agar "Bandung" tidak keliru mencocokkan "Bandung Barat" saja,
        // dan "Padang" tidak keliru mencocokkan "Padang Pariaman" saja
        if ($filterClean === 'bandung') {
            return (bool) preg_match('/\bbandung\b(?!\s+barat)/u', $itemNorm);
        }
        if ($filterClean === 'padang') {
            return (bool) preg_match('/\bpadang\b(?!\s+pariaman)/u', $itemNorm);
        }

        // Cocokkan sebagai frasa kata utuh pada teks lokasi bersih
        return (bool) preg_match('/\b' . preg_quote($filterClean, '/') . '\b/u', $itemNorm);
    }

    /**
     * Normalisasi teks lokasi untuk pencocokan: lowercase dan spasi tunggal.
     */
    private static function normalizeText(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim((string)$text);
    }
}

