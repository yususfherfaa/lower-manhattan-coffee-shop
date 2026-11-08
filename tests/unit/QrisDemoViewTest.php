<?php

use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class QrisDemoViewTest extends CIUnitTestCase
{
    public function testDemoModeRendersTheDemoImageAndWarning(): void
    {
        $html = view('customer/payment_qris', [
            'nama_toko' => 'Lower Manhattan Coffee Shop',
            'alamat_lengkap' => '',
            'nmid' => null,
            'qris' => 'uploads/qris-demo-lowermanhattan.png',
            'qris_mode' => 'demo',
            'is_demo' => true,
            'order' => [
                'id_order' => 'TEST000001',
                'id_meja' => 'MEJA001',
                'total' => 10000,
            ],
            'order_token' => '',
        ]);

        $this->assertStringContainsString(
            'alt="Kode QR demo untuk Lower Manhattan Coffee Shop"',
            $html
        );
        $this->assertStringContainsString(
            'uploads/qris-demo-lowermanhattan.png',
            $html
        );
        $this->assertStringContainsString(
            'MODE DEMO — bukan pembayaran sungguhan',
            $html
        );
        $this->assertStringContainsString('width="2048"', $html);
        $this->assertStringContainsString('height="2896"', $html);
        $this->assertStringNotContainsString('Scan kode QR di atas dan bayarkan', $html);
    }
}
