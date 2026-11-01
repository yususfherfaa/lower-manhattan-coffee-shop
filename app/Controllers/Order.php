<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\SettingModel;

class Order extends BaseController
{
    public function index($id_meja = null)
    {
        if (!$id_meja) {
            die('Meja tidak valid');
        }

        $db = \Config\Database::connect();

        // VALIDASI MEJA
        $meja = $db->table('tables_meja')
            ->where('id_meja', $id_meja)
            ->get()
            ->getRowArray();

        if (!$meja) {
            die('Meja tidak ditemukan');
        }

        // PRODUK
        $productModel = new ProductModel();

        $hasPlanBDataset = $db->fieldExists('kode_produk_dataset', 'products')
            || $db->table('products')->like('id_produk', 'PRDM%')->countAllResults() > 0;

        $prodBuilder = $productModel->orderBy('nama', 'ASC');
        if ($db->fieldExists('aktif', 'products')) {
            $prodBuilder->where('aktif', 1);
        }

        if ($hasPlanBDataset) {
            $prodBuilder->like('id_produk', 'PRDM%');
        }

        $products = $prodBuilder->findAll();

        if (!$products && $hasPlanBDataset) {
            $fallbackBuilder = $productModel->orderBy('nama', 'ASC');
            if ($db->fieldExists('aktif', 'products')) {
                $fallbackBuilder->where('aktif', 1);
            }
            $products = $fallbackBuilder->findAll();
        }

        $categoryOrder = [
            'Bakery',
            'Branded',
            'Coffee',
            'Coffee beans',
            'Drinking Chocolate',
            'Flavours',
            'Loose Tea',
            'Packaged Chocolate',
            'Tea',
        ];
        usort($products, static function (array $left, array $right) use ($categoryOrder): int {
            $leftCategory = $left['kategori_dataset'] ?? $left['kategori'];
            $rightCategory = $right['kategori_dataset'] ?? $right['kategori'];
            $leftRank = array_search($leftCategory, $categoryOrder, true);
            $rightRank = array_search($rightCategory, $categoryOrder, true);
            $leftRank = $leftRank === false ? count($categoryOrder) : $leftRank;
            $rightRank = $rightRank === false ? count($categoryOrder) : $rightRank;
            return $leftRank <=> $rightRank ?: strcasecmp($left['nama'], $right['nama']);
        });
        $optionsByProduct = [];
        foreach ($db->table('product_options')
            ->where('aktif', 1)
            ->orderBy('urutan', 'ASC')
            ->get()
            ->getResultArray() as $option) {
            $optionsByProduct[$option['id_produk']][] = $option;
        }
        foreach ($products as &$product) {
            $product['options'] = $optionsByProduct[$product['id_produk']] ?? [];
            if (in_array($product['kategori_dataset'] ?? $product['kategori'], ['Bakery', 'Branded', 'Coffee beans', 'Flavours', 'Loose Tea', 'Packaged Chocolate'], true)) {
                $product['label_opsi'] = 'Pilih varian';
            }
        }
        unset($product);

        // SETTINGS
        $settingModel = new SettingModel();

        $settings = [];

        foreach ($settingModel->findAll() as $s) {
            $settings[$s['nama']] = $s['value'];
        }

        $data = [

            'id_meja' => $id_meja,

            'meja' => $meja,

            'products' => $products,

            'nama_warkop' =>
                $settings['nama_warkop']
                ?? 'Lower Manhattan Coffee Shop',

            'alamat' =>
                $settings['alamat']
                ?? 'Alamat belum diatur',

            'kontak' =>
                $settings['kontak']
                ?? '-',

            'logo' =>
                $settings['logo']
                ?? 'uploads/manhattan-coffee-shop-logo.png'
        ];

        return view('customer/order', $data);
    }
}