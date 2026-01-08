<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Method;
use Kirby\Auth\Methods;
use Kirby\Auth\Status;
use Kirby\Cms\Auth;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Method::class)]
#[CoversClass(CodeMethod::class)]
class CodeMethodTest extends TestCase
{
	protected function auth(
		bool $has2fa = false,
		bool $hasPasswordReset = false
	): Auth {
		$methods = $this->createStub(Methods::class);
		$config = ['password' => []];

		if ($has2fa === true) {
			$config['password']['2fa'] = true;
		}

		if ($hasPasswordReset === true) {
			$config['password-reset'] = [];
		}

		$methods->method('config')->willReturn($config);

		$auth = $this->createStub(Auth::class);
		$auth->method('methods')->willReturn($methods);

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

	public function testIsEnabled(): void
	{
		$auth = $this->auth();
		$this->assertTrue(CodeMethod::isEnabled($auth));
	}

	public function testIsEnabledWith2F(): void
	{
		$auth = $this->auth(has2fa: true);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "code" login method cannot be enabled when 2FA is required');

		CodeMethod::isEnabled($auth);
	}

	public function testIsEnabledWithPasswordReset(): void
	{
		$auth = $this->auth(hasPasswordReset: true);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "code" and "password-reset" login methods cannot be enabled together');

		CodeMethod::isEnabled($auth);
	}

	public function testIsUsingChallenges(): void
	{
		$auth = $this->auth();
		$this->assertTrue(CodeMethod::isUsingChallenges($auth));
	}

	public function testType(): void
	{
		$this->assertSame('code', CodeMethod::type());
	}
}
