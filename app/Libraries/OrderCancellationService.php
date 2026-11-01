<?php

namespace App\Libraries;

use App\Models\SettingModel;
use CodeIgniter\Database\BaseConnection;
use Throwable;

class OrderCancellationService
{
    public const TIMEOUT_SETTING = 'batas_pembayaran_menit';
    public const DEFAULT_TIMEOUT_MINUTES = 15;
    public const MAX_TIMEOUT_MINUTES = 1440;

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function timeoutMinutes(): int
    {
        $setting = (new SettingModel())
            ->where('nama', self::TIMEOUT_SETTING)
            ->first();

        $minutes = filter_var($setting['value'] ?? null, FILTER_VALIDATE_INT);
        if ($minutes === false || $minutes < 1 || $minutes > self::MAX_TIMEOUT_MINUTES) {
            return self::DEFAULT_TIMEOUT_MINUTES;
        }

        return $minutes;
    }

    public function expireStaleOrders(): int
    {
        $minutes = $this->timeoutMinutes();
        $orders = $this->db->query(
            'SELECT id_order FROM orders
             WHERE status = ? AND TIMESTAMPDIFF(SECOND, created_at, CURRENT_TIMESTAMP) > ?',
            ['menunggu_pembayaran', $minutes * 60]
        )->getResultArray();

        $cancelled = 0;
        foreach ($orders as $order) {
            if ($this->cancelPendingOrder($order['id_order'], $minutes) === 'cancelled') {
                $cancelled++;
            }
        }

        return $cancelled;
    }

    /**
     * Cancel a pending order and return its stock exactly once.
     * Pass $expiredAfterMinutes to enforce the age check for automatic expiry.
     */
    public function cancelPendingOrder(string $idOrder, ?int $expiredAfterMinutes = null): string
    {
        if (!$this->db->transBegin()) {
            return 'failed';
        }

        try {
            $order = $this->db->query(
                'SELECT id_order, status FROM orders WHERE id_order = ? FOR UPDATE',
                [$idOrder]
            )->getRowArray();

            if (!$order || $order['status'] !== 'menunggu_pembayaran') {
                $this->db->transRollback();
                return 'not_pending';
            }

            if ($expiredAfterMinutes !== null) {
                $expired = $this->db->query(
                    'SELECT TIMESTAMPDIFF(SECOND, created_at, CURRENT_TIMESTAMP) > ? AS expired
                     FROM orders WHERE id_order = ?',
                    [$expiredAfterMinutes * 60, $idOrder]
                )->getRowArray();

                if (empty($expired['expired'])) {
                    $this->db->transRollback();
                    return 'not_pending';
                }
            }

            $items = $this->db->query(
                'SELECT id_produk, SUM(qty) AS qty FROM order_items WHERE id_order = ? GROUP BY id_produk',
                [$idOrder]
            )->getResultArray();

            foreach ($items as $item) {
                $qty = (int) $item['qty'];
                if ($qty < 1) {
                    continue;
                }

                $updated = $this->db->query(
                    'UPDATE products SET stok = stok + ? WHERE id_produk = ?',
                    [$qty, $item['id_produk']]
                );

                if (!$updated || $this->db->affectedRows() !== 1) {
                    throw new \RuntimeException('Stok produk tidak berhasil dikembalikan.');
                }
            }

            $updated = $this->db->query(
                'UPDATE orders SET status = ? WHERE id_order = ? AND status = ?',
                ['dibatalkan', $idOrder, 'menunggu_pembayaran']
            );

            if (!$updated || $this->db->affectedRows() !== 1 || !$this->db->transStatus()) {
                throw new \RuntimeException('Status pesanan tidak berhasil dibatalkan.');
            }

            if (!$this->db->transCommit()) {
                throw new \RuntimeException('Transaksi pembatalan tidak berhasil disimpan.');
            }

            return 'cancelled';
        } catch (Throwable $exception) {
            $this->db->transRollback();
            log_message('error', 'Order cancellation failed for {orderId}: {message}', [
                'orderId' => $idOrder,
                'message' => $exception->getMessage(),
            ]);

            return 'failed';
        }
    }
}
