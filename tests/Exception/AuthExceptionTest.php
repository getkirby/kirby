<?php

namespace Kirby\Exception;

use Kirby\TestCase;

class AuthExceptionTest extends TestCase
{
	/**
	 * @coversNothing
	 */
	public function testDefaults()
	{
		$exception = new AuthException();
		$this->assertSame('error.auth', $exception->getKey());
		$this->assertSame('Unauthenticated', $exception->getMessage());
		$this->assertSame(401, $exception->getHttpCode());
	}
}
