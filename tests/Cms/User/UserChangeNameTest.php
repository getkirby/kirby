<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserChangeNameTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.UserChangeName';

	public function testChangeName(): void
	{
		$user = new User(['email' => 'editor@domain.com']);
		$user = $user->changeName('Edith Thor');

		$this->assertSame('Edith Thor', $user->name()->value());
	}

	public function testChangeNameHooks(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.changeName:before' => function (User $user, $name) use ($phpunit, &$calls) {
					$phpunit->assertNull($user->name()->value());
					$phpunit->assertSame('Edith Thor', $name);
					$calls++;
				},
				'user.changeName:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
					$phpunit->assertSame('Edith Thor', $newUser->name()->value());
					$phpunit->assertNull($oldUser->name()->value());
					$calls++;
				}
			]
		]);

		$this->app->impersonate('kirby');

		$user = new User(['email' => 'editor@domain.com']);
		$user->changeName('Edith Thor');

		$this->assertSame(2, $calls);
	}
}
