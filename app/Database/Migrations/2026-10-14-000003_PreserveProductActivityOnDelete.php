<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PreserveProductActivityOnDelete extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('data_products')) {
            return;
        }

        $foreignKeys = $this->db->query(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME = ?',
            ['data_products', 'id_produk', 'products']
        )->getResultArray();

        foreach ($foreignKeys as $foreignKey) {
            $constraint = $this->db->escapeIdentifiers($foreignKey['CONSTRAINT_NAME']);
            $this->db->query('ALTER TABLE `data_products` DROP FOREIGN KEY ' . $constraint);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('data_products') || !$this->db->tableExists('products')) {
            return;
        }

        $orphanCount = $this->db->table('data_products')
            ->join('products', 'products.id_produk = data_products.id_produk', 'left')
            ->where('products.id_produk', null)
            ->countAllResults();

        if ($orphanCount > 0) {
            throw new \RuntimeException('Cannot restore the product foreign key while activity logs reference deleted products.');
        }

        $foreignKey = $this->db->query(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME = ?',
            ['data_products', 'id_produk', 'products']
        )->getRowArray();

        if (!$foreignKey) {
            $this->db->query(
                'ALTER TABLE `data_products`
                 ADD CONSTRAINT `fk_data_produk`
                 FOREIGN KEY (`id_produk`) REFERENCES `products` (`id_produk`)
                 ON DELETE CASCADE ON UPDATE CASCADE'
            );
        }
    }
}