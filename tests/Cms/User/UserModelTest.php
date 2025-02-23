<?php

namespace Kirby\Cms;


use PHPUnit\Framework\Attributes\CoversClass;

class UserTestModel extends User
{
}

#[CoversClass(User::class)]
class UserModelTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserModel';

	public function testUserModel(): void
	{
		User::$models = [
			'dummy' =>UserTestModel::class
		];

		$user = User::factory([
			'slug'  => 'test',
			'model' => 'dummy'
		]);

		$this->assertInstanceOf(UserTestModel::class, $user);

		User::$models = [];
	}
}
