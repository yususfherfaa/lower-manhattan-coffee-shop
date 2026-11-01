<?php

namespace App\Libraries;

final class CheckoutRules
{
    public static function isValidPaymentMethod(string $method): bool
    {
        return in_array($method, ['cash', 'qris'], true);
    }

    public static function hasEnoughStock(int $requested, int $available): bool
    {
        return $requested > 0 && $available >= 0 && $requested <= $available;
    }
}
