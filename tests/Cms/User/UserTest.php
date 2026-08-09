<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.User';

	public function testAvatar(): void
	{
		$user = new User([
			'email' => 'user@domain.com'
		]);

		$this->assertNull($user->avatar());
	}

	public function testCredentials(): void
	{
		$user = new User([
			'credentials' => $credentials = [
				'role' => 'editor'
			]
		]);

		$this->assertSame($credentials, $user->credentials());
	}

	public function testEnsure(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$this->assertSame($this->app->user(), User::ensure());
	}

	public function testEnsureWithKirbyUser(): void
	{
		$this->app->impersonate('kirby');

		$user = User::ensure();

		$this->assertTrue($user->isKirby());
		$this->assertSame('kirby', $user->id());
	}

	public function testEnsureWithoutAuthenticatedUser(): void
	{
		$this->app->impersonate(null);

		$this->assertNull($this->app->user());

		$user = User::ensure();

		$this->assertTrue($user->isNobody());
		$this->assertSame('nobody', $user->id());
		$this->assertSame('nobody', $user->role()->id());
		$this->assertSame('nobody@getkirby.com', $user->email());
	}

	public function testEnsureWithoutAuthenticatedUserReturnsNewInstance(): void
	{
		$this->app->impersonate(null);

		// the virtual nobody user is not stored anywhere,
		// so every call has to create it from scratch
		$this->assertNotSame(User::ensure(), User::ensure());
	}

	public function testId(): void
	{
		$user = new User([
			'id'    => 'test',
			'email' => 'user@domain.com'
		]);
		$this->assertSame('test', $user->id());
	}

	public function testSiblings(): void
	{
		$user = new User(['email' => 'user@domain.com']);
		$this->assertInstanceOf(Users::class, $user->siblings());
		$this->assertCount(0, $user->siblings());
	}

	public function testToString(): void
	{
		$user = new User([
			'email' => 'test@getkirby.com'
		]);

		$this->assertSame('test@getkirby.com', $user->toString());
	}

	public function testToStringWithTemplate(): void
	{
		$user = new User([
			'email' => 'test@getkirby.com'
		]);

		$this->assertSame(
			'Email: test@getkirby.com',
			$user->toString('Email: {{ user.email }}')
		);
	}

	public function testQuery(): void
	{
		$user = new User([
			'email' => 'test@getkirby.com',
			'name'  => 'Test User'
		]);

		$this->assertSame('Test User', $user->query('user.name')->value());
		$this->assertSame('test@getkirby.com', $user->query('user.email'));

		// also test with `model` key
		$this->assertSame('Test User', $user->query('model.name')->value());
		$this->assertSame('test@getkirby.com', $user->query('model.email'));
	}
}
