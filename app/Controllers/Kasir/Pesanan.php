<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;
use App\Libraries\OrderCancellationService;
use App\Models\SettingModel;

class Pesanan extends BaseController
{
    public function index()
    {
        (new OrderCancellationService())->expireStaleOrders();
        $db = \Config\Database::connect();

        /* ===============================
           AMBIL PESANAN
        ================================ */

        $orders = $db->table('orders o')

            ->select('
                o.id_order,
                o.id_meja,
                o.nama_pelanggan,
                o.total,
                o.status,
                o.created_at,

                p.id_pay as payment_id,
                p.metode_pembayaran,
                p.total_order,
                p.uang_dibayar,
                p.kembalian,
                p.nama_kasir
            ')

            ->join(
                'payments p',
                'p.id_order = o.id_order',
                'left'
            )

            ->orderBy(
                'o.created_at',
                'DESC'
            )

            ->get()

            ->getResultArray();

        /* ===============================
           SETTINGS
        ================================ */

        $settingModel = new SettingModel();

        $settings = [];

        foreach ($settingModel->findAll() as $s) {

            $settings[$s['nama']]
                = $s['value'];
        }

        return view(
            'kasir/pesanan',
            [

                'orders' => $orders,

                'nama_warkop' =>
                    $settings['nama_warkop']
                    ?? 'Lower Manhattan Coffee Shop'
            ]
        );
    }

    public function cancel(string $id_order)
    {
        $result = (new OrderCancellationService())->cancelPendingOrder($id_order);

        if ($result === 'cancelled') {
            return redirect()->to('/kasir/pesanan')->with('success', 'Pesanan dibatalkan dan stok dikembalikan.');
        }

        if ($result === 'not_pending') {
            return redirect()->to('/kasir/pesanan')->with('error', 'Hanya pesanan yang belum dibayar yang dapat dibatalkan.');
        }

        return redirect()->to('/kasir/pesanan')->with('error', 'Pesanan gagal dibatalkan. Silakan coba lagi.');
    }
}
