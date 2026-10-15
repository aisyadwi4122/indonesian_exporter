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

        // Verifikasi sesi admin saat controller diinisialisasi
        if (!session()->get('logged_in')) {
            $loginUrl = base_url('login');
            if (!headers_sent()) {
                header('Location: ' . $loginUrl);
                exit;
            }
        }
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
        return null;
    }
}
