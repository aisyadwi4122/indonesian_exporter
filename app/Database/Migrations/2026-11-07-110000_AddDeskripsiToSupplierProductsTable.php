<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeskripsiToSupplierProductsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tb_produk_supplier') && !$this->db->fieldExists('deskripsi', 'tb_produk_supplier')) {
            $this->forge->addColumn('tb_produk_supplier', [
                'deskripsi' => [
                    'type'  => 'TEXT',
                    'null'  => true,
                    'after' => 'nama_produk',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('tb_produk_supplier') && $this->db->fieldExists('deskripsi', 'tb_produk_supplier')) {
            $this->forge->dropColumn('tb_produk_supplier', 'deskripsi');
        }
    }
}
