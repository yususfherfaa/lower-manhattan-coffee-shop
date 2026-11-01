<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreatePlanBAggregateTables extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('agg_keramaian_jam')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => true,
                ],
                'store_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                ],
                'hari_minggu' => [
                    'type' => 'TINYINT',
                    'constraint' => 4,
                ],
                'jam' => [
                    'type' => 'TINYINT',
                    'constraint' => 4,
                ],
                'avg_transaksi' => [
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'default' => 0,
                ],
                'total_transaksi' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                ],
                'total_qty' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                ],
                'label_keramaian' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                ],
                'updated_at' => [
                    'type' => 'TIMESTAMP',
                    'default' => new RawSql('CURRENT_TIMESTAMP'),
                    'null' => false,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['store_id', 'hari_minggu', 'jam'], false, true, 'uk_store_hari_jam');
            $this->forge->createTable('agg_keramaian_jam');
        }

        if (!$this->db->tableExists('agg_kategori_segmen')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => true,
                ],
                'segmen' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                ],
                'product_category' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'total_penjualan' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                ],
                'proporsi' => [
                    'type' => 'DECIMAL',
                    'constraint' => '6,4',
                    'default' => 0,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['segmen', 'product_category'], false, true, 'uk_segmen_kategori');
            $this->forge->createTable('agg_kategori_segmen');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('agg_kategori_segmen')) {
            $this->forge->dropTable('agg_kategori_segmen');
        }
        if ($this->db->tableExists('agg_keramaian_jam')) {
            $this->forge->dropTable('agg_keramaian_jam');
        }
    }
}
