<?php

namespace Kirby\Cms;

use Kirby\Cms\NewUser as User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class NewUserChangeRoleTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewUserChangeRoleTest';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor'],
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testChangeRole(): void
	{
		$user = new User(['email' => 'editor@domain.com']);
		$user = $user->changeRole('editor');

		$this->assertInstanceOf(Role::class, $user->role());
		$this->assertSame('editor', $user->role()->name());
	}

	public function testChangeRoleHooks(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.changeRole:before' => function (User $user, $role) use ($phpunit, &$calls) {
					$phpunit->assertSame('editor', $user->role()->name());
					$phpunit->assertSame('admin', $role);
					$calls++;
				},
				'user.changeRole:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
					$phpunit->assertSame('admin', $newUser->role()->name());
					$phpunit->assertSame('editor', $oldUser->role()->name());
					$calls++;
				}
			]
		]);

		$this->app->impersonate('kirby');

		$user = new User([
			'email' => 'editor@domain.com',
			'role'  => 'editor'
		]);
		$user->changeRole('admin');

		$this->assertSame(2, $calls);
	}
}
