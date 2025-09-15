<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserUpdateTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserUpdate';

	public function testUpdate(): void
	{
		$user = new User(['email' => 'editor@domain.com']);
		$user = $user->update([
			'website' => $url = 'https://editor.com'
		]);

		$this->assertSame($url, $user->website()->value());
	}

	public function testUpdateWithAuthUser(): void
	{
		$user = new User(['email' => 'admin@domain.com', 'role' => 'admin']);
		$user->loginPasswordless();
		$user->update([
			'website' => $url = 'https://getkirby.com'
		]);
		$this->assertSame($url, $this->app->user()->website()->value());
		$user->logout();
	}

	public function testUpdateHooks(): void
	{
		$calls = 0;
		$phpunit = $this;
		$input = [
			'website' => 'https://getkirby.com'
		];

		$this->app = $this->app->clone([
			'hooks' => [
				'user.update:before' => function (User $user, $values, $strings) use ($phpunit, $input, &$calls) {
					$phpunit->assertNull($user->website()->value());
					$phpunit->assertSame($input, $values);
					$phpunit->assertSame($input, $strings);
					$calls++;
				},
				'user.update:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
					$phpunit->assertSame('https://getkirby.com', $newUser->website()->value());
					$phpunit->assertNull($oldUser->website()->value());
					$calls++;
				}
			]
		]);

		$this->app->impersonate('kirby');

		$user = new User(['email' => 'editor@domain.com']);
		$user->update($input);

		$this->assertSame(2, $calls);
	}
}
