<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\SupplierProductModel;
use App\Models\SupplierModel;
use App\Models\SupplierExportHistoryModel;
use App\Models\ProductModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;
use Dompdf\Options;

class Produk extends BaseController
{
    public function index()
    {
        $supplierProductModel = new SupplierProductModel();
        $q = trim((string)($this->request->getGet('q') ?? ''));

        $builder = $supplierProductModel->select('
            tb_produk_supplier.*,
            tb_produk_supplier.id_produk_supplier as id_produk,
            tb_produk_supplier.nama_produk as nama_produk_id,
            tb_supplier.nama_perusahaan,
            tb_supplier.jenis_perusahaan,
            tb_supplier.domisili_usaha
        ')->join('tb_supplier', 'tb_supplier.id_supplier = tb_produk_supplier.id_supplier', 'left');

        if ($q !== '') {
            $builder->groupStart()
                ->like('tb_produk_supplier.nama_produk', $q)
                ->orLike('tb_supplier.nama_perusahaan', $q)
                ->orLike('tb_produk_supplier.spesifikasi', $q)
                ->orLike('tb_produk_supplier.lokasi_barang', $q)
                ->orLike('tb_supplier.domisili_usaha', $q)
            ->groupEnd();
        }

        $products = $builder->orderBy('tb_produk_supplier.id_produk_supplier', 'DESC')->findAll();

        $data = [
            'title'      => 'Produk Sales - Indonesian Exporter',
            'page_title' => 'Produk',
            'activeMenu' => 'produk',
            'products'   => $products,
            'q'          => $q,
        ];

        return view('sales/produk/index', $data);
    }

    public function detail($id = null)
    {
        if ($id === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $detailData = $this->buildDetailData((int)$id);
        if (empty($detailData)) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan');
        }

        $data = array_merge([
            'title'      => ($detailData['product']['nama_produk_id'] ?? 'Detail Produk') . ' - Area Sales',
            'page_title' => 'Detail Produk',
            'activeMenu' => 'produk',
        ], $detailData);

        return view('sales/produk/detail', $data);
    }

    public function detailPdf($id = null)
    {
        if ($id === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $detailData = $this->buildDetailData((int)$id);
        if (empty($detailData)) {
            throw PageNotFoundException::forPageNotFound('Produk tidak ditemukan');
        }

        $rawSlug = $detailData['product']['nama_produk_id'] ?? ('item-' . $id);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $rawSlug), '-'));
        if (empty($slug)) {
            $slug = (string)$id;
        }

        $filename = 'produk-' . $slug . '-' . date('Ymd') . '.pdf';
        $this->renderPdf([$detailData], $filename);
    }

    public function exportPdf()
    {
        $supplierProductModel = new SupplierProductModel();
        $q = trim((string)($this->request->getGet('q') ?? ''));

        $builder = $supplierProductModel->select('
            tb_produk_supplier.id_produk_supplier
        ')->join('tb_supplier', 'tb_supplier.id_supplier = tb_produk_supplier.id_supplier', 'left');

        if ($q !== '') {
            $builder->groupStart()
                ->like('tb_produk_supplier.nama_produk', $q)
                ->orLike('tb_supplier.nama_perusahaan', $q)
                ->orLike('tb_produk_supplier.spesifikasi', $q)
                ->orLike('tb_produk_supplier.lokasi_barang', $q)
                ->orLike('tb_supplier.domisili_usaha', $q)
            ->groupEnd();
        }

        $rows = $builder->orderBy('tb_produk_supplier.id_produk_supplier', 'DESC')->findAll();
        $count = count($rows);

        $redirectUrl = base_url('sales/produk') . ($q !== '' ? '?q=' . urlencode($q) : '');

        if ($count > 50) {
            return redirect()->to($redirectUrl)->with('error', 'Persempit pencarian (maksimal 50 produk per PDF)');
        }

        if ($count === 0) {
            // Fallback jika menggunakan tb_produk
            $productModel = new ProductModel();
            $pBuilder = $productModel->select('id_produk');
            if ($q !== '') {
                $pBuilder->like('nama_produk_id', $q)->orLike('nama_produk_en', $q);
            }
            $pRows = $pBuilder->findAll();
            $pCount = count($pRows);

            if ($pCount > 50) {
                return redirect()->to($redirectUrl)->with('error', 'Persempit pencarian (maksimal 50 produk per PDF)');
            }

            if ($pCount === 0) {
                return redirect()->to($redirectUrl)->with('error', 'Tidak ada produk yang ditemukan untuk diekspor');
            }

            $rows = array_map(function ($r) {
                return ['id_produk_supplier' => $r['id_produk']];
            }, $pRows);
        }

        $items = [];
        foreach ($rows as $row) {
            $data = $this->buildDetailData((int)$row['id_produk_supplier']);
            if (!empty($data)) {
                $items[] = $data;
            }
        }

        if (empty($items)) {
            return redirect()->to($redirectUrl)->with('error', 'Tidak ada produk yang dapat diekspor');
        }

        $filename = 'katalog-produk-' . date('Ymd') . '.pdf';
        $this->renderPdf($items, $filename);
    }

    private function buildDetailData(int $id): array
    {
        $supplierProductModel = new SupplierProductModel();
        $produkSupplier = $supplierProductModel->find($id);

        $supplierModel = new SupplierModel();
        $historyModel = new SupplierExportHistoryModel();

        if ($produkSupplier) {
            $supplier = $supplierModel->find($produkSupplier['id_supplier']);
            $histories = $historyModel->where('id_supplier', $produkSupplier['id_supplier'])
                ->orderBy('tahun', 'DESC')
                ->orderBy('nilai_ekspor_usd', 'DESC')
                ->findAll();

            $years = [];
            $totalNilai = 0.0;
            foreach ($histories as $h) {
                if (!empty($h['tahun']) && !in_array((int)$h['tahun'], $years, true)) {
                    $years[] = (int)$h['tahun'];
                }
                if (isset($h['nilai_ekspor_usd']) && is_numeric($h['nilai_ekspor_usd'])) {
                    $totalNilai += (float)$h['nilai_ekspor_usd'];
                }
            }
            rsort($years);

            $defaultImg = 'assets/img/logo.png';
            $foto = !empty($produkSupplier['foto_1']) ? $produkSupplier['foto_1'] : $defaultImg;

            $product = [
                'id_produk'           => $produkSupplier['id_produk_supplier'],
                'nama_produk_id'      => $produkSupplier['nama_produk'],
                'nama_produk_en'      => $supplier['nama_perusahaan'] ?? '',
                'nama_kategori_id'    => 'Produk Supplier',
                'nama_kategori_en'    => 'Supplier Product',
                'foto_produk'         => $foto,
                'deskripsi_produk_id' => $produkSupplier['spesifikasi'] ?? '-',
                'deskripsi_produk_en' => $produkSupplier['spesifikasi'] ?? '-',
            ];
        } else {
            // Fallback jika id berasal dari tb_produk
            $productModel = new ProductModel();
            $product = $productModel->select('tb_produk.*, tb_kategori_produk.nama_kategori_id, tb_kategori_produk.nama_kategori_en')
                ->join('tb_kategori_produk', 'tb_kategori_produk.id_kategori_produk = tb_produk.id_kategori_produk', 'left')
                ->where('tb_produk.id_produk', $id)
                ->first();

            if (!$product) {
                return [];
            }

            $produkSupplier = null;   // array|null: data produk dari tb_produk_supplier
            $supplier       = null;   // array|null: data perusahaan dari tb_supplier
            $histories      = [];     // array: baris tb_history_export_supplier
            $years          = [];     // array tahun yang tersedia
            $totalNilai     = 0.0;
        }

        return [
            'product'        => $product,
            'produkSupplier' => $produkSupplier,
            'supplier'       => $supplier,
            'histories'      => $histories,
            'years'          => $years,
            'totalNilai'     => $totalNilai,
        ];
    }

    private function renderPdf(array $items, string $filename)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        // Siapkan logo aplikasi sebagai data URI
        $logoPath = FCPATH . 'assets/img/logo.png';
        $logoData = null;
        if (is_file($logoPath)) {
            $rawLogo = @file_get_contents($logoPath);
            if ($rawLogo !== false) {
                $logoData = 'data:image/png;base64,' . base64_encode($rawLogo);
            }
        }

        // Siapkan base64 gambar dan sanitasi HTML deskripsi per item produk
        foreach ($items as &$item) {
            $fotoFile = $item['product']['foto_produk'] ?? null;
            $item['imageData'] = $this->pdfImageData($fotoFile);
            $item['deskripsi_id_sanitized'] = $this->sanitizeHtml($item['product']['deskripsi_produk_id'] ?? '');
            $item['deskripsi_en_sanitized'] = $this->sanitizeHtml($item['product']['deskripsi_produk_en'] ?? '');
        }
        unset($item);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', FCPATH);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');

        $html = view('sales/produk/pdf_template', [
            'items'    => $items,
            'tanggal'  => date('d F Y'),
            'logoData' => $logoData,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->render();

        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
        $w = $canvas->get_width();
        $h = $canvas->get_height();
        $canvas->page_text($w / 2 - 50, $h - 28, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, 9, [0.4, 0.45, 0.5]);

        $dompdf->stream($filename, ['Attachment' => false]);
        exit;
    }

    private function pdfImageData(?string $file): ?string
    {
        if ($file === null || trim($file) === '') {
            return null;
        }

        $file = trim($file);

        // Cek path sesuai spesifikasi: FCPATH . 'assets/img/produk/' . $file
        $path = FCPATH . 'assets/img/produk/' . $file;
        if (!is_file($path)) {
            // Fallback jika $file sudah memiliki prefix 'assets/' atau path relatif dari FCPATH
            if (is_file(FCPATH . $file)) {
                $path = FCPATH . $file;
            } elseif (is_file(FCPATH . ltrim($file, '/\\'))) {
                $path = FCPATH . ltrim($file, '/\\');
            } else {
                return null;
            }
        }

        try {
            $raw = @file_get_contents($path);
            if ($raw === false || empty($raw)) {
                return null;
            }

            $src = @imagecreatefromstring($raw);
            if (!$src) {
                return null;
            }

            $origWidth = imagesx($src);
            $origHeight = imagesy($src);

            if ($origWidth <= 0 || $origHeight <= 0) {
                imagedestroy($src);
                return null;
            }

            $maxWidth = 800;
            if ($origWidth > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int)round(($origHeight / $origWidth) * $maxWidth);
            } else {
                $newWidth = $origWidth;
                $newHeight = $origHeight;
            }

            $dst = imagecreatetruecolor($newWidth, $newHeight);
            if (!$dst) {
                imagedestroy($src);
                return null;
            }

            // Latar putih untuk transparansi PNG/WebP/GIF
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

            ob_start();
            imagejpeg($dst, null, 80);
            $jpegData = ob_get_clean();

            imagedestroy($src);
            imagedestroy($dst);

            if (!$jpegData) {
                return null;
            }

            return 'data:image/jpeg;base64,' . base64_encode($jpegData);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function sanitizeHtml(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $whitelist = '<p><br><ul><ol><li><strong><b><em><i><h3><h4><table><thead><tbody><tr><th><td>';
        $cleaned = strip_tags($html, $whitelist);

        // Hapus atribut style, class, dan on*
        $cleaned = preg_replace('/\s*(?:style|class|on[a-z]+)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $cleaned);

        return trim($cleaned ?? '');
    }
}
