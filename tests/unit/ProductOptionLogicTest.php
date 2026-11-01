<?php

namespace Tests\Unit;

use App\Controllers\Kasir\ProductOption;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\RedirectResponse;

/** @internal */
final class ProductOptionLogicTest extends CIUnitTestCase
{
    public function testEnsureKasirRejectsNonKasir(): void
    {
        service('session')->set('role', 'admin');
        $controller = new ProductOption();
        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('ensureKasir');
        $method->setAccessible(true);

        $result = $method->invoke($controller);
        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testEnsureKasirAllowsKasirRole(): void
    {
        service('session')->set('role', 'kasir');
        $controller = new ProductOption();
        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('ensureKasir');
        $method->setAccessible(true);

        $result = $method->invoke($controller);
        $this->assertNull($result);
    }
}
