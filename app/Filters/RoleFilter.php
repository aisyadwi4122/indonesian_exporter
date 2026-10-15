<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $userRole = $session->get('role');

        // Jika filter diberi parameter role (misal: role:sales atau role:admin,sales)
        if (!empty($arguments)) {
            if (!$userRole || !in_array($userRole, $arguments, true)) {
                $session->setFlashdata('error', 'Anda tidak memiliki hak akses untuk halaman ini.');
                return redirect()->to('/login');
            }
        } else {
            // Default cek apakah ada role yang tersimpan di sesi
            if (!$userRole) {
                $session->setFlashdata('error', 'Silakan login terlebih dahulu.');
                return redirect()->to('/login');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}
