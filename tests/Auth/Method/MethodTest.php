<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Auth;
use Kirby\Auth\Method;
use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\TestCase;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

class TestMethod extends Method
{
	public function authenticate(
		string|null $email,
		string|null $password = null,
		bool $long = false
	): User|Status {
		return new User(['email' => $email]);
	}
}

#[CoversClass(Method::class)]
class MethodTest extends TestCase
{
	protected function method(string $type = 'test'): Method
	{
		return new TestMethod(
			auth: $this->createStub(Auth::class),
			type: $type
		);
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

	public function testType(): void
	{
		$this->assertSame('oauth-github', $this->method('oauth-github')->type());
	}
}
