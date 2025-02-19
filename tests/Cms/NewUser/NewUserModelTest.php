<?php

namespace Kirby\Cms;

use Kirby\Cms\NewUser as User;
use PHPUnit\Framework\Attributes\CoversClass;

class NewUserTestModel extends User
{
}

#[CoversClass(User::class)]
class NewUserModelTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewUserModelTest';

	public function testUserModel(): void
	{
		User::$models = [
			'dummy' => NewUserTestModel::class
		];

		$user = User::factory([
			'slug'  => 'test',
			'model' => 'dummy'
		]);

		$this->assertInstanceOf(NewUserTestModel::class, $user);

		User::$models = [];
	}
}
