<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Role::class)]
class RoleTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public function app()
	{
		return new App([
			'roots' => [
				'site' => static::FIXTURES
			]
		]);
	}

	public function testProps()
	{
		$role = new Role([
			'description' => 'Test',
			'name'  => 'admin',
			'title' => 'Admin'
		]);

		$this->assertSame('admin', $role->name());
		$this->assertSame('Admin', $role->title());
		$this->assertSame('Test', $role->description());
	}

	public function testFactory()
	{
		$app  = $this->app();
		$role = Role::load(static::FIXTURES . '/blueprints/users/editor.yml');

		$this->assertSame('editor', $role->name());
		$this->assertSame('Editor', $role->title());
		$this->assertSame('This should be inherited', $role->description());
	}

	public function testMissingRole()
	{
		$this->expectException('Exception');

		$app  = $this->app();
		$role = Role::load('does-not-exist');
	}

	public function testIs(): void
	{
		$editor  = new Role(['name' => 'editor']);
		$editor2 = new Role(['name' => 'editor']);
		$admin   = new Role(['name' => 'admin']);

		$this->assertTrue($editor->is($editor));
		$this->assertTrue($editor->is($editor2));
		$this->assertFalse($editor->is($admin));
		$this->assertFalse($editor->is(null));
	}

	public function testIsAccessible(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				[
					'name' => 'editor',
					'permissions' => [
						'users' => [
							'access' => false
						]
					]
				],
				[
					'name' => 'author',
					'permissions' => [
						'user' => [
							'access' => false
						],
						'users' => [
							'access' => false
						]
					]
				]
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'author@getkirby.com',
					'role'  => 'author'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$editorRole = $app->roles()->find('editor');
		$authorRole = $app->roles()->find('author');

		// admin can always access roles (users.access granted)
		$app->impersonate('admin@getkirby.com');
		$this->assertTrue($editorRole->isAccessible());

		// editor checks own role: delegates to user.access (default true)
		$app->impersonate('editor@getkirby.com');
		$this->assertTrue($editorRole->isAccessible());

		// editor checks a different role: uses users.access (false)
		$this->assertFalse($authorRole->isAccessible());

		// author checks own role: delegates to user.access (false)
		$app->impersonate('author@getkirby.com');
		$this->assertFalse($authorRole->isAccessible());

		// almighty kirby user can always access roles
		$app->impersonate('kirby');
		$this->assertTrue($editorRole->isAccessible());
	}

	public function testIsAccessibleWithBlueprintOptions(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'users/restricted' => [
					'options' => [
						'access' => [
							'editor' => true,
							'*'      => false
						]
					]
				]
			],
			'roles' => [
				['name' => 'editor'],
				['name' => 'restricted'],
			],
			'users' => [
				['email' => 'editor@test.com', 'role' => 'editor'],
				['email' => 'restricted@test.com', 'role' => 'restricted'],
			]
		]);

		$restrictedRole = $app->roles()->find('restricted');

		// editor can access the restricted role due to explicit blueprint option
		$app->impersonate('editor@test.com');
		$this->assertTrue($restrictedRole->isAccessible());

		// user with the restricted role cannot access their own role (wildcard blocks it)
		$app->impersonate('restricted@test.com');
		$this->assertFalse($restrictedRole->isAccessible());

		// almighty kirby user bypasses blueprint options
		$app->impersonate('kirby');
		$this->assertTrue($restrictedRole->isAccessible());
	}

	public function testIsAccessibleWithoutUser(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
		]);

		$role = new Role(['name' => 'editor']);
		$this->assertFalse($role->isAccessible());
	}

	public function testAdmin()
	{
		$app  = $this->app();
		$role = Role::admin();

		$this->assertSame('admin', $role->name());
		$this->assertSame('Admin', $role->title());
	}

	public function testNobody()
	{
		$app  = $this->app();
		$role = Role::nobody();

		$this->assertSame('nobody', $role->name());
		$this->assertSame('Nobody', $role->title());
		$this->assertTrue($role->isNobody());
	}

	public function testTranslateTitle()
	{
		$role = new Role([
			'name' => 'editor',
			'title' => [
				'en' => 'Editor',
				'de' => 'Bearbeiter'
			]
		]);

		$this->assertSame('Editor', $role->title());
	}

	public function testTranslateDescription()
	{
		$role = new Role([
			'name' => 'editor',
			'description' => [
				'en' => 'Editor',
				'de' => 'Bearbeiter'
			]
		]);

		$this->assertSame('Editor', $role->title());
	}

	public function testToArrayAndDebugInfo()
	{
		$role = new Role([
			'name'        => 'editor',
			'description' => 'Editor'
		]);

		$expected = [
			'description' => 'Editor',
			'id'          => 'editor',
			'name'        => 'editor',
			'permissions' => $role->permissions()->toArray(),
			'title'       => 'Editor'
		];

		$this->assertSame($expected, $role->toArray());
		$this->assertSame($expected, $role->__debugInfo());
	}
}
