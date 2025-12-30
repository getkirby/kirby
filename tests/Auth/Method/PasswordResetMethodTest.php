<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Cms\Auth;
use Kirby\Cms\Auth\Status;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Method::class)]
#[CoversClass(PasswordResetMethod::class)]
class PasswordResetMethodTest extends TestCase
{
	public function testAuthenticate(): void
	{
		$args   = null;
		$status = $this->createStub(Status::class);
		$auth   = $this->createStub(Auth::class);
		$auth->method('createChallenge')
			->willReturnCallback(function (...$x) use (&$args, $status) {
				$args = $x;
				return $status;
			});

		$method = new PasswordResetMethod(auth: $auth);

		$result = $method->authenticate('marge@simpsons.com');
		$this->assertInstanceOf(Status::class, $result);
		$this->assertSame($status, $result);
		$this->assertSame(['marge@simpsons.com', false, 'password-reset'], $args);

		// Password reset should ignore `long: true`
		$result = $method->authenticate('lisa@simpsons.com', long: true);
		$this->assertSame(['lisa@simpsons.com', false, 'password-reset'], $args);
	}

	public function testType(): void
	{
		$this->assertSame('password-reset', PasswordResetMethod::type());
	}
}
