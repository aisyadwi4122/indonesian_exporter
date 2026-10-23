<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEcpAndSupplierTables extends Migration
{
    public function up()
    {
        $tableAttributes = [
            'ENGINE'   => 'InnoDB',
            'CHARSET'  => 'utf8mb4',
            'COLLATE'  => 'utf8mb4_general_ci',
        ];

        // 1. Tabel data ekspor ECP (2024 & 2025)
        $this->forge->addField([
            'id_ecp' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tahun' => [
                'type'     => 'SMALLINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'nama_peserta' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nama_perusahaan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'no_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'domisili' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'wilayah_ecp' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'kategori_produk' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'produk_dipasarkan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'merk_dagang' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'jenis_perusahaan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'kapasitas_produksi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'omset' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'website' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'instagram' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'produk_ekspor' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'negara_tujuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'jenis_ekspor' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'kuantitas_ekspor' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nilai_ekspor_usd' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'nilai_ekspor_raw' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
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
        $this->forge->addPrimaryKey('id_ecp');
        $this->forge->addKey('tahun');
        $this->forge->addKey('nama_perusahaan');
        $this->forge->createTable('tb_ecp_ekspor', true, $tableAttributes);

        // 2. Tabel data produk & supplier (dari List Produk Supplier)
        $this->forge->addField([
            'id_supplier_produk' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'timestamp_form' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'email_pengirim' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'nama_pic' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'nama_perusahaan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'no_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'email_perusahaan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'website' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'instagram' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'lokasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'jenis_usaha' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'nama_produk' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'spesifikasi_kapasitas' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'legalitas' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sertifikasi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'foto_produk' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'pernah_ekspor' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'harga_moq' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'komisi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
        $this->forge->addPrimaryKey('id_supplier_produk');
        $this->forge->addKey('nama_perusahaan');
        $this->forge->createTable('tb_supplier_produk', true, $tableAttributes);
    }

    public function down()
    {
        $this->forge->dropTable('tb_supplier_produk', true);
        $this->forge->dropTable('tb_ecp_ekspor', true);
    }
}
