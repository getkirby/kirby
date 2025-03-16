<?php

namespace Kirby\Cms;

use Kirby\Exception\LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(UserPermissions::class)]
class UserPermissionsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserPermissions';

	public static function actionProvider(): array
	{
		return [
			['access'],
			['changeEmail'],
			['changeLanguage'],
			['changeName'],
			['changePassword'],
			['changeRole'],
			['create'],
			['delete'],
			['list'],
			['update'],
		];
	}

	#[DataProvider('actionProvider')]
	public function testWithAdmin(string $action): void
	{
		$this->app = $this->app->clone([

			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			]
		]);

		$this->app->impersonate('kirby');

		$user  = new User(['email' => 'test@getkirby.com']);
		$perms = $user->permissions();

		$this->assertTrue($perms->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithNobody(string $action): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			]
		]);

		$user        = new User(['email' => 'test@getkirby.com']);
		$permissions = $user->permissions();

		$this->assertFalse($permissions->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithNoAdmin(string $action): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				[
					'name' => 'editor',
					'permissions' => [
						'user' => [
							'access'         => false,
							'changeEmail'    => false,
							'changeLanguage' => false,
							'changeName'     => false,
							'changePassword' => false,
							'changeRole'     => false,
							'delete'         => false,
							'list'           => false,
							'update'         => false
						],
						'users' => [
							'changeEmail'    => true,
							'changeLanguage' => true,
							'changeName'     => true,
							'changePassword' => true,
							'changeRole'     => true,
							'create'         => true,
							'delete'         => true,
							'update'         => true
						]
					]
				]
			]
		]);

		$user1  = new User([
			'email' => 'editor1@getkirby.com',
			'role'  => 'editor'
		]);
		$user1->loginPasswordless();

		// `user` permissions are disabled
		$perms1 = $user1->permissions();
		$this->assertSame('editor', $user1->role()->name());
		$this->assertFalse($perms1->can($action));

		// `users` permissions are enabled
		$user2  = new User([
			'email' => 'editor2@getkirby.com',
			'role'  => 'editor'
		]);
		$perms2 = $user2->permissions();
		$this->assertTrue($perms2->can($action));

		$user1->logout();
	}

	/**
	 * @covers \Kirby\Cms\ModelPermissions::canFromCache
	 */
	public function testCanFromCache()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				['id' => 'bastian', 'role' => 'admin'],
			]
		]);

		$app->impersonate('bastian');

		$user = new User([
			'role'      => 'editor',
			'blueprint' => [
				'name' => 'users/editor',
				'options' => [
					'access' => false,
					'list'   => false
				]
			]
		]);

		$this->assertFalse(UserPermissions::canFromCache($user, 'access'));
		$this->assertFalse(UserPermissions::canFromCache($user, 'access'));
		$this->assertFalse(UserPermissions::canFromCache($user, 'list'));
		$this->assertFalse(UserPermissions::canFromCache($user, 'list'));
	}

	/**
	 * @covers \Kirby\Cms\ModelPermissions::canFromCache
	 */
	public function testCanFromCacheDynamic()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Cannot use permission cache for dynamically-determined permission');

		$user = new User(['role'=> 'admin']);

		UserPermissions::canFromCache($user, 'delete');
	}

	public function testChangeSingleRole(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin']
			]
		]);

		$this->app->impersonate('kirby');

		$user  = new User(['email' => 'test@getkirby.com', 'role' => 'admin']);
		$perms = $user->permissions();

		$this->assertFalse($perms->can('changeRole'));
	}
}
