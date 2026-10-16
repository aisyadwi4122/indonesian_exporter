<?php

namespace App\Filters;

use App\Models\SupplierUserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SupplierAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if ($session->get('supplier_logged_in') !== true) {
            $session->setFlashdata('error', 'Silakan login terlebih dahulu');
            return redirect()->to(base_url('supplier/login'));
        }

        // Pengecekan status is_active akun dari database setiap request (BONUS)
        $userId = $session->get('supplier_user_id');
        if (!empty($userId)) {
            $supplierUserModel = new SupplierUserModel();
            $user = $supplierUserModel->find($userId);

            if (!$user || (int)($user['is_active'] ?? 0) !== 1) {
                $session->remove([
                    'supplier_logged_in',
                    'supplier_user_id',
                    'supplier_id',
                    'supplier_username',
                    'supplier_name',
                ]);

                $session->setFlashdata('error', 'Akun Anda tidak aktif');
                return redirect()->to(base_url('supplier/login'));
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi setelah request
    }
}
