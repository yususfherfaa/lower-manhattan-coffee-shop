<?php

namespace App\Libraries;

final class PaymentRules
{
    public static function canPay(string $status, bool $hasPayment): bool
    {
        return !$hasPayment && $status === 'menunggu_pembayaran';
    }

    public static function change(int $paid, int $total): ?int
    {
        return $paid < $total ? null : $paid - $total;
    }
}
