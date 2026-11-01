<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UseProvidedProductPhotos extends Migration
{
    private const PRODUCT_IMAGES = [
        'foto-produk-PRDM0001.jpg' => ['PRDM0001'],
        'foto-produk-PRDM0002.jpg' => ['PRDM0002'],
        'foto-produk-PRDM0003.jpg' => ['PRDM0003'],
        'foto-produk-PRDM0004.jpg' => ['PRDM0004'],
        'foto-produk-PRDM0005.jpg' => ['PRDM0005'],
        'foto-produk-PRDM0006.jpg' => ['PRDM0006'],
        'foto-produk-PRDM0007.jpg' => ['PRDM0007'],
        'foto-produk-PRDM0008.jpg' => ['PRDM0008'],
        'foto-produk-PRDM0009.jpg' => ['PRDM0009'],
        'foto-produk-PRDM0010.jpg' => ['PRDM0010'],
        'foto-produk-PRDM0011.jpg' => ['PRDM0011'],
        'foto-produk-PRDM0012.jpg' => ['PRDM0012'],
        'foto-produk-PRDM0013.jpg' => ['PRDM0013'],
        'foto-produk-PRDM0014.jpg' => ['PRDM0014'],
        'foto-produk-PRDM0015.jpg' => ['PRDM0015'],
        'foto-produk-PRDM0016.jpg' => ['PRDM0016'],
        'foto-produk-PRDM0017.jpg' => ['PRDM0017'],
        'foto-produk-PRDM0018.jpg' => ['PRDM0018'],
        'foto-produk-PRDM0019.jpg' => ['PRDM0019'],
        'foto-produk-PRDM0020.jpg' => ['PRDM0020'],
        'foto-produk-PRDM0021.jpg' => ['PRDM0021'],
        'foto-produk-PRDM0022.jpg' => ['PRDM0022', 'PRDM0023', 'PRDM0024'],
        'foto-produk-PRDM0025.jpg' => ['PRDM0025', 'PRDM0026', 'PRDM0027'],
        'foto-produk-PRDM0028.jpg' => ['PRDM0028', 'PRDM0029', 'PRDM0030'],
        'foto-produk-PRDM0031.jpg' => ['PRDM0031', 'PRDM0032', 'PRDM0033'],
        'foto-produk-PRDM0034.jpg' => ['PRDM0034', 'PRDM0035', 'PRDM0036'],
        'foto-produk-PRDM0037.jpg' => ['PRDM0037'],
        'foto-produk-PRDM0038.jpg' => ['PRDM0038', 'PRDM0039'],
        'foto-produk-PRDM0040.jpg' => ['PRDM0040', 'PRDM0041'],
        'foto-produk-PRDM0042.jpg' => ['PRDM0042', 'PRDM0043'],
        'foto-produk-PRDM0044.jpg' => ['PRDM0044', 'PRDM0045'],
        'foto-produk-PRDM0046.jpg' => ['PRDM0046', 'PRDM0047'],
        'foto-produk-PRDM0048.jpg' => ['PRDM0048', 'PRDM0049'],
        'foto-produk-PRDM0050.jpg' => ['PRDM0050', 'PRDM0051'],
        'foto-produk-PRDM0052.jpg' => ['PRDM0052', 'PRDM0053'],
        'foto-produk-PRDM0054.jpg' => ['PRDM0054', 'PRDM0055'],
        'foto-produk-PRDM0056.jpg' => ['PRDM0056', 'PRDM0057'],
        'foto-produk-PRDM0058.jpg' => ['PRDM0058', 'PRDM0059'],
        'foto-produk-PRDM0060.jpg' => ['PRDM0060', 'PRDM0061'],
        'foto-produk-PRDM0063.jpg' => ['PRDM0063'],
        'foto-produk-PRDM0064.jpg' => ['PRDM0064'],
        'foto-produk-PRDM0065.jpg' => ['PRDM0065'],
        'foto-produk-PRDM0069.jpg' => ['PRDM0069'],
        'foto-produk-PRDM0070.jpg' => ['PRDM0070'],
        'foto-produk-PRDM0071.jpg' => ['PRDM0071'],
        'foto-produk-PRDM0072.jpg' => ['PRDM0072'],
        'foto-produk-PRDM0073.jpg' => ['PRDM0073'],
        'foto-produk-PRDM0074.jpg' => ['PRDM0074'],
        'foto-produk-PRDM0075.jpg' => ['PRDM0075'],
        'foto-produk-PRDM0076.jpg' => ['PRDM0076'],
        'foto-produk-PRDM0077.jpg' => ['PRDM0077'],
        'foto-produk-PRDM0078.webp' => ['PRDM0078'],
        'foto-produk-PRDM0079.jpg' => ['PRDM0079'],
        'foto-produk-PRDM0081.jpg' => ['PRDM0081'],
        'foto-produk-PRDM0082.jpg' => ['PRDM0082'],
        'foto-produk-PRDM0083.jpg' => ['PRDM0083'],
        'foto-produk-PRDM0084.jpg' => ['PRDM0084'],
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

    private const LEGACY_IMAGES = [
        'catalog-coffee-beans.jpg',
        'catalog-espresso-beans.jpg',
        'catalog-ethiopia-beans.jpg',
        'catalog-jamaica-beans.jpg',
        'catalog-lemon-grass-tea.jpg',
        'catalog-peppermint-tea.jpg',
        'catalog-breakfast-tea.jpg',
        'catalog-earl-grey-tea.jpg',
        'catalog-green-tea.jpg',
        'catalog-chai-tea.jpg',
        'catalog-dark-chocolate.jpg',
        'catalog-black-coffee.jpg',
        'catalog-brazilian-espresso.jpg',
        'catalog-latte.jpg',
        'catalog-cappuccino.jpg',
        'catalog-hot-chocolate.jpg',
        'catalog-coffee-syrup.jpg',
        'catalog-biscotti.jpg',
        'catalog-cranberry-scone.jpg',
        'catalog-chocolate-croissant.jpg',
        'catalog-ginger-scone.jpg',
        'catalog-almond-croissant.jpg',
        'catalog-croissant.jpg',
        'catalog-oatmeal-scone.jpg',
        'catalog-tshirt.jpg',
        'catalog-coffee-mug.jpg',
        'catalog-latte-cup.jpg',
        '1782892671_5d17140debb9adc1a0f7.jpg',
        '1782892739_5710e7e00431f6e601b9.jpg',
        '1782892849_cca365c575d52226cdfc.jpeg',
        '1782892953_7355e2cffff88b69963b.png',
        'no-image.svg',
        'assets/no-image.svg',
        'bakery.svg',
        'branded.svg',
        'coffee.svg',
        'coffee-beans.svg',
        'drinking-chocolate.svg',
        'flavours.svg',
        'loose-tea.svg',
        'packaged-chocolate.svg',
        'tea.svg',
    ];

    public function up()
    {
        if (!$this->db->tableExists('products') || !$this->db->fieldExists('gambar', 'products')) {
            return;
        }

        foreach (self::PRODUCT_IMAGES as $image => $productIds) {
            $imagePath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $image;
            if (!is_file($imagePath)) {
                throw new \RuntimeException("Product photo asset is missing: {$imagePath}");
            }

            $products = $this->db->table('products')
                ->select('id_produk, gambar')
                ->whereIn('id_produk', $productIds)
                ->get()
                ->getResultArray();

            foreach ($products as $product) {
                if (!$this->isReplaceableImage($product['gambar'])) {
                    continue;
                }

                $this->db->table('products')
                    ->where('id_produk', $product['id_produk'])
                    ->where('gambar', $product['gambar'])
                    ->update(['gambar' => $image]);
            }
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('products') || !$this->db->fieldExists('gambar', 'products')) {
            return;
        }

        foreach (self::PRODUCT_IMAGES as $image => $productIds) {
            $products = $this->db->table('products')
                ->select('id_produk, kategori, gambar')
                ->whereIn('id_produk', $productIds)
                ->where('gambar', $image)
                ->get()
                ->getResultArray();

            foreach ($products as $product) {
                $placeholder = self::CATEGORY_IMAGES[$product['kategori']] ?? 'assets/no-image.svg';
                $this->db->table('products')
                    ->where('id_produk', $product['id_produk'])
                    ->where('gambar', $image)
                    ->update(['gambar' => $placeholder]);
            }
        }
    }

    private function isReplaceableImage(string $image): bool
    {
        return in_array($image, self::LEGACY_IMAGES, true)
            || in_array($image, self::CATEGORY_IMAGES, true)
            || str_starts_with($image, 'catalog-');
    }
}
