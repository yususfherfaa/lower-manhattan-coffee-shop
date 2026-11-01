<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;
use App\Models\SettingModel;

class DetailPesanan extends BaseController
{
    public function index($id_order = null)
    {
        $db = \Config\Database::connect();

        /* ======================
           DATA ORDER
        ====================== */

        $order = $db->table('orders')

            ->where('id_order', $id_order)

            ->get()

            ->getRowArray();

        if (!$order) {
            die('Pesanan tidak ditemukan');
        }

        /* ======================
           ITEM PESANAN
        ====================== */

        $items = $db->table('order_items')

            ->where('id_order', $id_order)

            ->get()

            ->getResultArray();

        /* ======================
           PEMBAYARAN
        ====================== */

        $payment = $db->table('payments')

            ->where('id_order', $id_order)

            ->get()

            ->getRowArray();

        /* ======================
           SETTINGS
        ====================== */

        $settingModel = new SettingModel();

        $settings = [];

        foreach ($settingModel->findAll() as $s) {

            $settings[$s['nama']]
                = $s['value'];
        }

        return view(
            'kasir/detail_pesanan',
            [

                'order' => $order,

                'items' => $items,

                'payment' => $payment,

                'nama_warkop' =>
                    $settings['nama_warkop']
                    ?? 'Lower Manhattan Coffee Shop'
            ]
        );
    }
}
