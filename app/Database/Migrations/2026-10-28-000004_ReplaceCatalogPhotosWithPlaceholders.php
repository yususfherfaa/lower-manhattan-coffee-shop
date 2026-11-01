<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ReplaceCatalogPhotosWithPlaceholders extends Migration
{
    private const PRODUCT_IMAGES = [
        'catalog-coffee-beans.jpg' => ['PRDM0001', 'PRDM0002', 'PRDM0005', 'PRDM0008', 'PRDM0009', 'PRDM0010'],
        'catalog-espresso-beans.jpg' => ['PRDM0003', 'PRDM0004'],
        'catalog-ethiopia-beans.jpg' => ['PRDM0006'],
        'catalog-jamaica-beans.jpg' => ['PRDM0007'],
        'catalog-lemon-grass-tea.jpg' => ['PRDM0011', 'PRDM0042', 'PRDM0043'],
        'catalog-peppermint-tea.jpg' => ['PRDM0012', 'PRDM0044', 'PRDM0045'],
        'catalog-breakfast-tea.jpg' => ['PRDM0013', 'PRDM0048', 'PRDM0049'],
        'catalog-earl-grey-tea.jpg' => ['PRDM0014', 'PRDM0050', 'PRDM0051'],
        'catalog-green-tea.jpg' => ['PRDM0015', 'PRDM0046', 'PRDM0047'],
        'catalog-chai-tea.jpg' => ['PRDM0016', 'PRDM0017', 'PRDM0018', 'PRDM0052', 'PRDM0053', 'PRDM0054', 'PRDM0055', 'PRDM0056', 'PRDM0057'],
        'catalog-dark-chocolate.jpg' => ['PRDM0019', 'PRDM0020', 'PRDM0021'],
        'catalog-black-coffee.jpg' => ['PRDM0022', 'PRDM0023', 'PRDM0024', 'PRDM0028', 'PRDM0029', 'PRDM0030', 'PRDM0031', 'PRDM0032', 'PRDM0033', 'PRDM0034', 'PRDM0035', 'PRDM0036'],
        'catalog-brazilian-espresso.jpg' => ['PRDM0025', 'PRDM0026', 'PRDM0027', 'PRDM0037', 'PRDM0087'],
        'catalog-latte.jpg' => ['PRDM0038', 'PRDM0039'],
        'catalog-cappuccino.jpg' => ['PRDM0040', 'PRDM0041'],
        'catalog-hot-chocolate.jpg' => ['PRDM0058', 'PRDM0059', 'PRDM0060', 'PRDM0061'],
        'catalog-coffee-syrup.jpg' => ['PRDM0063', 'PRDM0064', 'PRDM0065', 'PRDM0084'],
        'catalog-biscotti.jpg' => ['PRDM0069', 'PRDM0074', 'PRDM0076'],
        'catalog-cranberry-scone.jpg' => ['PRDM0070', 'PRDM0078'],
        'catalog-chocolate-croissant.jpg' => ['PRDM0071'],
        'catalog-ginger-scone.jpg' => ['PRDM0072', 'PRDM0079'],
        'catalog-almond-croissant.jpg' => ['PRDM0073'],
        'catalog-croissant.jpg' => ['PRDM0075'],
        'catalog-oatmeal-scone.jpg' => ['PRDM0077'],
        'catalog-tshirt.jpg' => ['PRDM0081'],
        'catalog-coffee-mug.jpg' => ['PRDM0082'],
        'catalog-latte-cup.jpg' => ['PRDM0083'],
    ];

    private const CATEGORY_IMAGES = [
        'Bakery' => 'bakery.svg',
        'Branded' => 'branded.svg',
        'Coffee' => 'coffee.svg',
        'Coffee beans' => 'coffee-beans.svg',
        'Drinking Chocolate' => 'drinking-chocolate.svg',
        'Flavours' => 'flavours.svg',
        'Loose Tea' => 'loose-tea.svg',
        'Packaged Chocolate' => 'packaged-chocolate.svg',
        'Tea' => 'tea.svg',
    ];

    private const LEGACY_CATALOG_IMAGES = [
        '1782892671_5d17140debb9adc1a0f7.jpg',
        '1782892739_5710e7e00431f6e601b9.jpg',
        '1782892849_cca365c575d52226cdfc.jpeg',
        '1782892953_7355e2cffff88b69963b.png',
    ];

    public function up()
    {
        if (!$this->db->tableExists('products') || !$this->db->fieldExists('gambar', 'products')) {
            return;
        }

        $products = $this->db->table('products')
            ->select('id_produk, kategori, gambar')
            ->groupStart()
                ->like('gambar', 'catalog-', 'after')
                ->orWhereIn('gambar', self::LEGACY_CATALOG_IMAGES)
            ->groupEnd()
            ->get()
            ->getResultArray();

        foreach ($products as $product) {
            $placeholder = self::CATEGORY_IMAGES[$product['kategori']] ?? 'assets/no-image.svg';
            $this->db->table('products')
                ->where('id_produk', $product['id_produk'])
                ->where('gambar', $product['gambar'])
                ->update(['gambar' => $placeholder]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('products') || !$this->db->fieldExists('gambar', 'products')) {
            return;
        }

        $productIds = [];
        foreach (self::PRODUCT_IMAGES as $ids) {
            array_push($productIds, ...$ids);
        }

        $products = $this->db->table('products')
            ->select('id_produk, kategori, gambar')
            ->whereIn('id_produk', $productIds)
            ->get()
            ->getResultArray();
        $imagesByProduct = [];
        foreach (self::PRODUCT_IMAGES as $image => $ids) {
            foreach ($ids as $id) {
                $imagesByProduct[$id] = $image;
            }
        }

        foreach ($products as $product) {
            $placeholder = self::CATEGORY_IMAGES[$product['kategori']] ?? 'assets/no-image.svg';
            if ($product['gambar'] !== $placeholder || !isset($imagesByProduct[$product['id_produk']])) {
                continue;
            }

            $this->db->table('products')
                ->where('id_produk', $product['id_produk'])
                ->where('gambar', $placeholder)
                ->update(['gambar' => $imagesByProduct[$product['id_produk']]]);
        }
    }
}
