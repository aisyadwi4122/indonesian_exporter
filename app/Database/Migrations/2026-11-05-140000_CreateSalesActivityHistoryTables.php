<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesActivityHistoryTables extends Migration
{
    public function up()
    {
        // 1. Pastikan tabel search_history memiliki index pada user_id
        if ($this->db->tableExists('search_history')) {
            $indexes = $this->db->getIndexData('search_history');
            $hasUserIndex = false;
            foreach ($indexes as $idx) {
                if (in_array('user_id', $idx->fields, true)) {
                    $hasUserIndex = true;
                    break;
                }
            }
            if (!$hasUserIndex) {
                $this->db->query("ALTER TABLE search_history ADD INDEX idx_search_user_id (user_id)");
            }
        }

        // 2. Tabel login_history
        if (!$this->db->tableExists('login_history')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'ip_address' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 45,
                    'null'       => false,
                ],
                'user_agent' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id');
            $this->forge->addKey('created_at');
            $this->forge->createTable('login_history', true);
        }

        // 3. Tabel detail_click_history
        if (!$this->db->tableExists('detail_click_history')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'product_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'product_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id');
            $this->forge->addKey('product_id');
            $this->forge->addKey('created_at');
            $this->forge->createTable('detail_click_history', true);
        }

        // 4. Tabel commission_click_history
        if (!$this->db->tableExists('commission_click_history')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'product_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'product_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id');
            $this->forge->addKey('product_id');
            $this->forge->addKey('created_at');
            $this->forge->createTable('commission_click_history', true);
        }

        // 5. Tabel pdf_history
        if (!$this->db->tableExists('pdf_history')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'pdf_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => false,
                ],
                'product_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'product_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id');
            $this->forge->addKey('created_at');
            $this->forge->createTable('pdf_history', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('pdf_history', true);
        $this->forge->dropTable('commission_click_history', true);
        $this->forge->dropTable('detail_click_history', true);
        $this->forge->dropTable('login_history', true);
    }
}
