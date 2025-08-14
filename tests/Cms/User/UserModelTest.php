<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

class UserTestModel extends User
{
}

#[CoversClass(User::class)]
class UserModelTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.UserModel';

	public function setUp(): void
	{
		parent::setUp();
		User::$models = [];
	}

	public function tearDown(): void
	{
		parent::tearDown();
		User::$models = [];
	}

	public function testModel(): void
	{
		User::extendModels([
			'dummy' => UserTestModel::class
		]);

		$user = User::factory([
			'slug'  => 'test',
			'model' => 'dummy'
		]);

		$this->assertInstanceOf(UserTestModel::class, $user);
	}

	public function testModelWithUppercaseKey(): void
	{
		User::extendModels([
			'Dummy' => UserTestModel::class
		]);

		$user = User::factory([
			'slug'  => 'test',
			'model' => 'dummy'
		]);

		$this->assertInstanceOf(UserTestModel::class, $user);
	}

}
