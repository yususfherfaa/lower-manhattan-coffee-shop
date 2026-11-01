<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UseLicensedCatalogPhotos extends Migration
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

    private const LEGACY_IMAGES = [
        '1782893018_3852702b5d883e8b42a6.jpg' => ['PRDM0001', 'PRDM0002', 'PRDM0003', 'PRDM0004', 'PRDM0005', 'PRDM0006', 'PRDM0007', 'PRDM0008', 'PRDM0009', 'PRDM0010'],
        '1782892849_cca365c575d52226cdfc.jpeg' => ['PRDM0011', 'PRDM0012', 'PRDM0013', 'PRDM0014', 'PRDM0015', 'PRDM0016', 'PRDM0017', 'PRDM0018'],
        'packaged-chocolate.svg' => ['PRDM0019', 'PRDM0020', 'PRDM0021'],
        '1782892671_5d17140debb9adc1a0f7.jpg' => ['PRDM0022', 'PRDM0023', 'PRDM0024', 'PRDM0025', 'PRDM0026', 'PRDM0027', 'PRDM0028', 'PRDM0029', 'PRDM0030', 'PRDM0031', 'PRDM0032', 'PRDM0033', 'PRDM0034', 'PRDM0035', 'PRDM0036', 'PRDM0037', 'PRDM0038', 'PRDM0039', 'PRDM0040', 'PRDM0041', 'PRDM0087'],
        '1782892739_5710e7e00431f6e601b9.jpg' => ['PRDM0042', 'PRDM0043', 'PRDM0044', 'PRDM0045', 'PRDM0046', 'PRDM0047', 'PRDM0048', 'PRDM0049', 'PRDM0050', 'PRDM0051', 'PRDM0052', 'PRDM0053', 'PRDM0054', 'PRDM0055', 'PRDM0056', 'PRDM0057'],
        '1782892953_7355e2cffff88b69963b.png' => ['PRDM0058', 'PRDM0059', 'PRDM0060', 'PRDM0061'],
        'flavours.svg' => ['PRDM0063', 'PRDM0064', 'PRDM0065', 'PRDM0084'],
        'bakery.svg' => ['PRDM0069', 'PRDM0070', 'PRDM0071', 'PRDM0072', 'PRDM0073', 'PRDM0074', 'PRDM0075', 'PRDM0076', 'PRDM0077', 'PRDM0078', 'PRDM0079'],
        'branded.svg' => ['PRDM0081', 'PRDM0082', 'PRDM0083'],
    ];

    public function up()
    {
        if (!$this->db->tableExists('products') || !$this->db->fieldExists('gambar', 'products')) {
            return;
        }

        $legacyImages = array_keys(self::LEGACY_IMAGES);
        foreach (self::PRODUCT_IMAGES as $image => $productIds) {
            $this->db->table('products')
                ->whereIn('id_produk', $productIds)
                ->whereIn('gambar', $legacyImages)
                ->update(['gambar' => $image]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('products') || !$this->db->fieldExists('gambar', 'products')) {
            return;
        }

        $catalogImages = array_keys(self::PRODUCT_IMAGES);
        foreach (self::LEGACY_IMAGES as $image => $productIds) {
            $this->db->table('products')
                ->whereIn('id_produk', $productIds)
                ->whereIn('gambar', $catalogImages)
                ->update(['gambar' => $image]);
        }
    }
}
