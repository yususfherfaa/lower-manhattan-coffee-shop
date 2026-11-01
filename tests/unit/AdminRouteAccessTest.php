<?php

use App\Filters\RoleFilter;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class AdminRouteAccessTest extends CIUnitTestCase
{
    public function testCashierIsRedirectedFromAdminRoleFilter(): void
    {
        service('session')->set('role', 'kasir');
        $filter = new RoleFilter();
        $request = $this->createMock(RequestInterface::class);

        $this->assertInstanceOf(RedirectResponse::class, $filter->before($request, ['admin']));
    }
}
