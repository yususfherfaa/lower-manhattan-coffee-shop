<?php

use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class CatalogHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('catalog');
    }

    public function testGroupsAvailableDatasetSizesIntoOneMenu(): void
    {
        $products = [
            $this->product('PRDM0025', 'Brazilian (Sm)', 'Sm', 35000),
            $this->product('PRDM0026', 'Brazilian (Rg)', 'Rg', 48000),
            $this->product('PRDM0027', 'Brazilian (Lg)', 'Lg', 56000),
        ];

        $groups = group_catalog_products_by_size($products);

        $this->assertCount(1, $groups);
        $this->assertSame('Brazilian', $groups[0]['name']);
        $this->assertSame('PRDM0026', $groups[0]['product']['id_produk']);
        $this->assertSame(['Small', 'Regular', 'Large'], array_column($groups[0]['variants'], 'label'));
        $this->assertSame([35000, 48000, 56000], array_column(array_column($groups[0]['variants'], 'product'), 'harga'));
    }

    public function testTreatsUnspecifiedSizeAsRegularAndDoesNotInventMissingSizes(): void
    {
        $products = [
            $this->product('PRDM0040', 'Cappuccino', 'none', 60000),
            $this->product('PRDM0041', 'Cappuccino (Lg)', 'Lg', 68000),
        ];

        $groups = group_catalog_products_by_size($products);

        $this->assertCount(1, $groups);
        $this->assertSame('PRDM0040', $groups[0]['product']['id_produk']);
        $this->assertSame(['Regular', 'Large'], array_column($groups[0]['variants'], 'label'));
    }

    public function testKeepsUnspecifiedAndExplicitRegularProductsAvailable(): void
    {
        $products = [
            $this->product('PRDM0038', 'Latte', 'none', 60000),
            $this->product('PRDM0039', 'Latte (Rg)', 'Rg', 68000),
        ];

        $groups = group_catalog_products_by_size($products);

        $this->assertCount(1, $groups);
        $this->assertSame(['Standard', 'Regular'], array_column($groups[0]['variants'], 'label'));
        $this->assertSame(
            ['PRDM0038', 'PRDM0039'],
            array_column(array_column($groups[0]['variants'], 'product'), 'id_produk')
        );
    }

    public function testKeepsProductsWithDifferentCategoriesSeparate(): void
    {
        $products = [
            $this->product('PRDM0001', 'Brazilian - Organic', 'none', 288000, 'Coffee beans'),
            $this->product('PRDM0025', 'Brazilian (Sm)', 'Sm', 35000, 'Coffee'),
            $this->product('PRDM0026', 'Brazilian (Rg)', 'Rg', 48000, 'Coffee'),
        ];

        $groups = group_catalog_products_by_size($products);

        $this->assertCount(2, $groups);
        $this->assertFalse($groups[0]['is_size_group']);
        $this->assertTrue($groups[1]['is_size_group']);
    }

    public function testFormatsSizeLabelsWithoutDatasetAbbreviations(): void
    {
        $this->assertSame('Ethiopia', catalog_product_display_name(['nama' => 'Ethiopia (Lg)']));
        $this->assertSame('Large', catalog_product_size_label(['nama' => 'Ethiopia (Lg)']));
        $this->assertSame('Cappuccino', catalog_product_display_name(['nama' => 'Cappuccino - Regular']));
        $this->assertSame('Regular', catalog_product_size_label(['nama' => 'Cappuccino - Regular']));
    }

    private function product(string $id, string $name, string $size, int $price, string $category = 'Coffee'): array
    {
        return [
            'id_produk' => $id,
            'nama' => $name,
            'kategori' => $category,
            'kategori_dataset' => $category,
            'tipe_dataset' => 'Brewed coffee',
            'ukuran' => $size,
            'harga' => $price,
            'stok' => 100,
        ];
    }
}
