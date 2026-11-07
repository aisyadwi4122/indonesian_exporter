<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddViewModeToSalesActivityTables extends Migration
{
    public function up()
    {
        // 1. search_history
        if ($this->db->tableExists('search_history') && !$this->db->fieldExists('view_mode', 'search_history')) {
            $this->forge->addColumn('search_history', [
                'view_mode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'internal',
                    'null'       => false,
                    'after'      => 'result_count',
                ],
            ]);
            $this->db->query("ALTER TABLE search_history ADD INDEX idx_search_view_mode (view_mode)");
        }

        // 2. detail_click_history
        if ($this->db->tableExists('detail_click_history') && !$this->db->fieldExists('view_mode', 'detail_click_history')) {
            $this->forge->addColumn('detail_click_history', [
                'view_mode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'internal',
                    'null'       => false,
                    'after'      => 'product_name',
                ],
            ]);
            $this->db->query("ALTER TABLE detail_click_history ADD INDEX idx_detail_view_mode (view_mode)");
        }

        // 3. commission_click_history
        if ($this->db->tableExists('commission_click_history') && !$this->db->fieldExists('view_mode', 'commission_click_history')) {
            $this->forge->addColumn('commission_click_history', [
                'view_mode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'internal',
                    'null'       => false,
                    'after'      => 'product_name',
                ],
            ]);
            $this->db->query("ALTER TABLE commission_click_history ADD INDEX idx_comm_view_mode (view_mode)");
        }

        // 4. pdf_history
        if ($this->db->tableExists('pdf_history') && !$this->db->fieldExists('view_mode', 'pdf_history')) {
            $this->forge->addColumn('pdf_history', [
                'view_mode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'internal',
                    'null'       => false,
                    'after'      => 'product_name',
                ],
            ]);
            $this->db->query("ALTER TABLE pdf_history ADD INDEX idx_pdf_view_mode (view_mode)");
        }
    }

    public function down()
    {
        if ($this->db->tableExists('search_history') && $this->db->fieldExists('view_mode', 'search_history')) {
            $this->forge->dropColumn('search_history', 'view_mode');
        }
        if ($this->db->tableExists('detail_click_history') && $this->db->fieldExists('view_mode', 'detail_click_history')) {
            $this->forge->dropColumn('detail_click_history', 'view_mode');
        }
        if ($this->db->tableExists('commission_click_history') && $this->db->fieldExists('view_mode', 'commission_click_history')) {
            $this->forge->dropColumn('commission_click_history', 'view_mode');
        }
        if ($this->db->tableExists('pdf_history') && $this->db->fieldExists('view_mode', 'pdf_history')) {
            $this->forge->dropColumn('pdf_history', 'view_mode');
        }
    }
}
