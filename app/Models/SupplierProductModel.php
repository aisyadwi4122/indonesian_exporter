<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierProductModel extends Model
{
    protected $table            = 'tb_produk_supplier';
    protected $primaryKey       = 'id_produk_supplier';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_supplier',
        'nama_produk',
        'spesifikasi',
        'kapasitas_produksi',
        'legalitas',
        'sertifikasi',
        'lokasi_barang',
        'foto_1',
        'foto_2',
        'foto_3',
        'foto_4',
        'foto_5',
        'moq',
        'harga_exw',
        'komisi',
        'sudah_ekspor',
        'views',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts        = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * static model chain, return $this agar controller bisa memanggil ->paginate()
     */
    public function getListWithSupplier(array $filter = [])
    {
        $this->select('tb_produk_supplier.id_produk_supplier, tb_produk_supplier.nama_produk, tb_produk_supplier.foto_1, tb_produk_supplier.views, tb_produk_supplier.lokasi_barang, tb_supplier.id_supplier, tb_supplier.nama_perusahaan, tb_supplier.jenis_perusahaan')
            ->join('tb_supplier', 'tb_supplier.id_supplier = tb_produk_supplier.id_supplier', 'inner');

        if (! empty($filter['keyword'])) {
            $keyword = trim((string) $filter['keyword']);
            $this->groupStart()
                ->like('tb_produk_supplier.nama_produk', $keyword)
                ->orLike('tb_supplier.nama_perusahaan', $keyword)
                ->orLike('tb_produk_supplier.lokasi_barang', $keyword)
                ->orLike('tb_supplier.produk_dipasarkan', $keyword)
            ->groupEnd();
        }

        $sort = $filter['sort'] ?? 'az';
        switch ($sort) {
            case 'za':
                $this->orderBy('tb_produk_supplier.nama_produk', 'DESC');
                break;
            case 'views':
                $this->orderBy('tb_produk_supplier.views', 'DESC')
                     ->orderBy('tb_produk_supplier.nama_produk', 'ASC');
                break;
            case 'terbaru':
                $this->orderBy('tb_produk_supplier.id_produk_supplier', 'DESC');
                break;
            case 'az':
            default:
                $this->orderBy('tb_produk_supplier.nama_produk', 'ASC');
                break;
        }

        return $this;
    }

    /**
     * Ambil produk (find) lalu supplier-nya (SupplierModel find by id_supplier)
     * Kembalikan null jika produk tidak ada.
     */
    public function getDetail(int $id): ?array
    {
        $produk = $this->find($id);

        if (! $produk) {
            return null;
        }

        $supplierModel = new SupplierModel();
        $supplier      = ! empty($produk['id_supplier']) ? $supplierModel->find($produk['id_supplier']) : null;

        return [
            'produk'   => $produk,
            'supplier' => $supplier,
        ];
    }

    /**
     * Jalankan UPDATE views = views + 1 tanpa mengubah updated_at
     */
    public function incrementViews(int $id): bool
    {
        return (bool) $this->builder()
            ->set('views', 'views + 1', false)
            ->where($this->primaryKey, $id)
            ->update();
    }

    /**
     * Ekstrak file ID Google Drive dari URL.
     * Hanya terima host drive.google.com, docs.google.com, lh3.googleusercontent.com.
     * Link folder (/folders/) dan format di luar spesifikasi dianggap tidak valid (return null).
     * ID harus cocok ^[A-Za-z0-9_-]{10,}$.
     */
    public static function driveFileId(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return null;
        }

        $host = strtolower($parsed['host']);
        $validHosts = ['drive.google.com', 'docs.google.com', 'lh3.googleusercontent.com'];
        $hostValid = false;
        foreach ($validHosts as $vh) {
            if ($host === $vh || str_ends_with($host, '.' . $vh)) {
                $hostValid = true;
                break;
            }
        }
        if (!$hostValid) {
            return null;
        }

        $path = $parsed['path'] ?? '';

        // Link folder dianggap tidak valid
        if (str_contains($path, '/folders/')) {
            return null;
        }

        $id = null;

        // 1. Cek query parameter id (open?id=ID, uc?id=ID, thumbnail?id=ID)
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $queryArgs);
            if (!empty($queryArgs['id']) && is_string($queryArgs['id'])) {
                $id = $queryArgs['id'];
            }
        }

        // 2. Cek path format: /file/d/ID, /d/ID
        if (empty($id) && !empty($path)) {
            if (preg_match('#/(?:file/)?d/([A-Za-z0-9_-]+)#', $path, $matches)) {
                $id = $matches[1];
            }
        }

        if (empty($id) || !preg_match('/^[A-Za-z0-9_-]{10,}$/', $id)) {
            return null;
        }

        return $id;
    }

    /**
     * Pisahkan nilai yang berisi beberapa link (koma, spasi, baris baru), trim, buang yang kosong.
     */
    public static function splitLinks(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $items = preg_split('/[\s,\r\n]+/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
        if (!$items) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            $t = trim($item);
            if ($t !== '') {
                $result[] = $t;
            }
        }

        return array_values($result);
    }

    /**
     * Menghasilkan URL foto produk supplier dengan urutan prioritas:
     * (1) File lokal uploads/supplier/{id}_{n}.jpg jika ada
     * (2) Thumbnail Drive https://drive.google.com/thumbnail?id={ID}&sz=w1000 jika valid
     * (3) Nama file lokal di assets/img/produk atau uploads jika ada
     * (4) null
     */
    public static function photoUrl(array $row, int $n): ?string
    {
        if (!function_exists('base_url')) {
            helper('url');
        }

        $id = (int)($row['id_produk_supplier'] ?? $row['id_produk'] ?? 0);

        // Prioritas (1): file lokal uploads/supplier/{id}_{n}.jpg
        if ($id > 0) {
            $localRel = 'uploads/supplier/' . $id . '_' . $n . '.jpg';
            if (is_file(FCPATH . $localRel)) {
                return function_exists('base_url') ? base_url($localRel) : ('/' . $localRel);
            }
        }

        // Nilai kolom foto_N
        $colVal = $row['foto_' . $n] ?? '';
        if (empty($colVal) && $n === 1 && !empty($row['foto_produk'])) {
            $colVal = $row['foto_produk'];
        }

        if (!empty($colVal) && is_string($colVal)) {
            // Prioritas (2): thumbnail Drive jika ada link Drive valid
            $links = self::splitLinks($colVal);
            foreach ($links as $link) {
                $driveId = self::driveFileId($link);
                if ($driveId !== null) {
                    return 'https://drive.google.com/thumbnail?id=' . $driveId . '&sz=w1000';
                }
            }

            // Prioritas (3): file lokal di assets/img/produk atau uploads
            $trimmed = trim($colVal);
            if (!str_starts_with($trimmed, 'http://') && !str_starts_with($trimmed, 'https://')) {
                if (is_file(FCPATH . 'assets/img/produk/' . $trimmed)) {
                    return function_exists('base_url') ? base_url('assets/img/produk/' . $trimmed) : ('/assets/img/produk/' . $trimmed);
                }
                $cleanPath = ltrim($trimmed, '/\\');
                if (is_file(FCPATH . $cleanPath)) {
                    return function_exists('base_url') ? base_url($cleanPath) : ('/' . $cleanPath);
                }
            }
        }

        // Prioritas (4): null
        return null;
    }

    /**
     * Mengembalikan daftar URL foto yang tersedia (foto_1..foto_5), tanpa null, tanpa duplikat.
     */
    public static function photos(array $row): array
    {
        $list = [];
        for ($i = 1; $i <= 5; $i++) {
            $url = self::photoUrl($row, $i);
            if ($url !== null) {
                $list[] = $url;
            }
        }

        return array_values(array_unique($list));
    }

    /**
     * Menghasilkan kode produk unik standar Indonesian Exporter: IE-XXXX
     */
    public static function getKodeProduk(int $id): string
    {
        return 'IE-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Menyaring satu tempat: membuang field identitas supplier & data sensitif untuk mode eksternal.
     * Semua halaman (daftar, detail, PDF) WAJIB memanggil method ini untuk mode eksternal
     * SEBELUM data dikirim ke view atau template PDF.
     *
     * Field yang dibuang (unset):
     * - Supplier: nama_perusahaan, nama_kunci, nama_kontak, email, no_hp, website, instagram,
     *             legalitas, link_dokumen_legalitas, link_dokumen_sertifikat, link_foto_usaha.
     * - Produk: legalitas, komisi.
     *
     * Menambahkan:
     * - kode_produk = 'IE-' . str_pad(id_produk_supplier, 4, '0', STR_PAD_LEFT)
     */
    public static function maskForExternal(array $data): array
    {
        // 1. Jika data berupa kumpulan baris (list terindeks numerik)
        if (!empty($data) && array_is_list($data)) {
            foreach ($data as $idx => $item) {
                if (is_array($item)) {
                    $data[$idx] = self::maskForExternal($item);
                }
            }
            return $data;
        }

        // Tentukan ID produk untuk membuat kode_produk
        $prodId = (int)(
            $data['produkSupplier']['id_produk_supplier'] 
            ?? ($data['product']['id_produk'] 
            ?? ($data['id_produk_supplier'] 
            ?? ($data['id_produk'] 
            ?? ($data['id'] ?? 0))))
        );
        $kodeProduk = self::getKodeProduk($prodId);
        $data['kode_produk'] = $kodeProduk;

        $companyName = (string)(
            $data['supplier']['nama_perusahaan'] 
            ?? ($data['nama_perusahaan'] ?? '')
        );

        // Jika ada sub-array 'product' (struktur detail / item PDF)
        if (isset($data['product']) && is_array($data['product'])) {
            $data['product']['kode_produk'] = $kodeProduk;
            if ($companyName !== '' && trim((string)($data['product']['nama_produk_en'] ?? '')) === trim($companyName)) {
                $data['product']['nama_produk_en'] = $kodeProduk;
            }
            unset(
                $data['product']['legalitas'],
                $data['product']['komisi']
            );
        }

        // Jika ada sub-array 'produkSupplier'
        if (isset($data['produkSupplier']) && is_array($data['produkSupplier'])) {
            $data['produkSupplier']['kode_produk'] = $kodeProduk;
            unset(
                $data['produkSupplier']['legalitas'],
                $data['produkSupplier']['komisi']
            );

            // Sanitasi jika ada catatan komisi yang terselip di field produk non-komisi (misal harga_exw)
            foreach ($data['produkSupplier'] as $fld => $val) {
                if (is_string($val) && preg_match('/\bkomisi\b/i', $val)) {
                    if (preg_match('/^(\s*sales\s+)?komisi\b/i', $val)) {
                        $data['produkSupplier'][$fld] = '-';
                    } else {
                        $cleanedVal = trim(preg_replace('/\b(sales\s+)?komisi\s*[^,.]*([,.]|$)/i', '', $val));
                        $data['produkSupplier'][$fld] = ($cleanedVal === '') ? '-' : $cleanedVal;
                    }
                }
            }
        }

        // Kumpulkan kata kunci nama perusahaan untuk menyaring penyebutan nama di riwayat ekspor / teks bebas
        $censorPatterns = [];
        if ($companyName !== '') {
            $censorPatterns[] = $companyName;
            // Ambil nama tanpa prefix badan usaha (PT, CV, UD, dll)
            $cleanComp = trim(preg_replace('/^(PT|CV|UD|PD|Koperasi|Firma)\.?\s+/i', '', $companyName));
            if (mb_strlen($cleanComp) >= 3) {
                $censorPatterns[] = $cleanComp;
            }
            // Jika ada kata tunggal yang unik (mis. "ARVA" dari "CV ARVA Indonesia")
            $words = preg_split('/[\s,.-]+/', $cleanComp, -1, PREG_SPLIT_NO_EMPTY);
            $genericWords = ['indonesia', 'tbk', 'persero', 'group', 'internasional', 'nusantara', 'jaya', 'mandiri', 'sukses', 'abadi', 'makmur', 'utama'];
            foreach ($words as $w) {
                if (mb_strlen($w) >= 3 && !in_array(strtolower($w), $genericWords, true)) {
                    $censorPatterns[] = $w;
                }
            }
        }
        $namaKunci = (string)($data['supplier']['nama_kunci'] ?? ($data['nama_kunci'] ?? ''));
        if ($namaKunci !== '' && mb_strlen($namaKunci) >= 3) {
            $censorPatterns[] = $namaKunci;
        }
        $censorPatterns = array_values(array_unique($censorPatterns));

        // Jika ada sub-array 'supplier'
        if (isset($data['supplier']) && is_array($data['supplier'])) {
            $data['supplier']['kode_produk'] = $kodeProduk;
            unset(
                $data['supplier']['nama_perusahaan'],
                $data['supplier']['nama_kunci'],
                $data['supplier']['nama_kontak'],
                $data['supplier']['email'],
                $data['supplier']['no_hp'],
                $data['supplier']['website'],
                $data['supplier']['instagram'],
                $data['supplier']['legalitas'],
                $data['supplier']['link_dokumen_legalitas'],
                $data['supplier']['link_dokumen_sertifikat'],
                $data['supplier']['link_foto_usaha']
            );

            // Bersihkan sisa teks bebas di profil usaha jika memuat nama perusahaan/merek
            if (!empty($censorPatterns)) {
                foreach ($data['supplier'] as $fld => $val) {
                    if (is_string($val) && $val !== '') {
                        foreach ($censorPatterns as $pat) {
                            if (stripos($val, $pat) !== false) {
                                $val = preg_replace('/\b' . preg_quote($pat, '/') . '\b/i', '', $val);
                                $val = trim(preg_replace('/\s+/', ' ', str_replace(['()', '( )', '[]'], '', $val)));
                                $val = trim($val, " -:,\t\n\r\0\x0B");
                                if (str_starts_with($val, '(') && str_ends_with($val, ')')) {
                                    $val = trim(substr($val, 1, -1));
                                }
                            }
                        }
                        $data['supplier'][$fld] = $val;
                    }
                }
            }
        }

        // Sanitasi teks riwayat ekspor agar tidak membocorkan nama perusahaan / nama kunci
        if (!empty($censorPatterns) && isset($data['histories']) && is_array($data['histories'])) {
            foreach ($data['histories'] as $hIdx => $h) {
                if (is_array($h)) {
                    foreach ($h as $fld => $val) {
                        if (is_string($val) && $val !== '') {
                            foreach ($censorPatterns as $pat) {
                                if (stripos($val, $pat) !== false) {
                                    $val = preg_replace('/\b' . preg_quote($pat, '/') . '\b/i', '', $val);
                                    $val = trim(preg_replace('/\s+/', ' ', str_replace(['()', '( )', '[]'], '', $val)));
                                    $val = trim($val, " -:,\t\n\r\0\x0B");
                                    if (str_starts_with($val, '(') && str_ends_with($val, ')')) {
                                        $val = trim(substr($val, 1, -1));
                                    }
                                    if ($val === '') {
                                        $val = $kodeProduk;
                                    }
                                }
                            }
                            $data['histories'][$hIdx][$fld] = $val;
                        }
                    }
                }
            }
        }

        // Bersihkan jika nama_produk_en ada di root dan membocorkan nama perusahaan
        if ($companyName !== '' && isset($data['nama_produk_en']) && trim((string)$data['nama_produk_en']) === trim($companyName)) {
            $data['nama_produk_en'] = $kodeProduk;
        }

        // Sanitasi jika ada catatan komisi yang terselip di field produk non-komisi (misal harga_exw)
        foreach ($data as $fld => $val) {
            if ($fld !== 'komisi' && is_string($val) && preg_match('/\bkomisi\b/i', $val)) {
                if (preg_match('/^(\s*sales\s+)?komisi\b/i', $val)) {
                    $data[$fld] = '-';
                } else {
                    $cleanedVal = trim(preg_replace('/\b(sales\s+)?komisi\s*[^,.]*([,.]|$)/i', '', $val));
                    $data[$fld] = ($cleanedVal === '') ? '-' : $cleanedVal;
                }
            }
        }

        // Bersihkan forbidden fields di root array
        unset(
            $data['nama_perusahaan'],
            $data['nama_kunci'],
            $data['nama_kontak'],
            $data['email'],
            $data['no_hp'],
            $data['website'],
            $data['instagram'],
            $data['legalitas'],
            $data['link_dokumen_legalitas'],
            $data['link_dokumen_sertifikat'],
            $data['link_foto_usaha'],
            $data['komisi']
        );

        return $data;
    }
}

