<?php

namespace Kirby\Exception;

use PHPUnit\Framework\TestCase;

class LogicExceptionTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testDefaults()
    {
        $exception = new LogicException();
        $this->assertEquals('error.logic', $exception->getKey());
        $this->assertEquals('This task cannot be finished', $exception->getMessage());
        $this->assertEquals(400, $exception->getHttpCode());
    }
}
