<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Auth\Methods;
use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\Auth\Status;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Method::class)]
#[CoversClass(CodeMethod::class)]
class CodeMethodTest extends TestCase
{
	protected function auth(
		bool $has2fa = false,
		bool $hasPasswordReset = false,
		bool $debug = false
	): Auth {
		$methods = $this->createStub(Methods::class);
		$methods->method('hasAnyWith2FA')->willReturn($has2fa);
		$methods->method('has')->willReturnCallback(
			fn (string $type) => $type === 'password-reset' ? $hasPasswordReset : false
		);

		$kirby = $this->createStub(App::class);
		$kirby->method('option')->willReturnCallback(
			fn (string $key) => $key === 'debug' ? $debug : null
		);

		$auth = $this->createStub(Auth::class);
		$auth->method('methods')->willReturn($methods);
		$auth->method('kirby')->willReturn($kirby);

		return $auth;
	}

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

		$method = new CodeMethod(auth: $auth);

		$result = $method->authenticate('marge@simpsons.com', long: true);
		$this->assertInstanceOf(Status::class, $result);
		$this->assertSame($status, $result);
		$this->assertSame(['marge@simpsons.com', true, 'login'], $args);

		$result = $method->authenticate('lisa@simpsons.com', long: false);
		$this->assertSame($status, $result);
		$this->assertSame(['lisa@simpsons.com', false, 'login'], $args);
	}

	public function testIsAvailable(): void
	{
		$auth = $this->auth();
		$this->assertTrue(CodeMethod::isAvailable($auth));
	}

	public function testIsAvailableWith2FA(): void
	{
		$auth = $this->auth(has2fa: true);
		$this->assertFalse(CodeMethod::isAvailable($auth));
	}

	public function testIsAvailableWith2FADebug(): void
	{
		$auth = $this->auth(has2fa: true, debug: true);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "code" login method cannot be enabled when 2FA is required');

		CodeMethod::isAvailable($auth);
	}

	public function testIsAvailableWithPasswordReset(): void
	{
		$auth = $this->auth(hasPasswordReset: true);
		$this->assertFalse(CodeMethod::isAvailable($auth));
	}

	public function testIsAvailableWithPasswordResetDebug(): void
	{
		$auth = $this->auth(hasPasswordReset: true, debug: true);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "code" and "password-reset" login methods cannot be enabled together');

		CodeMethod::isAvailable($auth);
	}

	public function testType(): void
	{
		$this->assertSame('code', CodeMethod::type());
	}
}
