<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesProdukTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_produk' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'id_kategori_produk' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'nama_produk_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'nama_produk_en' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'deskripsi_produk_id' => [
                'type' => 'TEXT',
            ],
            'deskripsi_produk_en' => [
                'type' => 'TEXT',
            ],
            'foto_produk' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'alt_produk_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'default'    => '',
            ],
            'alt_produk_en' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'default'    => '',
            ],
            'title_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => '',
            ],
            'title_en' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => '',
            ],
            'meta_desc_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => '',
            ],
            'meta_desc_en' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'default'    => '',
            ],
            'slug_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'default'    => '',
            ],
            'slug_en' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'default'    => '',
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
        $this->forge->addKey('id_produk', true);
        $this->forge->createTable('tb_sales_produk', true);
    }

    public function down()
    {
        $this->forge->dropTable('tb_sales_produk', true);
    }
}
