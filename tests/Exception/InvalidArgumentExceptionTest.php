<?php

namespace Kirby\Exception;

use PHPUnit\Framework\TestCase;

class InvalidArgumentExceptionTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testDefaults()
    {
        $exception = new InvalidArgumentException();
        $this->assertSame('error.invalidArgument', $exception->getKey());
        $this->assertSame('Invalid argument "-" in method "-"', $exception->getMessage());
        $this->assertSame(400, $exception->getHttpCode());
        $this->assertSame(['argument' => null, 'method' => null], $exception->getData());
    }

    /**
     * @coversNothing
     */
    public function testPlaceholders()
    {
        $exception = new InvalidArgumentException([
            'data' => [
                'argument' => 'key',
                'method' => 'get'
            ]
        ]);
        $this->assertSame('Invalid argument "key" in method "get"', $exception->getMessage());
        $this->assertSame([
            'argument' => 'key',
            'method' => 'get'
        ], $exception->getData());
    }
}
