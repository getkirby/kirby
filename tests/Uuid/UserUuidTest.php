<?php

namespace Kirby\Uuid;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserUuid::class)]
class UserUuidTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Uuid.UserUuid';

	public function testIndex()
	{
		$index = UserUuid::index();
		$this->assertInstanceOf(Generator::class, $index);
		$this->assertIsUser($index->current());
		$this->assertSame(1, iterator_count($index));
	}

	public function testModel()
	{
		$user = $this->app->user('my-user');
		$this->assertIsUser($user, Uuid::for('user://my-user')->model());
	}

	public function testPopulate()
	{
		$uuid = $this->app->user('my-user')->uuid();
		$this->assertTrue($uuid->populate());
	}
}
