<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropExportColumnsFromSupplierTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tb_supplier')) {
            $columnsToDrop = [
                'pernah_ekspor',
                'frekuensi_ekspor',
                'kategori_ekspor',
                'pameran_internasional',
                'ringkasan_ekspor_tanggal',
                'ringkasan_ekspor_produk',
                'ringkasan_ekspor_kapasitas',
                'ringkasan_ekspor_negara',
                'ringkasan_ekspor_nilai',
            ];

            foreach ($columnsToDrop as $col) {
                if ($this->db->fieldExists($col, 'tb_supplier')) {
                    $this->forge->dropColumn('tb_supplier', $col);
                }
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('tb_supplier')) {
            $fieldsToAdd = [
                'pernah_ekspor' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'frekuensi_ekspor' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'kategori_ekspor' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'pameran_internasional' => [
                    'type' => 'TEXT',
                    'null' => true,
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
            ];

            foreach ($fieldsToAdd as $col => $def) {
                if (!$this->db->fieldExists($col, 'tb_supplier')) {
                    $this->forge->addColumn('tb_supplier', [$col => $def]);
                }
            }
        }
    }
}

