<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SalesSeeder extends Seeder
{
    public function run()
    {
        $salesData = [
            [
                'nama_sales' => 'Sales Representative',
                'username'   => 'sales',
                'password'   => password_hash('sales123', PASSWORD_DEFAULT),
                'email'      => 'sales@indonesian-exporter.com',
                'no_hp'      => '081234567890',
                'wilayah'    => 'Indonesia',
                'status'     => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($salesData as $sales) {
            $existing = $this->db->table('tb_sales')->where('username', $sales['username'])->get()->getRowArray();
            if ($existing) {
                $this->db->table('tb_sales')->where('id_sales', $existing['id_sales'])->update($sales);
            } else {
                $this->db->table('tb_sales')->insert($sales);
            }
        }
    }
}
