<?php

namespace Kirby\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class AuthExceptionTest extends TestCase
{
	public function testDefaults(): void
	{
		$exception = new AuthException();
		$this->assertSame('error.auth', $exception->getKey());
		$this->assertSame('Unauthenticated', $exception->getMessage());
		$this->assertSame(401, $exception->getHttpCode());
	}
}
