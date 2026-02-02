<?php

namespace Kirby\Auth\Exception;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

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
