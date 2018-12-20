<?php

namespace Kirby\Exception;

class DuplicateExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testDefaults()
    {
        $exception = new DuplicateException();
        $this->assertEquals('error.duplicate', $exception->getKey());
        $this->assertEquals('The entry exists', $exception->getMessage());
        $this->assertEquals(400, $exception->getHttpCode());
    }
}
