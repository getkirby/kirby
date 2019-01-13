<?php

namespace Kirby\Exception;

class PermissionExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testDefaults()
    {
        $exception = new PermissionException();
        $this->assertEquals('error.permission', $exception->getKey());
        $this->assertEquals('You are not allowed to do this', $exception->getMessage());
        $this->assertEquals(403, $exception->getHttpCode());
    }
}
