<?php

use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class HealthTest extends CIUnitTestCase
{
    public function testTestingEnvironmentIsInitialized(): void
    {
        $this->assertSame('testing', ENVIRONMENT);
        $this->assertTrue(defined('APPPATH'));
    }
}
