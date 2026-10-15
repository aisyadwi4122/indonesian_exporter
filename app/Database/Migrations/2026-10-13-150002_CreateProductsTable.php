<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_produk' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'gambar_utama' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'domisili_usaha' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'jenis_perusahaan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'jumlah_pegawai' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'kapasitas_produksi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'produk_dipasarkan' => [
                'type' => 'TEXT',
            ],
            'produk_siap_ekspor' => [
                'type' => 'TEXT',
            ],
            'frekuensi_ekspor' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'published'],
                'default'    => 'draft',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('products', true);
    }

    public function down()
    {
        $this->forge->dropTable('products', true);
    }
}
