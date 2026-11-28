<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Auth;
use Kirby\Auth\Challenges;
use Kirby\Auth\Method;
use Kirby\Auth\Methods;
use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\TestCase;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

class TestMethod extends Method
{
	public function authenticate(
		string|null $email,
		string|null $password = null,
		bool $long = false
	): User|Status {
		return new User(['email' => $email]);
	}

	public function has2FA(User $user): bool
	{
		return parent::has2FA($user);
	}

	public function loginOrChallenge(User $user, bool $long = false): User|Status
	{
		return parent::loginOrChallenge($user, $long);
	}
}

#[CoversClass(Method::class)]
class MethodTest extends TestCase
{
	protected function method(
		string $type = 'test',
		Auth|null $auth = null
	): TestMethod {
		return new TestMethod(
			auth: $auth ?? $this->createStub(Auth::class),
			type: $type
		);
	}

	public static function has2FAProvider(): array
	{
		return [
			'not needed'          => [false, false, false],
			'required'            => [true, false, true],
			'set up'              => [false, true, true],
			'required and set up' => [true, true, true],
		];
	}

	#[DataProvider('has2FAProvider')]
	public function testHas2FA(
		bool $required,
		bool $available,
		bool $expected
	): void {
		$auth = $this->createConfiguredStub(Auth::class, [
			'challenges' => $this->createConfiguredStub(Challenges::class, [
				'hasAvailable' => $available
			]),
			'methods' => $this->createConfiguredStub(Methods::class, [
				'hasAnyRequiring2FA' => $required
			]),
		]);

		$user = new User(['email' => 'marge@simpsons.com']);
		$this->assertSame($expected, $this->method(auth: $auth)->has2FA($user));
	}

	public function testIcon(): void
	{
		$this->assertSame('key', $this->method()->icon());
	}

	public function testIsEnabled(): void
	{
		$this->assertTrue($this->method()->isEnabled());
	}

	public function testLabel(): void
	{
		I18n::$translations = [
			'en' => ['login.method.oauth-github.label' => 'GitHub']
		];

		$this->assertSame('GitHub', $this->method('oauth-github')->label());
		$this->assertSame('login.method.test.label', $this->method()->label());
	}

	public function testLoginOrChallenge(): void
	{
		$user = $this->createMock(User::class);
		$user->expects($this->once())
			->method('loginPasswordless')
			->with(['createMode' => 'cookie', 'long' => true]);

		$auth = $this->createMock(Auth::class);
		$auth->expects($this->never())->method('createChallenge');

		$this->assertSame($user, $this->method(auth: $auth)->loginOrChallenge($user, true));
	}

	public function testLoginOrChallengeWith2FA(): void
	{
		$user = $this->createMock(User::class);
		$user->method('email')->willReturn('marge@simpsons.com');
		$user->expects($this->never())->method('loginPasswordless');

		$status = $this->createStub(Status::class);
		$auth   = $this->createMock(Auth::class);
		$auth->method('methods')->willReturn(
			$this->createConfiguredStub(Methods::class, [
				'hasAnyRequiring2FA' => true
			])
		);
		$auth->expects($this->once())
			->method('createChallenge')
			->with('marge@simpsons.com', true, '2fa')
			->willReturn($status);

		$this->assertSame($status, $this->method(auth: $auth)->loginOrChallenge($user, true));
	}

	public function testType(): void
	{
		$this->assertSame('oauth-github', $this->method('oauth-github')->type());
	}
}
