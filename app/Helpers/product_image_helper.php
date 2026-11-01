<?php

if (!function_exists('product_image_is_custom')) {
    function product_image_is_custom(?string $image): bool
    {
        $categoryPlaceholders = [
            'bakery.svg',
            'branded.svg',
            'coffee.svg',
            'coffee-beans.svg',
            'drinking-chocolate.svg',
            'flavours.svg',
            'loose-tea.svg',
            'packaged-chocolate.svg',
            'tea.svg',
            'no-image.svg',
            '1782892671_5d17140debb9adc1a0f7.jpg',
            '1782892739_5710e7e00431f6e601b9.jpg',
            '1782892849_cca365c575d52226cdfc.jpeg',
            '1782892953_7355e2cffff88b69963b.png',
        ];

        $filename = basename(str_replace('\\', '/', trim((string) $image)));
        return $filename !== ''
            && !str_starts_with($filename, 'catalog-')
            && !in_array($filename, $categoryPlaceholders, true)
            && is_file(FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $filename);
    }
}

if (!function_exists('product_image_url')) {
    function product_image_url(?string $image, ?string $category = null): string
    {
        $categoryImages = [
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

        if (product_image_is_custom($image)) {
            $filename = basename(str_replace('\\', '/', trim((string) $image)));

            return base_url('uploads/' . rawurlencode($filename));
        }

        $categoryImage = $categoryImages[$category ?? ''] ?? null;
        if ($categoryImage !== null && is_file(FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $categoryImage)) {
            return base_url('uploads/' . $categoryImage);
        }

        return base_url('assets/no-image.svg');
    }
}
