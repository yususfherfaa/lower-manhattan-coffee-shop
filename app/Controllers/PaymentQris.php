<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class PaymentQris extends BaseController
{
    public function index($id_order, $id_meja)
    {
        $db = \Config\Database::connect();

        $order = $db->table('orders')
            ->where('id_order', $id_order)
            ->get()
            ->getRowArray();

        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pesanan tidak ditemukan.');
        }

        $providedToken = (string) $this->request->getGet('token');
        $sessionToken = (string) session()->get('order_access_' . $id_order);
        if (!hash_equals((string) $order['token'], $providedToken) && !hash_equals((string) $order['token'], $sessionToken)) {
            return $this->response->setStatusCode(403)->setBody('Akses pesanan tidak valid.');
        }

        $merchantName = trim((string) (setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop'));
        $merchantAddress = trim((string) (setting('alamat') ?? 'Bekasi Utara'));
        $qris = trim((string) (setting('qris') ?? 'uploads/qris-placeholder.svg'));

        return view('customer/payment_qris', [
            'order' => $order,
            'id_meja' => $id_meja,
            'merchant_name' => $merchantName ?: 'Lower Manhattan Coffee Shop',
            'merchant_address' => $merchantAddress ?: 'Bekasi Utara',
            'qris' => $qris ?: 'uploads/qris-placeholder.svg',
            'order_token' => $order['token'],
        ]);
    }
}
