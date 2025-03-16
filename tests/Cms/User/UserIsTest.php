<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserIsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.UserIs';

	public function testIsAccessibleUsersUser()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor-global',
					'permissions' => [
						'users' => [
							'access' => false
						],
					]
				],
				[
					'name' => 'editor-own',
					'permissions' => [
						'user' => [
							'access' => false
						],
					]
				],
				[
					'name' => 'editor-both',
					'permissions' => [
						'user' => [
							'access' => false
						],
						'users' => [
							'access' => false
						],
					]
				]
			],
			'users' => [
				[
					'email' => 'editor-global@getkirby.com',
					'role'  => 'editor-global'
				],
				[
					'email' => 'editor-own@getkirby.com',
					'role'  => 'editor-own'
				],
				[
					'email' => 'editor-both@getkirby.com',
					'role'  => 'editor-both'
				]
			],
		]);

		$userGlobal = $app->user('editor-global@getkirby.com');
		$userOwn    = $app->user('editor-own@getkirby.com');
		$userBoth   = $app->user('editor-both@getkirby.com');

		// user with only `users.access` disabled can access themselves
		$app->impersonate('editor-global@getkirby.com');
		$this->assertTrue($userGlobal->isAccessible());
		$this->assertFalse($userOwn->isAccessible());
		$this->assertFalse($userBoth->isAccessible());

		// users with only `user.access` disabled can access everyone else
		$app->impersonate('editor-own@getkirby.com');
		$this->assertTrue($userGlobal->isAccessible());
		$this->assertFalse($userOwn->isAccessible());
		$this->assertTrue($userBoth->isAccessible());

		// users with both disabled can't access anything
		$app->impersonate('editor-both@getkirby.com');
		$this->assertFalse($userGlobal->isAccessible());
		$this->assertFalse($userOwn->isAccessible());
		$this->assertFalse($userBoth->isAccessible());

		// almighty Kirby user can access everything
		$app->impersonate('kirby');
		$this->assertTrue($userGlobal->isAccessible());
		$this->assertTrue($userOwn->isAccessible());
		$this->assertTrue($userBoth->isAccessible());
	}

	public function testIsAccessibleBlueprint()
	{
		$app = new App([
			'blueprints' => [
				'users/editor-other' => [
					'options' => ['access' => false]
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor'
				],
				[
					'name' => 'editor-other'
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				],
				[
					'email' => 'editor-other@getkirby.com',
					'role'  => 'editor-other'
				]
			],
		]);

		$user      = $app->user('editor@getkirby.com');
		$userOther = $app->user('editor-other@getkirby.com');

		// no access to role that has the option disabled
		$app->impersonate('editor@getkirby.com');
		$this->assertTrue($user->isAccessible());
		$this->assertFalse($userOther->isAccessible());

		// almighty Kirby user can access everything
		$app->impersonate('kirby');
		$this->assertTrue($user->isAccessible());
		$this->assertTrue($userOther->isAccessible());
	}

	public function testIsListableUsersUser()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor-global',
					'permissions' => [
						'users' => [
							'list' => false
						],
					]
				],
				[
					'name' => 'editor-own',
					'permissions' => [
						'user' => [
							'list' => false
						],
					]
				],
				[
					'name' => 'editor-both',
					'permissions' => [
						'user' => [
							'list' => false
						],
						'users' => [
							'list' => false
						],
					]
				]
			],
			'users' => [
				[
					'email' => 'editor-global@getkirby.com',
					'role'  => 'editor-global'
				],
				[
					'email' => 'editor-own@getkirby.com',
					'role'  => 'editor-own'
				],
				[
					'email' => 'editor-both@getkirby.com',
					'role'  => 'editor-both'
				]
			],
		]);

		$userGlobal = $app->user('editor-global@getkirby.com');
		$userOwn    = $app->user('editor-own@getkirby.com');
		$userBoth   = $app->user('editor-both@getkirby.com');

		// user with only `users.list` disabled can list themselves
		$app->impersonate('editor-global@getkirby.com');
		$this->assertTrue($userGlobal->isListable());
		$this->assertFalse($userOwn->isListable());
		$this->assertFalse($userBoth->isListable());

		// users with only `user.list` disabled can list everyone else
		$app->impersonate('editor-own@getkirby.com');
		$this->assertTrue($userGlobal->isListable());
		$this->assertFalse($userOwn->isListable());
		$this->assertTrue($userBoth->isListable());

		// users with both disabled can't list anything
		$app->impersonate('editor-both@getkirby.com');
		$this->assertFalse($userGlobal->isListable());
		$this->assertFalse($userOwn->isListable());
		$this->assertFalse($userBoth->isListable());

		// almighty Kirby user can list everything
		$app->impersonate('kirby');
		$this->assertTrue($userGlobal->isListable());
		$this->assertTrue($userOwn->isListable());
		$this->assertTrue($userBoth->isListable());
	}

	public function testIsListableBlueprint()
	{
		$app = new App([
			'blueprints' => [
				'users/editor-other' => [
					'options' => ['list' => false]
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor'
				],
				[
					'name' => 'editor-other'
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				],
				[
					'email' => 'editor-other@getkirby.com',
					'role'  => 'editor-other'
				]
			],
		]);

		$user      = $app->user('editor@getkirby.com');
		$userOther = $app->user('editor-other@getkirby.com');

		// no access to role that has the option disabled
		$app->impersonate('editor@getkirby.com');
		$this->assertTrue($user->isListable());
		$this->assertFalse($userOther->isListable());

		// almighty Kirby user can access everything
		$app->impersonate('kirby');
		$this->assertTrue($user->isListable());
		$this->assertTrue($userOther->isListable());
	}

	public function testIsListableDependentOnAccess()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor-both',
					'permissions' => [
						'user' => [
							'access' => false
						],
						'users' => [
							'access' => false
						],
					]
				],
			],
			'users' => [
				[
					'email' => 'editor-both@getkirby.com',
					'role'  => 'editor-both'
				]
			],
		]);

		$user = $app->user('editor-both@getkirby.com');

		$app->impersonate('editor-both@getkirby.com');
		$this->assertFalse($user->isListable());

		$app->impersonate('kirby');
		$this->assertTrue($user->isListable());
	}
}
