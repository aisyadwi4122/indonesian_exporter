<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipeAksesToAkunSales extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('tb_sales')) {
            if (!$this->db->fieldExists('tipe_akses', 'tb_sales')) {
                $this->forge->addColumn('tb_sales', [
                    'tipe_akses' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 10,
                        'default'    => 'internal',
                        'null'       => false,
                        'after'      => 'status',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('tb_sales')) {
            if ($this->db->fieldExists('tipe_akses', 'tb_sales')) {
                $this->forge->dropColumn('tb_sales', 'tipe_akses');
            }
        }
    }
}
