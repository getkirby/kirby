<?php

namespace Kirby\Exception;

class PermissionExceptionTest extends \PHPUnit\Framework\TestCase
{

    public function testDefaults()
    {
        $exception = new PermissionException();

        $this->assertEquals('exception.permission.missing', $exception->getKey());
        $this->assertEquals('Missing required permission', $exception->getMessage());
        $this->assertEquals(403, $exception->getHttpCode());
        $this->assertEquals([], $exception->getData());
    }

}
