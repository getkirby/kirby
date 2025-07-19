<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Access::class)]
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
		Dir::remove(static::TMP);
	}

	public function testHasWithoutUser(): void
	{
		// bool
		$access = $this->app->panel()->access();
		$this->assertFalse($access->area());

		// exception
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to access the panel');
		$access->area(throws: true);
	}

	public function testHasWithoutAcceptedUser(): void
	{
		// user without panel access
		$this->app->impersonate('nobody');
		$access = $this->app->panel()->access();

		// bool
		$this->assertFalse($access->area());

		// exception
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to access the panel');
		$access->area(throws: true);
	}

	public function testHasWithAcceptedUser(): void
	{
		// accepted user
		$this->app->impersonate('kirby');
		$access = $this->app->panel()->access();

		// general access
		$result = $access->area();
		$this->assertTrue($result);

		// area access
		$result = $access->area('site');
		$this->assertTrue($result);
	}

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
		$access = $app->panel()->access();

		// general access
		$this->assertTrue($access->area());
		$this->assertTrue($access->area('*'));

		// no defined area permissions means access
		$this->assertTrue($access->area('foo'));
		$access->area('foo', throws: true);

		// no area access
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to access this part of the panel');

		$this->assertFalse($access->area('system'));
		$access->area('system', throws: true);
	}
}
