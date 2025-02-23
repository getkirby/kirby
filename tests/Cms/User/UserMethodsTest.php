<?php

namespace Kirby\Cms;


use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserMethodsTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserMethods';

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
