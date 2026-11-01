<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class OrderSuccess extends BaseController
{
    public function index($id_order = null, $id_meja = null)
    {
        if (!$id_order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Order tidak valid.');
        }

        $db = \Config\Database::connect();

        // ambil order
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

        // ambil item
        $items = $db->table('order_items')
            ->where('id_order', $id_order)
            ->get()
            ->getResultArray();

        return view('customer/order_success', [

            'order'     => $order,

            'items'     => $items,

            'id_meja'   => $id_meja,
            'order_token' => $order['token'],
        ]);
    }
}
