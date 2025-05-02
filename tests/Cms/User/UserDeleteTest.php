<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserDeleteTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserDelete';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				]
			],
			'user' => 'admin@getkirby.com'
		]);
	}

	public function testDelete(): void
	{
		$user = User::create(['email' => 'editor@domain.com']);

		$this->assertFileExists($user->root() . '/user.txt');
		$user->delete();
		$this->assertFileDoesNotExist($user->root() . '/user.txt');
	}

	public function testDeleteWithFiles(): void
	{
		$user = User::create(['email' => 'editor@domain.com']);

		touch($user->root() . '/test.jpg');

		$this->assertCount(1, $user->files());

		$user->delete();

		$this->assertFileDoesNotExist($user->root() . '/test.jpg');
		$this->assertDirectoryDoesNotExist($user->root());
	}

	public function testDeleteHooks(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.delete:before' => function (User $user) use ($phpunit, &$calls) {
					$phpunit->assertSame('editor@domain.com', $user->email());
					$calls++;
				},
				'user.delete:after' => function ($status, User $user) use ($phpunit, &$calls) {
					$phpunit->assertTrue($status);
					$phpunit->assertSame('editor@domain.com', $user->email());
					$calls++;
				}
			]
		]);

		$user = User::create(['email' => 'editor@domain.com']);
		$user->delete();

		$this->assertSame(2, $calls);
	}

	public function testDeleteHookWithUUIDAccess(): void
	{
		$phpunit = $this;
		$uuid    = null;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.delete:after' => function ($status, User $user) use ($phpunit, &$uuid) {
					$phpunit->assertSame($uuid, $user->uuid()->id());
				}
			]
		]);

		// we need to authenticate again after the app has been cloned
		$this->app->impersonate('kirby');

		$user = User::create(['email' => 'editor@domain.com']);
		$uuid = $user->uuid()->id();

		$user->delete();
	}
}
