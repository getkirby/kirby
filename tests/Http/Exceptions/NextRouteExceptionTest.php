<?php

namespace Kirby\Http\Exceptions;

use PHPUnit\Framework\TestCase;

class NextRouteExceptionTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testException()
    {
        $exception = new NextRouteException('test');
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('test', $exception->getMessage());
    }
}
