<?php

namespace App\Controllers\Sales;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class ProductController extends BaseController
{
    protected ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    /**
     * Menampilkan katalog produk yang berstatus published
     * Route: GET /sales/products
     */
    public function index()
    {
        $products = $this->productModel->getPublishedProducts();

        $data = [
            'title'    => 'Katalog Produk Sales - Indonesian Exporter',
            'products' => $products,
        ];

        return view('sales/products/index', $data);
    }

    /**
     * Menampilkan detail 1 produk (READ-ONLY)
     * Route: GET /sales/products/detail/(:num)
     */
    public function detail($id = null)
    {
        if ($id === null) {
            session()->setFlashdata('error', 'Produk tidak ditemukan');
            return redirect()->to(base_url('sales/products'));
        }

        $product = $this->productModel->getDetail($id);

        if (!$product) {
            session()->setFlashdata('error', 'Produk tidak ditemukan');
            return redirect()->to(base_url('sales/products'));
        }

        $data = [
            'title'   => 'Detail Produk - ' . ($product['nama_produk'] ?? ''),
            'product' => $product,
        ];

        return view('sales/products/detail', $data);
    }

    /**
     * Generate dan download PDF detail produk
     * Route: GET /sales/products/pdf/(:num)
     */
    public function downloadPdf($id = null)
    {
        if ($id === null) {
            session()->setFlashdata('error', 'Produk tidak ditemukan');
            return redirect()->to(base_url('sales/products'));
        }

        $product = $this->productModel->getDetail($id);

        if (!$product) {
            session()->setFlashdata('error', 'Produk tidak ditemukan');
            return redirect()->to(base_url('sales/products'));
        }

        $data = [
            'product'    => $product,
            'printed_at' => date('d-m-Y H:i'),
        ];

        $html = view('sales/products/pdf', $data);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'sans-serif');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $product['nama_produk']);
        $filename = 'produk-' . trim($safeName, '-') . '.pdf';

        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }
}
