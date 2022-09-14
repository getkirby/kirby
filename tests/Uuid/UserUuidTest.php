<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\User;

/**
 * @coversDefaultClass \Kirby\Uuid\UserUuid
 */
class UserUuidTest extends TestCase
{
	/**
	 * @covers ::index
	 */
	public function testIndex()
	{
		$index = UserUuid::index();
		$this->assertInstanceOf(Generator::class, $index);
		$this->assertInstanceOf(User::class, $index->current());
		$this->assertSame(1, iterator_count($index));
	}

	/**
	 * @covers ::model
	 */
	public function testModel()
	{
		$user = $this->app->user('my-user');
		$this->assertSame($user, Uuid::for('user://my-user')->model());
	}

	/**
	 * @covers ::populate
	 */
	public function testPopulate()
	{
		$uuid = $this->app->user('my-user')->uuid();
		$this->assertSame(true, $uuid->populate());
	}
}
