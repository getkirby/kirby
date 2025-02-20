<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class UserPermissionsTest extends TestCase
{
	public static function actionProvider(): array
	{
		return [
			['create'],
			['changeEmail'],
			['changeLanguage'],
			['changeName'],
			['changePassword'],
			['changeRole'],
			['delete'],
			['update'],
		];
	}

	/**
	 * @dataProvider actionProvider
	 */
	public function testWithAdmin($action)
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			]
		]);

		$kirby->impersonate('kirby');

		$user  = new User(['email' => 'test@getkirby.com']);
		$perms = $user->permissions();

		$this->assertTrue($perms->can($action));
	}

	/**
	 * @dataProvider actionProvider
	 */
	public function testWithNobody($action)
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			]
		]);

		$user  = new User(['email' => 'test@getkirby.com']);
		$perms = $user->permissions();

		$this->assertFalse($perms->can($action));
	}

	/**
	 * @dataProvider actionProvider
	 */
	public function testWithNoAdmin($action)
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor',
					'permissions' => [
						'user' => [
							'changeEmail'    => false,
							'changeLanguage' => false,
							'changeName'     => false,
							'changePassword' => false,
							'changeRole'     => false,
							'delete'         => false,
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
			],
			'user'  => 'editor1@getkirby.com',
			'users' => [
				[
					'email' => 'editor1@getkirby.com',
					'role'  => 'editor'
				],
				[
					'email' => 'editor2@getkirby.com',
					'role'  => 'editor'
				]
			],
		]);

		// `user` permissions are disabled
		$user1  = $app->user();
		$perms1 = $user1->permissions();
		$this->assertSame('editor', $user1->role()->name());
		$this->assertFalse($perms1->can($action));

		// `users` permissions are enabled
		$user2  = $app->user('editor2@getkirby.com');
		$perms2 = $user2->permissions();
		$this->assertTrue($perms2->can($action));
	}

	public function testChangeSingleRole()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin']
			],
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$app->impersonate('kirby');

		$user  = $app->user('test@getkirby.com');
		$perms = $user->permissions();

		$this->assertFalse($perms->can('changeRole'));
	}
}
