<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class HapusRiwayatPenjualanSeeder extends Seeder
{
    public function run()
    {
        foreach (['orders', 'order_items', 'payments'] as $table) {
            if (!$this->db->tableExists($table)) {
                throw new \RuntimeException("Tabel {$table} tidak ditemukan; riwayat penjualan tidak diubah.");
            }
        }

        $tables = ['order_items', 'payments', 'orders'];
        if ($this->db->tableExists('notifications')) {
            $tables[] = 'notifications';
        }

        $counts = [];
        foreach ($tables as $table) {
            $counts[$table] = $table === 'notifications'
                ? (int) $this->db->query(
                    'SELECT COUNT(*) AS total FROM `notifications` WHERE `id_order` IN (SELECT `id_order` FROM `orders`)'
                )->getRow()->total
                : (int) $this->db->table($table)->countAllResults();
        }

        if (!$this->db->transBegin()) {
            throw new \RuntimeException('Tidak dapat memulai transaksi penghapusan riwayat penjualan.');
        }

        try {
            if ($this->db->tableExists('notifications') && $this->db->query(
                'DELETE FROM `notifications` WHERE `id_order` IN (SELECT `id_order` FROM `orders`)'
            ) === false) {
                throw new \RuntimeException('Gagal menghapus notifikasi yang terkait transaksi.');
            }

            foreach (['order_items', 'payments', 'orders'] as $table) {
                if ($this->db->query('DELETE FROM `' . $table . '`') === false) {
                    throw new \RuntimeException("Gagal menghapus data dari {$table}.");
                }
            }

            if (!$this->db->transCommit()) {
                throw new \RuntimeException('Gagal menyimpan penghapusan riwayat penjualan.');
            }
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }

        echo "Riwayat penjualan berhasil dihapus:\n";
        foreach ($counts as $table => $count) {
            echo "- {$table}: {$count} baris\n";
        }
        echo "Akun admin/kasir, katalog, dan log pengelolaan menu tidak diubah.\n";
    }
}
