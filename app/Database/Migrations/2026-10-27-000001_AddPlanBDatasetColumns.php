<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlanBDatasetColumns extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('kategori_dataset', 'products')) {
            $this->forge->addColumn('products', [
                'kategori_dataset' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'kategori',
                ],
            ]);
        }

        if (!$this->db->fieldExists('tipe_dataset', 'products')) {
            $this->forge->addColumn('products', [
                'tipe_dataset' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'kategori_dataset',
                ],
            ]);
        }

        if (!$this->db->fieldExists('ukuran', 'products')) {
            $this->forge->addColumn('products', [
                'ukuran' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'after' => 'tipe_dataset',
                ],
            ]);
        }

        if (!$this->db->fieldExists('kode_produk_dataset', 'products')) {
            $this->forge->addColumn('products', [
                'kode_produk_dataset' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'after' => 'ukuran',
                ],
            ]);
        }

        if (!$this->db->fieldExists('harga_dolar_ref', 'products')) {
            $this->forge->addColumn('products', [
                'harga_dolar_ref' => [
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'null' => true,
                    'after' => 'harga',
                ],
            ]);
        }

        if (!$this->db->fieldExists('sumber_model', 'recommendation_logs')) {
            $this->forge->addColumn('recommendation_logs', [
                'sumber_model' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'public_dataset',
                    'null' => false,
                ],
            ]);
        }

        if (!$this->db->fieldExists('label_keramaian', 'recommendation_logs')) {
            $this->forge->addColumn('recommendation_logs', [
                'label_keramaian' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                ],
            ]);
        }

        if (!$this->db->fieldExists('versi_model', 'recommendation_logs')) {
            $this->forge->addColumn('recommendation_logs', [
                'versi_model' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'default' => '2.0.0-planB',
                    'null' => false,
                ],
            ]);
        }
    }

    public function down()
    {
        foreach (['kategori_dataset', 'tipe_dataset', 'ukuran', 'kode_produk_dataset', 'harga_dolar_ref'] as $column) {
            if ($this->db->fieldExists($column, 'products')) {
                $this->forge->dropColumn('products', $column);
            }
        }

        foreach (['sumber_model', 'label_keramaian', 'versi_model'] as $column) {
            if ($this->db->fieldExists($column, 'recommendation_logs')) {
                $this->forge->dropColumn('recommendation_logs', $column);
            }
        }
    }
}
