<?php

namespace Kirby\Exception;

class LogicExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testDefaults()
    {
        $exception = new LogicException();
        $this->assertEquals('error.logic', $exception->getKey());
        $this->assertEquals('This task cannot be finished', $exception->getMessage());
        $this->assertEquals(400, $exception->getHttpCode());
    }
}
