<?php

namespace App\Controllers\Supplier;

use App\Controllers\BaseController;

class SupplierBase extends BaseController
{
    /**
     * Memastikan akun supplier yang sedang login sudah memiliki data perusahaan.
     * Jika belum, arahkan ke supplier/perusahaan dengan flash message.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    public function requireCompany()
    {
        $supplierId = session()->get('supplier_id');

        if (empty($supplierId)) {
            session()->setFlashdata('error', 'Lengkapi profil perusahaan Anda terlebih dahulu.');
            return redirect()->to(base_url('supplier/perusahaan'));
        }

        return null;
    }
}
