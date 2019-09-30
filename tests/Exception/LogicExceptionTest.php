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
        $this->assertSame('error.logic', $exception->getKey());
        $this->assertSame('This task cannot be finished', $exception->getMessage());
        $this->assertSame(400, $exception->getHttpCode());
    }
}
