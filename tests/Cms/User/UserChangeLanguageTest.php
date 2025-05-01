<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserChangeLanguageTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserChangeLanguage';

	public function testChangeLanguage(): void
	{
		$user = new User(['email' => 'editor@domain.com']);
		$user = $user->changeLanguage('de');

		$this->assertSame('de', $user->language());
	}

	public function testChangeLanguageHooks(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.changeLanguage:before' => function (User $user, $language) use ($phpunit, &$calls) {
					$phpunit->assertSame('en', $user->language());
					$phpunit->assertSame('de', $language);
					$calls++;
				},
				'user.changeLanguage:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
					$phpunit->assertSame('de', $newUser->language());
					$phpunit->assertSame('en', $oldUser->language());
					$calls++;
				}
			]
		]);

		$this->app->impersonate('kirby');

		$user = new User(['email' => 'editor@domain.com']);
		$user->changeLanguage('de');

		$this->assertSame(2, $calls);
	}
}
