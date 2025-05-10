<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Access
 */
class AccessTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Access';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);
	}

	/**
	 * @covers ::has
	 */
	public function testHasWithoutUser(): void
	{
		// bool
		$this->assertFalse(Access::has());

		// exception
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to access the panel');
		Access::has(throws: true);
	}

	/**
	 * @covers ::has
	 */
	public function testHasWithoutAcceptedUser(): void
	{
		// user without panel access
		$this->app->impersonate('nobody');

		// bool
		$this->assertFalse(Access::has($this->app->user()));

		// exception
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to access the panel');
		Access::has($this->app->user(), throws: true);
	}

	/**
	 * @covers ::has
	 */
	public function testHasWithAcceptedUser(): void
	{
		// accepted user
		$this->app->impersonate('kirby');

		// general access
		$result = Access::has($this->app->user());
		$this->assertTrue($result);

		// area access
		$result = Access::has($this->app->user(), 'site');
		$this->assertTrue($result);
	}

	/**
	 * @covers ::has
	 */
	public function testHasAreaAccess(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'title' => 'Editor',
					'permissions' => [
						'access' => [
							'system' => false
						]
					]
				]
			]
		]);

		// accepted user
		$app->impersonate('test@getkirby.com');

		// general access
		$result = Access::has($app->user());
		$this->assertTrue($result);

		// no defined area permissions means access
		$this->assertTrue(Access::has($app->user(), 'foo'));
		Access::has($app->user(), 'foo', throws: true);

		// no area access
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to access this part of the panel');

		$this->assertFalse(Access::has($app->user(), 'system'));
		Access::has($app->user(), 'system', throws: true);
	}
}
