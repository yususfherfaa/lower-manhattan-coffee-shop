<?php

if (!function_exists('catalog_product_size_label')) {
    function catalog_product_size_label(array $product): ?string
    {
        $explicitLabel = trim((string) ($product['ukuran_label'] ?? ''));
        if (in_array($explicitLabel, ['Small', 'Regular', 'Large'], true)) {
            return $explicitLabel;
        }

        $size = strtoupper(trim((string) ($product['ukuran'] ?? '')));
        if ($size === '' || $size === 'NONE') {
            if (preg_match('/\s*\((SM|RG|LG)\)\s*$/i', (string) ($product['nama'] ?? ''), $matches)) {
                $size = strtoupper((string) $matches[1]);
            } elseif (preg_match('/\s+-\s+(Small|Regular|Large)\s*$/i', (string) ($product['nama'] ?? ''), $matches)) {
                return ucfirst(strtolower((string) $matches[1]));
            }
        }

        return [
            'SM' => 'Small',
            'RG' => 'Regular',
            'LG' => 'Large',
        ][$size] ?? null;
    }
}

if (!function_exists('catalog_product_display_name')) {
    function catalog_product_display_name(array $product): string
    {
        return trim((string) preg_replace(
            '/\s*(?:\((?:SM|RG|LG)\)|-\s*(?:Small|Regular|Large))\s*$/i',
            '',
            (string) ($product['nama'] ?? '')
        ));
    }
}

if (!function_exists('group_catalog_products_by_size')) {
    function group_catalog_products_by_size(array $products): array
    {
        $candidates = [];

        foreach ($products as $product) {
            $size = strtoupper(trim((string) ($product['ukuran'] ?? '')));
            $name = trim((string) ($product['nama'] ?? ''));
            $baseName = preg_replace('/\s*\((?:SM|RG|LG)\)\s*$/i', '', $name);
            $category = (string) ($product['kategori_dataset'] ?? $product['kategori'] ?? '');
            $type = (string) ($product['tipe_dataset'] ?? '');
            $key = mb_strtolower($category . '|' . $type . '|' . $baseName);

            $candidates[$key][] = [
                'product' => $product,
                'name' => $baseName,
                'size' => in_array($size, ['SM', 'RG', 'LG'], true) ? $size : '',
            ];
        }

        $groups = [];
        foreach ($candidates as $items) {
            $hasSizeVariant = count(array_filter(
                $items,
                static fn(array $item): bool => $item['size'] !== ''
            )) > 0;
            $isSizeGroup = count($items) > 1 && $hasSizeVariant;

            if (!$isSizeGroup) {
                foreach ($items as $item) {
                    $groups[] = [
                        'name' => (string) ($item['product']['nama'] ?? ''),
                        'product' => $item['product'],
                        'image_product' => $item['product'],
                        'variants' => [],
                        'is_size_group' => false,
                    ];
                }
                continue;
            }

            $variants = [];
            $hasExplicitRegular = count(array_filter(
                $items,
                static fn(array $item): bool => $item['size'] === 'RG'
            )) > 0;
            foreach ($items as $item) {
                $size = $item['size'] !== '' ? $item['size'] : ($hasExplicitRegular ? 'STD' : 'RG');
                $variants[$size] = [
                    'label' => ['SM' => 'Small', 'STD' => 'Standard', 'RG' => 'Regular', 'LG' => 'Large'][$size],
                    'product' => $item['product'],
                ];
            }

            $orderedVariants = [];
            foreach (['SM', 'STD', 'RG', 'LG'] as $size) {
                if (isset($variants[$size])) {
                    $orderedVariants[] = $variants[$size];
                }
            }

            $defaultVariant = $variants['RG'] ?? $orderedVariants[0];
            $imageProduct = $defaultVariant['product'];
            foreach ($orderedVariants as $variant) {
                if (function_exists('product_image_is_custom')
                    && product_image_is_custom($variant['product']['gambar'] ?? null)) {
                    $imageProduct = $variant['product'];
                    break;
                }
            }

            $groups[] = [
                'name' => $items[0]['name'],
                'product' => $defaultVariant['product'],
                'image_product' => $imageProduct,
                'variants' => $orderedVariants,
                'is_size_group' => true,
            ];
        }

        return $groups;
    }
}
