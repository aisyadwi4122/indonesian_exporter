<?php

namespace App\Commands;

use App\Models\SupplierModel;
use App\Models\SupplierUserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateSupplierUser extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Supplier';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'supplier:create-user';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Membuat akun pengguna supplier baru.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'supplier:create-user <username> <password> [id_supplier] [nama]';

    /**
     * The Command's Arguments
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'username'    => 'Username unik untuk supplier',
        'password'    => 'Password minimal 8 karakter',
        'id_supplier' => 'ID supplier pada tb_supplier (opsional)',
        'nama'        => 'Nama lengkap pengguna supplier (opsional)',
    ];

    /**
     * Actually run the command.
     *
     * @param array<int|string, string|null> $params
     */
    public function run(array $params)
    {
        $username = isset($params[0]) ? trim((string)$params[0]) : null;
        $password = isset($params[1]) ? (string)$params[1] : null;
        $rawIdSupplier = isset($params[2]) ? trim((string)$params[2]) : null;
        $name = isset($params[3]) ? trim(implode(' ', array_slice($params, 3))) : null;

        if (empty($username) || empty($password)) {
            CLI::error('Penggunaan: php spark supplier:create-user <username> <password> [id_supplier] [nama]');
            return EXIT_USER_INPUT;
        }

        // 1. Validasi password minimal 8 karakter
        if (strlen($password) < 8) {
            CLI::error('Password minimal 8 karakter.');
            return EXIT_USER_INPUT;
        }

        // 2. Validasi username unik di tb_supplier_user
        $userModel = new SupplierUserModel();
        $existing = $userModel->where('username', $username)->first();
        if ($existing) {
            CLI::error("Username '{$username}' sudah digunakan.");
            return EXIT_USER_INPUT;
        }

        // 3. Validasi id_supplier (jika diisi harus ada di tb_supplier)
        $idSupplier = null;
        if (!empty($rawIdSupplier)) {
            $supplierModel = new SupplierModel();
            $supplier = $supplierModel->find((int)$rawIdSupplier);
            if (!$supplier) {
                CLI::error("id_supplier '{$rawIdSupplier}' tidak ditemukan di tb_supplier.");
                return EXIT_USER_INPUT;
            }
            $idSupplier = (int)$rawIdSupplier;

            if (empty($name)) {
                $name = !empty($supplier['nama_kontak']) ? $supplier['nama_kontak'] : $supplier['nama_perusahaan'];
            }
        }

        if (empty($name)) {
            $name = $username;
        }

        // 4. Simpan password dengan password_hash() tanpa menampilkan password di output
        $userModel->insert([
            'id_supplier' => $idSupplier,
            'username'    => $username,
            'password'    => password_hash($password, PASSWORD_DEFAULT),
            'name'        => $name,
            'is_active'   => 1,
        ]);

        CLI::write('Akun dibuat.', 'green');

        return EXIT_SUCCESS;
    }
}
