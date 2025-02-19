<?php

namespace Kirby\Cms;

use Kirby\Cms\NewUser as User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class NewUserRolesTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewUserRolesTest';

	public function testRoles(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor'],
				['name' => 'guest']
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				],
				[
					'email' => 'guest@getkirby.com',
					'role'  => 'guest'
				]
			],
		]);

		// unauthenticated, should only have the current role
		$user  = $this->app->user('editor@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['editor'], $roles);

		// user on another normal user should not have admin as option
		$this->app->impersonate('editor@getkirby.com');
		$user  = $this->app->user('guest@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['editor', 'guest'], $roles);

		// user on themselves should not have admin as option
		$this->app->impersonate('editor@getkirby.com');
		$user  = $this->app->user('editor@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['editor', 'guest'], $roles);

		// current user is admin, user can also have admin option
		$this->app->impersonate('admin@getkirby.com');
		$user  = $this->app->user('editor@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['admin', 'editor', 'guest'], $roles);

		// last admin has only admin role as option
		$user  = $this->app->user('admin@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['admin'], $roles);
	}

	public function testRolesWithPermissions(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				[
					'name'        => 'editor',
					'permissions' => [
						'user' => [
							'changeRole' => true
						],
						'users' => [
							'changeRole' => false
						]
					]
				],
				['name' => 'guest']
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				],
				[
					'email' => 'guest@getkirby.com',
					'role'  => 'guest'
				]
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		// user has permission to change their own role
		$user  = $this->app->user('editor@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['editor', 'guest'], $roles);

		// user has no permission to change someone else's role
		$user  = $this->app->user('guest@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['guest'], $roles);
	}

	public function testRolesWithOptions(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'manager'],
				['name' => 'editor'],
				['name' => 'guest']
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				],
				[
					'email' => 'guest@getkirby.com',
					'role'  => 'guest'
				]
			],
			'blueprints' => [
				'users/manager' => [
					'options' => [
						'create' => [
							'editor' => false
						]
					]
				],
				'users/editor' => [
					'permissions' => [
						'user' => [
							'changeRole' => true
						],
						'users' => [
							'changeRole' => true
						]
					]
				],
				'users/guest' => [
					'options' => [
						'changeRole' => [
							'editor' => false
						]
					]
				]
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		// blueprint option disallows changing role for guest role
		$user  = $this->app->user('guest@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['guest'], $roles);

		// blueprint `create` option limits available roles
		$user  = $this->app->user('editor@getkirby.com');
		$roles = $user->roles()->values(fn ($role) => $role->id());
		$this->assertSame(['editor', 'guest'], $roles);
	}
}
