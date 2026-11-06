<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Base controller untuk seluruh modul admin yang membutuhkan autentikasi admin.
 * Memeriksa sesi 'logged_in' yang diset oleh LoginController.
 */
class AdminAuthBase extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * Memastikan sesi admin valid pada setiap pemanggilan method.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    protected function checkAuth()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to(base_url('login'));
        }
        if (session()->get('role') === 'sales') {
            return redirect()->to(base_url('sales/dashboard'))->with('error', 'Akses ditolak. Halaman hanya untuk admin.');
        }
        return null;
    }
}
