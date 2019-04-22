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
        $this->assertEquals('error.invalidArgument', $exception->getKey());
        $this->assertEquals('Invalid argument "-" in method "-"', $exception->getMessage());
        $this->assertEquals(400, $exception->getHttpCode());
        $this->assertEquals(['argument' => null, 'method' => null], $exception->getData());
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
        $this->assertEquals('Invalid argument "key" in method "get"', $exception->getMessage());
        $this->assertEquals([
            'argument' => 'key',
            'method' => 'get'
        ], $exception->getData());
    }
}
