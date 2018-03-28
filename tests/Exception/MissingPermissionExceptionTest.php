<?php

namespace Kirby\Exception;

class MissingPermissionExceptionTest extends \PHPUnit\Framework\TestCase
{

    public function testDefaults()
    {
        $exception = new MissingPermissionException();

        $this->assertEquals('exception.permission.missing', $exception->getKey());
        $this->assertEquals('Missing required permission', $exception->getMessage());
        $this->assertEquals(403, $exception->getCode());
        $this->assertEquals([], $exception->getData());
    }

}
