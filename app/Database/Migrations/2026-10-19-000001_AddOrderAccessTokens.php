<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderAccessTokens extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('token', 'orders')) {
            $this->forge->addColumn('orders', [
                'token' => [
                    'type' => 'CHAR',
                    'constraint' => 16,
                    'null' => true,
                    'after' => 'id_order',
                ],
            ]);
        }

        $orders = $this->db->table('orders')->select('id_order')->where('token', null)->get()->getResultArray();
        foreach ($orders as $order) {
            $this->db->table('orders')
                ->where('id_order', $order['id_order'])
                ->update(['token' => bin2hex(random_bytes(8))]);
        }

        $this->forge->modifyColumn('orders', [
            'token' => [
                'name' => 'token',
                'type' => 'CHAR',
                'constraint' => 16,
                'null' => false,
            ],
        ]);
        $this->db->query('ALTER TABLE `orders` ADD UNIQUE KEY `uq_orders_token` (`token`)');
    }

    public function down()
    {
        if ($this->db->fieldExists('token', 'orders')) {
            $this->forge->dropColumn('orders', 'token');
        }
    }
}
