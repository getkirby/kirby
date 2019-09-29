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
        $this->assertSame('error.permission', $exception->getKey());
        $this->assertSame('You are not allowed to do this', $exception->getMessage());
        $this->assertSame(403, $exception->getHttpCode());
    }
}
