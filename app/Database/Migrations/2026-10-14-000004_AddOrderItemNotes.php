<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderItemNotes extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('catatan', 'order_items')) {
            $this->forge->addColumn('order_items', [
                'catatan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 500,
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('catatan', 'order_items')) {
            $this->forge->dropColumn('order_items', 'catatan');
        }
    }
}
