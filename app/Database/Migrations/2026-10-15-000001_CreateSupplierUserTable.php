<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSupplierUserTable extends Migration
{
    public function up()
    {
        $tableAttributes = [
            'ENGINE'   => 'InnoDB',
            'CHARSET'  => 'utf8mb4',
            'COLLATE'  => 'utf8mb4_general_ci',
        ];

        $this->forge->addField([
            'id_supplier_user' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_supplier' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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

        $this->forge->addPrimaryKey('id_supplier_user');
        $this->forge->addForeignKey('id_supplier', 'tb_supplier', 'id_supplier', 'CASCADE', 'SET NULL');
        $this->forge->createTable('tb_supplier_user', true, $tableAttributes);
    }

    public function down()
    {
        $this->forge->dropTable('tb_supplier_user', true);
    }
}
