<?php

namespace Kirby\Exception;

use PHPUnit\Framework\TestCase;

class PermissionExceptionTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testDefaults()
    {
        $exception = new PermissionException();
        $this->assertEquals('error.permission', $exception->getKey());
        $this->assertEquals('You are not allowed to do this', $exception->getMessage());
        $this->assertEquals(403, $exception->getHttpCode());
    }
}
