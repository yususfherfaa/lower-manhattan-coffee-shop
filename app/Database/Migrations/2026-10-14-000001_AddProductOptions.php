<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class AddProductOptions extends Migration
{
    public function up()
    {
        $productFields = [
            'suhu_tetap' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'punya_opsi' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'label_opsi' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
            ],
        ];

        foreach ($productFields as $field => $definition) {
            if (!$this->db->fieldExists($field, 'products')) {
                $this->forge->addColumn('products', [$field => $definition]);
            }
        }

        if (!$this->db->tableExists('product_options')) {
            $this->forge->addField([
                'id_opsi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 11,
                ],
                'id_produk' => [
                    'type' => 'VARCHAR',
                    'constraint' => 11,
                ],
                'nama_opsi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'tambahan_harga' => [
                    'type' => 'DECIMAL',
                    'constraint' => '12,2',
                    'default' => 0,
                ],
                'gambar' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'urutan' => [
                    'type' => 'INT',
                    'default' => 0,
                ],
                'aktif' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'created_at' => [
                    'type' => 'TIMESTAMP',
                    'default' => new RawSql('CURRENT_TIMESTAMP'),
                ],
            ]);
            $this->forge->addKey('id_opsi', true);
            $this->forge->addKey('id_produk');
            $this->forge->addForeignKey('id_produk', 'products', 'id_produk', 'CASCADE', 'CASCADE');
            $this->forge->createTable('product_options');
        }

        if (!$this->db->fieldExists('opsi', 'order_items')) {
            $this->forge->addColumn('order_items', [
                'opsi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('opsi', 'order_items')) {
            $this->forge->dropColumn('order_items', 'opsi');
        }
        if ($this->db->tableExists('product_options')) {
            $this->forge->dropTable('product_options');
        }
        foreach (['label_opsi', 'punya_opsi', 'suhu_tetap'] as $field) {
            if ($this->db->fieldExists($field, 'products')) {
                $this->forge->dropColumn('products', $field);
            }
        }
    }
}
