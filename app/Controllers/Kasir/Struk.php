<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\PaymentModel;
use App\Models\SettingModel;
use App\Models\UserModel;

class Struk extends BaseController
{
    public function index($id_order)
    {
        $orderModel = new OrderModel();
        $itemModel = new OrderItemModel();
        $paymentModel = new PaymentModel();
        $settingModel = new SettingModel();

        $order = $orderModel->find($id_order);
        

        if (!$order) {
            return 'Struk tidak ditemukan';
        }

        $payment = $paymentModel
            ->where('id_order', $id_order)
            ->first();

        $items = $itemModel
            ->where('id_order', $id_order)
            ->findAll();

        $settings = [];

        foreach ($settingModel->findAll() as $s) {
            $settings[$s['nama']] = $s['value'];
        }

        $data = [
            'order' => $order,
            'payment' => $payment,
            'items' => $items,

            'nama_warkop' => $settings['nama_warkop'] ?? 'Lower Manhattan Coffee Shop',
            'alamat' => $settings['alamat'] ?? '-',
            'kontak' => $settings['kontak'] ?? '-',
        ];

        return view('kasir/struk', $data);
    }
}
