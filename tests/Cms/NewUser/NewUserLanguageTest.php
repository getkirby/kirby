<?php

namespace Kirby\Cms;

use Kirby\Cms\NewUser as User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class NewUserLanguageTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewUserLanguageTest';

	public function testLanguage(): void
	{
		$user = new User([
			'email'    => 'user@domain.com',
			'language' => 'en',
		]);

		$this->assertSame('en', $user->language());
	}

	public function testDefaultLanguage(): void
	{
		$user = new User([
			'email' => 'user@domain.com',
		]);

		$this->assertSame('en', $user->language());
	}
}
