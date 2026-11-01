<?php

use App\Libraries\PaymentRules;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class PaymentRulesTest extends CIUnitTestCase
{
    public function testCashPaymentCalculatesChange(): void
    {
        $this->assertSame(5000, PaymentRules::change(25000, 20000));
    }

    public function testPaidOrderCannotBePaidAgain(): void
    {
        $this->assertFalse(PaymentRules::canPay('lunas', false));
        $this->assertFalse(PaymentRules::canPay('menunggu_pembayaran', true));
        $this->assertTrue(PaymentRules::canPay('menunggu_pembayaran', false));
    }
}
