<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class OrderStatus extends BaseController
{
    public function cek($id_order)
    {
        $db = \Config\Database::connect();

        $order = $db->table('orders')
            ->where('id_order', $id_order)
            ->get()
            ->getRowArray();

        if (!$order) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status' => 'not_found',
                    'message' => 'Pesanan tidak ditemukan.',
                ]);
        }

        $providedToken = (string) $this->request->getGet('token');
        $sessionToken = (string) session()->get('order_access_' . $id_order);
        if (!hash_equals((string) $order['token'], $providedToken) && !hash_equals((string) $order['token'], $sessionToken)) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'forbidden']);
        }

        return $this->response->setJSON([
            'status' => $order['status']
        ]);
    }
}
