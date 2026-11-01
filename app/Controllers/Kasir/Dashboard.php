<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\SettingModel;

class Dashboard extends BaseController
{
    public function index()
    {
        // CEK LOGIN
        if (!session()->get('logged_in')) {
            return redirect()->to('/');
        }

        $db = \Config\Database::connect();

        $id_user = session()->get('id_user');

        $nama_kasir = session()->get('nama_kasir');

        /* ======================
           STATISTIK HARI INI
        ====================== */

        $todayStart = date('Y-m-d 00:00:00');
        $tomorrowStart = date('Y-m-d 00:00:00', strtotime('+1 day'));

        $total_pesanan = $db->table('payments')
            ->selectCount('id_order')
            ->where('id_user', $id_user)
            ->where('created_at >=', $todayStart)
            ->where('created_at <', $tomorrowStart)
            ->get()
            ->getRow()
            ->id_order;

        $total_pendapatan = $db->table('payments')
            ->selectSum('total_order')
            ->where('id_user', $id_user)
            ->where('created_at >=', $todayStart)
            ->where('created_at <', $tomorrowStart)
            ->get()
            ->getRow()
            ->total_order ?? 0;

         /* ======================
           DATA LAIN
        ====================== */

        $productModel = new ProductModel();

        $jumlah_produk = $productModel->countAll();

            /* ======================
           SETTINGS
        ====================== */

        $settingModel = new SettingModel();

        $settings = [];

        foreach ($settingModel->findAll() as $s) {
            $settings[$s['nama']] = $s['value'];
        }

        $data = [

            'nama_kasir' => $nama_kasir,

            'nama_warkop' => $settings['nama_warkop'] ?? 'Lower Manhattan Coffee Shop',

            'alamat' => $settings['alamat'] ?? 'Alamat belum diatur',

            'kontak' => $settings['kontak'] ?? '-',

            'logo' => $settings['logo'] ?? 'uploads/manhattan-coffee-shop-logo.png',

            'total_pesanan' => $total_pesanan,

            'total_pendapatan' => $total_pendapatan,

            'jumlah_produk' => $jumlah_produk,
        ];

        return view('kasir/dashboard', $data);
    }
}
