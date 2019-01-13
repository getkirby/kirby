<?php

namespace Kirby\Exception;

class InvalidArgumentExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testDefaults()
    {
        $exception = new InvalidArgumentException();
        $this->assertEquals('error.invalidArgument', $exception->getKey());
        $this->assertEquals('Invalid argument "-" in method "-"', $exception->getMessage());
        $this->assertEquals(400, $exception->getHttpCode());
        $this->assertEquals(['argument' => null, 'method' => null], $exception->getData());
    }

    public function testPlaceholders()
    {
        $exception = new InvalidArgumentException([
            'data' => [
                'argument' => 'key',
                'method' => 'get'
            ]
        ]);
        $this->assertEquals('Invalid argument "key" in method "get"', $exception->getMessage());
    }
}
