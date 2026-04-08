<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class UserPermissionsTest extends TestCase
{
	public static function actionProvider(): array
	{
		return [
			['access'],
			['create'],
			['changeEmail'],
			['changeLanguage'],
			['changeName'],
			['changePassword'],
			['changeRole'],
			['delete'],
			['list'],
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
				['name' => 'admin'],
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
							'access'         => false,
							'changeEmail'    => false,
							'changeLanguage' => false,
							'changeName'     => false,
							'changePassword' => false,
							'changeRole'     => false,
							'create'         => false,
							'delete'         => false,
							'list'           => false,
							'update'         => false
						]
					]
				]
			],
			'user'  => 'editor@getkirby.com',
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			],
		]);

		$user  = $app->user();
		$perms = $user->permissions();

		$this->assertSame('editor', $user->role()->name());
		$this->assertFalse($perms->can($action));
	}

	public function testChangeSingleRole()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin']
			]
		]);

		$user  = new User(['email' => 'test@getkirby.com']);
		$perms = $user->permissions();

		$this->assertFalse($perms->can('changeRole'));
	}
}
