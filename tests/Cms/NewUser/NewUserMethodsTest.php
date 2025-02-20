<?php

namespace Kirby\Cms;

use Kirby\Cms\NewUser as User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class NewUserMethodsTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewUserMethods';

	public function testUserMethods(): void
	{
		User::$methods = [
			'test' => fn () => 'homer'
		];

		$user = new User([
			'email' => 'test@getkirby.com',
			'name'  => 'Test User'
		]);

		$this->assertSame('homer', $user->test());

		User::$methods = [];
	}
}
