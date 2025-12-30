<?php

namespace Kirby\Auth\Method;

use InvalidArgumentException;
use Kirby\Auth\Method;
use Kirby\Cms\Auth;
use Kirby\Cms\Auth\Status;
use Kirby\Cms\User;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;

#[CoversClass(Method::class)]
#[CoversClass(PasswordMethod::class)]
class PasswordMethodTest extends TestCase
{
	public function testAuthenticate(): void
	{
		$login = [];
		$user  = $this->createStub(User::class);
		$user->method('loginPasswordless')
			->willReturnCallback(function ($options) use (&$login) {
				$login[] = $options;
			});

		$validate = null;
		$auth     = $this->createStub(Auth::class);
		$auth->method('validatePassword')
			->willReturnCallback(function (...$args) use (&$validate, $user) {
				$validate = $args;
				return $user;
			});
		$auth->method('createChallenge')
			->willReturnCallback(function () {
				throw new RuntimeException('createChallenge should not be called');
			});

		$method = new PasswordMethod(auth: $auth);
		$result = $method->authenticate('marge@simpsons.com', 'springfield123', true);

		$this->assertInstanceOf(User::class, $result);
		$this->assertSame($user, $result);
		$this->assertSame(['marge@simpsons.com', 'springfield123'], $validate);
		$this->assertSame([[
			'createMode' => 'cookie',
			'long'       => true
		]], $login);
	}

	public function testAuthenticateWith2FA(): void
	{
		$status       = $this->createStub(Status::class);
		$user         = $this->createStub(User::class);
		$validate     = null;
		$challenge    = null;
		$auth         = $this->createStub(Auth::class);
		$auth->method('validatePassword')
			->willReturnCallback(function (...$args) use (&$validate, $user) {
				$validate = $args;
				return $user;
			});
		$auth->method('createChallenge')
			->willReturnCallback(function (...$args) use (&$challenge, $status) {
				$challenge = $args;
				return $status;
			});

		$method = new PasswordMethod(auth: $auth, options: ['2fa' => true]);
		$result = $method->authenticate('marge@simpsons.com', 'springfield123');

		$this->assertSame($status, $result);
		$this->assertSame(['marge@simpsons.com', 'springfield123'], $validate);
		$this->assertSame(['marge@simpsons.com', false, '2fa'], $challenge);
	}

	public function testAuthenticateWithoutPassword(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Missing password');

		$auth   = $this->createStub(Auth::class);
		$method = new PasswordMethod(auth: $auth);
		$method->authenticate('marge@simpsons.com');
	}

	public function testType(): void
	{
		$this->assertSame('password', PasswordMethod::type());
	}
}
