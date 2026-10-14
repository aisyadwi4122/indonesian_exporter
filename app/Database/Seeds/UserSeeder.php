<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name'       => 'Administrator',
                'email'      => 'admin@example.com',
                'password'   => password_hash('admin123', PASSWORD_BCRYPT),
                'role'       => 'admin',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Sales Representative',
                'email'      => 'sales@example.com',
                'password'   => password_hash('sales123', PASSWORD_BCRYPT),
                'role'       => 'sales',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert or ignore / update if email exists
        foreach ($users as $u) {
            $existing = $this->db->table('users')->where('email', $u['email'])->get()->getRowArray();
            if ($existing) {
                $this->db->table('users')->where('id', $existing['id'])->update($u);
            } else {
                $this->db->table('users')->insert($u);
            }
        }
    }
}
