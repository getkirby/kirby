<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserLanguageTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserLanguage';

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
