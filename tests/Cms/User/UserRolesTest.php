<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserRolesTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.UserRoles';

	private function roleIds(string $email): array
	{
		return $this->app->user($email)->roles()->values(fn ($role) => $role->id());
	}

	public function testRoles(): void
	{
		$uuid = uuid();

		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor-' . $uuid],
				['name' => 'guest-' . $uuid]
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'email' => 'guest@getkirby.com',
					'role'  => 'guest-' . $uuid
				]
			],
		]);

		// no authenticated user: permissions cannot be evaluated
		$this->assertSame([], $this->roleIds('editor@getkirby.com'));

		// non-admin cannot assign the admin role to another user or to themselves
		$this->app->impersonate('editor@getkirby.com');
		$this->assertSame(['editor-' . $uuid, 'guest-' . $uuid], $this->roleIds('guest@getkirby.com'));
		$this->assertSame(['editor-' . $uuid, 'guest-' . $uuid], $this->roleIds('editor@getkirby.com'));

		// admin can assign any role including admin
		$this->app->impersonate('admin@getkirby.com');
		$this->assertSame(['admin', 'editor-' . $uuid, 'guest-' . $uuid], $this->roleIds('editor@getkirby.com'));

		// last admin cannot be demoted, so only the admin role is returned
		$this->assertSame(['admin'], $this->roleIds('admin@getkirby.com'));
	}

	public function testRolesWithChangeRoleOption(): void
	{
		$uuid = uuid();

		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'editor-' . $uuid],
				['name' => 'guest-' . $uuid]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'email' => 'guest@getkirby.com',
					'role'  => 'guest-' . $uuid
				]
			],
			'blueprints' => [
				'users/editor-' . $uuid => [
					'permissions' => [
						'user'  => ['changeRole' => true],
						'users' => ['changeRole' => true]
					]
				],
				'users/guest-' . $uuid => [
					'options' => [
						'changeRole' => [
							'editor-' . $uuid => false
						]
					]
				]
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		// the guest blueprint denies changeRole for the editor role,
		// so the editor cannot change the guest's role at all
		$this->assertSame(['guest-' . $uuid], $this->roleIds('guest@getkirby.com'));
	}

	public function testRolesWithCreateOption(): void
	{
		$uuid = uuid();

		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'editor-' . $uuid],
				['name' => 'guest-' . $uuid],
				['name' => 'manager-' . $uuid]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'email' => 'guest@getkirby.com',
					'role'  => 'guest-' . $uuid
				]
			],
			'blueprints' => [
				'users/editor-' . $uuid => [
					'permissions' => [
						'user'  => ['changeRole' => true],
						'users' => ['changeRole' => true]
					]
				],
				'users/manager-' . $uuid => [
					'options' => [
						'create' => [
							'editor-' . $uuid => false
						]
					]
				]
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		// the manager blueprint denies create for the editor role,
		// so manager is excluded from the available roles for the editor
		$this->assertSame(['editor-' . $uuid, 'guest-' . $uuid], $this->roleIds('editor@getkirby.com'));
	}

	public function testRolesWithInaccessibleRole(): void
	{
		$uuid = uuid();

		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				[
					'name'        => 'editor-' . $uuid,
					'permissions' => [
						'user'  => ['access' => false],
						'users' => ['access' => false]
					]
				],
				['name' => 'guest-' . $uuid]
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'email' => 'guest@getkirby.com',
					'role'  => 'guest-' . $uuid
				]
			]
		]);

		// user.access: false and users.access: false block all roles from passing
		// the isAccessible filter, so no roles are available even for the editor's own role
		$this->app->impersonate('editor@getkirby.com');
		$this->assertSame([], $this->roleIds('editor@getkirby.com'));

		// the same applies when the editor acts on another user
		$this->assertSame([], $this->roleIds('guest@getkirby.com'));

		// admin bypasses access restrictions and can assign all roles
		$this->app->impersonate('admin@getkirby.com');
		$this->assertSame(['admin', 'editor-' . $uuid, 'guest-' . $uuid], $this->roleIds('editor@getkirby.com'));
	}

	public function testRolesWithPermissions(): void
	{
		$uuid = uuid();

		$this->app = $this->app->clone([
			'roles' => [
				[
					'name'        => 'editor-' . $uuid,
					'permissions' => [
						'user'  => ['changeRole' => true],
						'users' => ['changeRole' => false]
					]
				],
				['name' => 'guest-' . $uuid]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				],
				[
					'email' => 'guest@getkirby.com',
					'role'  => 'guest-' . $uuid
				]
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		// user.changeRole: true allows the editor to change their own role
		$this->assertSame(['editor-' . $uuid, 'guest-' . $uuid], $this->roleIds('editor@getkirby.com'));

		// users.changeRole: false prevents the editor from changing another user's role,
		// so only that user's current role is returned
		$this->assertSame(['guest-' . $uuid], $this->roleIds('guest@getkirby.com'));
	}
}
