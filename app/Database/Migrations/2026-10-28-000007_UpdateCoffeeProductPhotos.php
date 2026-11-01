<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateCoffeeProductPhotos extends Migration
{
    private const PRODUCT_IMAGES = [
        'PRDM0022' => [
            'foto' => 'foto-produk-PRDM0022.jpg',
            'products' => ['PRDM0022', 'PRDM0023', 'PRDM0024'],
            'replaceable' => ['foto-produk-PRDM0022.jpg', 'catalog-black-coffee.jpg', 'coffee.svg'],
        ],
        'PRDM0031' => [
            'foto' => 'foto-produk-PRDM0031.jpg',
            'products' => ['PRDM0031', 'PRDM0032', 'PRDM0033'],
            'replaceable' => ['foto-produk-PRDM0031.jpg', 'catalog-ethiopia-beans.jpg', 'catalog-black-coffee.jpg', 'coffee.svg'],
        ],
        'PRDM0034' => [
            'foto' => 'foto-produk-PRDM0034.jpg',
            'products' => ['PRDM0034', 'PRDM0035', 'PRDM0036'],
            'replaceable' => ['foto-produk-PRDM0034.jpg', 'catalog-jamaica-beans.jpg', 'catalog-black-coffee.jpg', 'coffee.svg'],
        ],
        'PRDM0087' => [
            'foto' => 'foto-produk-PRDM0087.jpg',
            'products' => ['PRDM0087'],
            'replaceable' => ['coffee.svg', 'catalog-brazilian-espresso.jpg'],
        ],
    ];

    public function up()
    {
        if (!$this->db->tableExists('products') || !$this->db->fieldExists('gambar', 'products')) {
            throw new \RuntimeException('Tabel products atau kolom gambar tidak tersedia untuk memperbarui foto Coffee.');
        }

        foreach (self::PRODUCT_IMAGES as $mapping) {
            $imagePath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $mapping['foto'];
            if (!is_file($imagePath)) {
                throw new \RuntimeException('Foto produk Coffee tidak ditemukan: ' . $imagePath);
            }

            $this->db->table('products')
                ->whereIn('id_produk', $mapping['products'])
                ->whereIn('gambar', $mapping['replaceable'])
                ->update(['gambar' => $mapping['foto']]);
        }
    }

    public function down()
    {
    }
}
