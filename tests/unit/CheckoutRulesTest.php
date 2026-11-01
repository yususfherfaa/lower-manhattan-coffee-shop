<?php

use App\Libraries\CheckoutRules;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class CheckoutRulesTest extends CIUnitTestCase
{
    public function testCheckoutAcceptsStockAtRequestedQuantity(): void
    {
        $this->assertTrue(CheckoutRules::hasEnoughStock(3, 3));
    }

    public function testCheckoutRejectsInsufficientStock(): void
    {
        $this->assertFalse(CheckoutRules::hasEnoughStock(4, 3));
    }

    public function testCheckoutRejectsInvalidPaymentMethod(): void
    {
        $this->assertFalse(CheckoutRules::isValidPaymentMethod('transfer'));
        $this->assertTrue(CheckoutRules::isValidPaymentMethod('qris'));
    }
}
