<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SalesAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Cek apakah sesi role sales aktif
        if (!$session->get('sales_logged_in') || $session->get('role') !== 'sales') {
            $session->setFlashdata('error', 'Silakan login terlebih dahulu');
            return redirect()->to(base_url('sales'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi setelah request
    }
}
