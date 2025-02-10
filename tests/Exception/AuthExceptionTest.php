<?php

namespace Kirby\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

class AuthExceptionTest extends TestCase
{
	#[CoversNothing]
	public function testDefaults()
	{
		$exception = new AuthException();
		$this->assertSame('error.auth', $exception->getKey());
		$this->assertSame('Unauthenticated', $exception->getMessage());
		$this->assertSame(401, $exception->getHttpCode());
	}
}
