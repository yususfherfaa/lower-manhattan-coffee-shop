<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductDescriptions extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('deskripsi', 'products')) {
            $this->forge->addColumn('products', [
                'deskripsi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('deskripsi', 'products')) {
            $this->forge->dropColumn('products', 'deskripsi');
        }
    }
}
