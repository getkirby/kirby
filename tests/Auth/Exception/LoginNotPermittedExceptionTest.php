<?php

namespace Kirby\Auth\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LoginNotPermittedException::class)]
class LoginNotPermittedExceptionTest extends TestCase
{
	public function testDefaults(): void
	{
		$exception = new LoginNotPermittedException();
		$this->assertSame('error.access.login', $exception->getKey());
		$this->assertSame('Invalid login', $exception->getMessage());
		$this->assertSame(403, $exception->getHttpCode());
	}
}
