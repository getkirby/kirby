<?php

namespace Kirby\Exception;

class BadMethodCallExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testDefaults()
    {
        $exception = new BadMethodCallException();
        $this->assertEquals('error.invalidMethod', $exception->getKey());
        $this->assertEquals('The method "-" does not exist', $exception->getMessage());
        $this->assertEquals(400, $exception->getHttpCode());
        $this->assertEquals(['method' => null], $exception->getData());
    }

    public function testPlaceholders()
    {
        $exception = new BadMethodCallException([
            'data' => [
                'method' => 'get'
            ]
        ]);
        $this->assertEquals('The method "get" does not exist', $exception->getMessage());
    }
}
