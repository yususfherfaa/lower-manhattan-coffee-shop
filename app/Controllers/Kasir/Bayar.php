<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;
use App\Libraries\OrderCancellationService;
use App\Libraries\PaymentRules;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\PaymentModel;
use App\Models\SettingModel;

class Bayar extends BaseController
{
    public function index($id_order)
    {
        (new OrderCancellationService())->expireStaleOrders();
        $orderModel = new OrderModel();
        $itemModel = new OrderItemModel();
        $settingModel = new SettingModel();

        $order = $orderModel->find($id_order);

        if (!$order) {
            return 'Pesanan tidak ditemukan';
        }

        if ($order['status'] !== 'menunggu_pembayaran') {
            return redirect()->to('/kasir/pesanan')->with('error', 'Pesanan ini tidak lagi menunggu pembayaran.');
        }

        $items = $itemModel
            ->where('id_order', $id_order)
            ->findAll();

        $settings = [];

        foreach ($settingModel->findAll() as $s) {
            $settings[$s['nama']] = $s['value'];
        }

        $data = [
            'order' => $order,
            'items' => $items,
            'nama_warkop' => $settings['nama_warkop'] ?? 'Lower Manhattan Coffee Shop'
        ];

        return view('kasir/bayar', $data);
    }

    public function process($id_order)
    {
        $cancellationService = new OrderCancellationService();
        $cancellationService->expireStaleOrders();
        $timeoutMinutes = $cancellationService->timeoutMinutes();
        $orderModel = new OrderModel();
        $paymentModel = new PaymentModel();

        $db = \Config\Database::connect();
        if (!$db->transBegin()) {
            log_message('error', 'Payment transaction could not start for order {orderId}.', ['orderId' => $id_order]);
            return redirect()->back()->with('error', 'Pembayaran gagal diproses. Silakan coba lagi.');
        }

        $order = $db->query(
            'SELECT * FROM orders WHERE id_order = ? FOR UPDATE',
            [$id_order]
        )->getRowArray();

        if (!$order) {
            $db->transRollback();
            return 'Pesanan tidak ditemukan';
        }

        $existingPayment = $paymentModel
            ->where('id_order', $id_order)
            ->first();
        if (!PaymentRules::canPay((string) $order['status'], (bool) $existingPayment)) {
            $db->transRollback();
            return $existingPayment
                ? redirect()->to('/kasir/struk/' . $id_order)
                : redirect()->to('/kasir/pesanan')->with('error', 'Pesanan sudah dibatalkan atau tidak dapat dibayar.');
        }

        $expired = $db->query(
            'SELECT TIMESTAMPDIFF(SECOND, created_at, CURRENT_TIMESTAMP) > ? AS expired
             FROM orders WHERE id_order = ?',
            [$timeoutMinutes * 60, $id_order]
        )->getRowArray();
        if (!empty($expired['expired'])) {
            $db->transRollback();
            $cancellationService->cancelPendingOrder($id_order, $timeoutMinutes);
            return redirect()->to('/kasir/pesanan')->with('error', 'Batas waktu pembayaran telah lewat; pesanan dibatalkan dan stok dikembalikan.');
        }

        $uang_dibayar = (int)$this->request->getPost('uang_dibayar');
        $total_order  = (int)$order['total'];

        $kembalian = PaymentRules::change($uang_dibayar, $total_order);
        if ($kembalian === null) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Uang dibayar kurang');
        }

        $paymentId = $this->generateIdPay();

        $inserted = $paymentModel->insert([
            'id_pay' => $paymentId,
            'id_order' => $id_order,
            'id_user' => session()->get('id_user'),
            'metode_pembayaran' => $order['metode_pembayaran'],
            'total_order' => $total_order,
            'uang_dibayar' => $uang_dibayar,
            'kembalian' => $kembalian,
            'nama_kasir' => session()->get('nama_kasir')
        ]);

        if (!$inserted || !$db->transStatus()) {
            $db->transRollback();
            log_message('error', 'Payment record could not be saved for order {orderId}.', ['orderId' => $id_order]);
            return redirect()->back()->with('error', 'Pembayaran gagal disimpan. Silakan coba lagi.');
        }

        if (!$orderModel->update($id_order, ['status' => 'lunas']) || !$db->transStatus()) {
            $db->transRollback();
            log_message('error', 'Paid status could not be saved for order {orderId}.', ['orderId' => $id_order]);
            return redirect()->back()->with('error', 'Status pembayaran gagal diperbarui. Silakan coba lagi.');
        }

        if (!$db->transCommit()) {
            $db->transRollback();
            log_message('error', 'Payment transaction could not commit for order {orderId}.', ['orderId' => $id_order]);
            return redirect()->back()->with('error', 'Pembayaran gagal disimpan. Silakan coba lagi.');
        }

        return redirect()->to('/kasir/struk/' . $id_order);
    }

    private function generateIdPay()
{
    $db = \Config\Database::connect();
    $builder = $db->table('payments');

    $last = $db->query('SELECT id_pay FROM payments ORDER BY id_pay DESC LIMIT 1 FOR UPDATE')->getRowArray();

    if (!$last) {
        return 'PAY000001';
    }

    $lastNumber = (int) substr($last['id_pay'], 3);
    $nextNumber = $lastNumber + 1;

    return 'PAY' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
}
}
