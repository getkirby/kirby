<?php

namespace Kirby\Cms;

use Kirby\Cms\NewUser as User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class NewUserRoleTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewUserRole';

	public function testIsAdmin(): void
	{
		$user = new User([
			'email' => 'test@getkirby.com',
			'role'  => 'admin'
		]);

		$this->assertTrue($user->isAdmin());

		$user = new User([
			'email' => 'test@getkirby.com',
			'role'  => 'editor'
		]);

		$this->assertFalse($user->isAdmin());
	}

	public function testIsKirby(): void
	{
		$user = new User([
			'id'   => 'kirby',
			'role' => 'admin'
		]);
		$this->assertTrue($user->isKirby());

		$user = new User([
			'role' => 'admin'
		]);
		$this->assertFalse($user->isKirby());

		$user = new User([
			'id'   => 'kirby',
		]);
		$this->assertFalse($user->isKirby());

		$user = new User([
			'emai' => 'kirby@getkirby.com',
		]);
		$this->assertFalse($user->isKirby());
	}

	public function testIsNobody(): void
	{
		$user = new User([
			'id'   => 'nobody',
			'role' => 'nobody'
		]);
		$this->assertTrue($user->isNobody());

		$user = new User([
			'role' => 'nobody'
		]);
		$this->assertFalse($user->isNobody());

		$user = new User([
			'id' => 'nobody',
		]);
		$this->assertTrue($user->isNobody());
	}

	public function testRole(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor'],
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				],
				[
					'email' => 'foo@getkirby.com',
					'role'  => 'foo'
				]
			],
		]);

		$user  = $this->app->user('editor@getkirby.com');
		$this->assertSame('editor', $user->role()->id());

		$user  = $this->app->user('admin@getkirby.com');
		$this->assertSame('admin', $user->role()->id());

		// non-existing role
		$user  = $this->app->user('foo@getkirby.com');
		$this->assertSame('nobody', $user->role()->id());
	}
}
