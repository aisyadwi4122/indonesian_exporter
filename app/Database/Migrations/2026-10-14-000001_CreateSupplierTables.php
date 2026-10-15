<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSupplierTables extends Migration
{
    public function up()
    {
        $tableAttributes = [
            'ENGINE'   => 'InnoDB',
            'CHARSET'  => 'utf8mb4',
            'COLLATE'  => 'utf8mb4_general_ci',
        ];

        // --------------------------------------------------------------------
        // 1. tb_supplier (Data Perusahaan)
        // --------------------------------------------------------------------
        $this->forge->addField([
            'id_supplier' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_perusahaan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'nama_kunci' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'nama_kontak' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'no_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
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
            'domisili_usaha' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'lokasi_pabrik_gudang' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'produk_dipasarkan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'jenis_perusahaan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'kapasitas_produksi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'jumlah_pegawai' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'frekuensi_ekspor' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'pameran_internasional' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'keunggulan_produk' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'link_foto_usaha' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'legalitas' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'link_dokumen_legalitas' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sertifikasi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'link_dokumen_sertifikat' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'pernah_ekspor' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'kategori_ekspor' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'ringkasan_ekspor_tanggal' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'ringkasan_ekspor_produk' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ringkasan_ekspor_kapasitas' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'ringkasan_ekspor_negara' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'ringkasan_ekspor_nilai' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sumber_tahun' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
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
        $this->forge->addPrimaryKey('id_supplier');
        $this->forge->addUniqueKey('nama_kunci');
        $this->forge->createTable('tb_supplier', true, $tableAttributes);

        // --------------------------------------------------------------------
        // 2. tb_produk_supplier (Data Produk)
        // --------------------------------------------------------------------
        $this->forge->addField([
            'id_produk_supplier' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_supplier' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'nama_produk' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'spesifikasi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'kapasitas_produksi' => [
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
            'lokasi_barang' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'foto_1' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'foto_2' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'foto_3' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'foto_4' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'foto_5' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'moq' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'harga_exw' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'komisi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'sudah_ekspor' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'views' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
                'default'  => 0,
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
        $this->forge->addPrimaryKey('id_produk_supplier');
        $this->forge->addKey('nama_produk');
        $this->forge->addKey('id_supplier');
        $this->forge->addForeignKey('id_supplier', 'tb_supplier', 'id_supplier', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tb_produk_supplier', true, $tableAttributes);

        // --------------------------------------------------------------------
        // 3. tb_history_export_supplier (Data History Ekspor)
        // --------------------------------------------------------------------
        $this->forge->addField([
            'id_history' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_supplier' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'tahun' => [
                'type'     => 'SMALLINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'produk' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'negara_tujuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'jenis_ekspor' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'kuantitas' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nilai_ekspor_usd' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'nilai_ekspor_asli' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'catatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id_history');
        $this->forge->addKey(['id_supplier', 'tahun']);
        $this->forge->addForeignKey('id_supplier', 'tb_supplier', 'id_supplier', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tb_history_export_supplier', true, $tableAttributes);
    }

    public function down()
    {
        $this->forge->dropTable('tb_history_export_supplier', true);
        $this->forge->dropTable('tb_produk_supplier', true);
        $this->forge->dropTable('tb_supplier', true);
    }
}
