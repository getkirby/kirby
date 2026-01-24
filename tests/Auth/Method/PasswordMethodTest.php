<?php

namespace Kirby\Auth\Method;

use InvalidArgumentException;
use Kirby\Auth\Auth;
use Kirby\Auth\Challenges;
use Kirby\Auth\Method;
use Kirby\Auth\Status;
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

	public function testAuthenticateWithOptional2FAWithoutChallenge(): void
	{
		$login = [];
		$user  = $this->createStub(User::class);
		$user->method('loginPasswordless')
			->willReturnCallback(function ($options) use (&$login) {
				$login[] = $options;
			});

		$validate   = null;
		$challenges = $this->createStub(Challenges::class);
		$challenges->method('available')->willReturn([]);

		$auth = $this->createStub(Auth::class);
		$auth->method('validatePassword')
			->willReturnCallback(function (...$args) use (&$validate, $user) {
				$validate = $args;
				return $user;
			});
		$auth->method('challenges')->willReturn($challenges);
		$auth->method('createChallenge')
			->willReturnCallback(function () {
				throw new RuntimeException('createChallenge should not be called');
			});

		$method = new PasswordMethod(auth: $auth, options: ['2fa' => 'optional']);
		$result = $method->authenticate('marge@simpsons.com', 'springfield123', true);

		$this->assertInstanceOf(User::class, $result);
		$this->assertSame($user, $result);
		$this->assertSame(['marge@simpsons.com', 'springfield123'], $validate);
		$this->assertSame([[
			'createMode' => 'cookie',
			'long'       => true
		]], $login);
	}

	public function testAuthenticateWithOptional2FAWithChallenge(): void
	{
		$status       = $this->createStub(Status::class);
		$user         = $this->createStub(User::class);
		$validate     = null;
		$challenge    = null;
		$challenges   = $this->createStub(Challenges::class);
		$challenges->method('available')->willReturn(['totp']);

		$auth = $this->createStub(Auth::class);
		$auth->method('validatePassword')
			->willReturnCallback(function (...$args) use (&$validate, $user) {
				$validate = $args;
				return $user;
			});
		$auth->method('challenges')->willReturn($challenges);
		$auth->method('createChallenge')
			->willReturnCallback(function (...$args) use (&$challenge, $status) {
				$challenge = $args;
				return $status;
			});

		$method = new PasswordMethod(auth: $auth, options: ['2fa' => 'optional']);
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

	public function testIcon(): void
	{
		$icon = PasswordMethod::icon();
		$this->assertSame('key', $icon);
	}

	public function testIsEnabled(): void
	{
		$auth = $this->createStub(Auth::class);
		$this->assertTrue(PasswordMethod::isEnabled($auth));
	}

	public function testIsUsingChallenges(): void
	{
		$auth = $this->createStub(Auth::class);

		$this->assertFalse(PasswordMethod::isUsingChallenges($auth));
		$this->assertTrue(PasswordMethod::isUsingChallenges($auth, ['2fa' => true]));
		$this->assertTrue(PasswordMethod::isUsingChallenges($auth, ['2fa' => 'optional']));
	}

	public function testOptions(): void
	{
		$auth   = $this->createStub(Auth::class);
		$method = new PasswordMethod(auth: $auth, options: ['2fa' => true]);

		$this->assertSame(['2fa' => true], $method->options());
	}

	public function testSettings(): void
	{
		$user     = $this->createStub(User::class);
		$settings = PasswordMethod::settings($user);
		$this->assertCount(1, $settings);
	}

	public function testType(): void
	{
		$this->assertSame('password', PasswordMethod::type());
	}
}
