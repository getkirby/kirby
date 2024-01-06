<?php

namespace Kirby\Http\Exceptions;

use Kirby\TestCase;

class NextRouteExceptionTest extends TestCase
{
	/**
	 * @coversNothing
	 */
	public function testException()
	{
		$exception = new NextRouteException('test');
		$this->assertInstanceOf(\Exception::class, $exception);
		$this->assertSame('test', $exception->getMessage());
	}
}
