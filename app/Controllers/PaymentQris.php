<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\StoreIdentity;

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

        $store = StoreIdentity::get();

        return view('customer/payment_qris', [
            'order'          => $order,
            'id_meja'        => $id_meja,
            'nama_toko'      => $store['nama_toko'],
            'alamat_lengkap' => $store['alamat_lengkap'],
            'nmid'           => $store['nmid'],
            'qris'           => $store['qris_image'],
            'qris_mode'      => $store['qris_mode'],
            'is_demo'        => $store['is_demo'],
            'order_token'    => $order['token'],
        ]);
    }
}
