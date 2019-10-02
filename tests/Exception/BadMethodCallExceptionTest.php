<?php

namespace Kirby\Exception;

use PHPUnit\Framework\TestCase;

class BadMethodCallExceptionTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testDefaults()
    {
        $exception = new BadMethodCallException();
        $this->assertSame('error.invalidMethod', $exception->getKey());
        $this->assertSame('The method "-" does not exist', $exception->getMessage());
        $this->assertSame(400, $exception->getHttpCode());
        $this->assertSame(['method' => null], $exception->getData());
    }

    /**
     * @coversNothing
     */
    public function testPlaceholders()
    {
        $exception = new BadMethodCallException([
            'data' => [
                'method' => 'get'
            ]
        ]);
        $this->assertSame('The method "get" does not exist', $exception->getMessage());
        $this->assertSame(['method' => 'get'], $exception->getData());
    }
}
