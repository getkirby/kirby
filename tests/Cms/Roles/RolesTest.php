<?php

namespace Kirby\Cms;

use Kirby\Data\Data;

class RolesTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.Roles';

	public function testFactory(): void
	{
		$roles = Roles::factory([
			[
				'name'  => 'editor',
				'title' => 'Editor'
			]
		]);

		$this->assertInstanceOf(Roles::class, $roles);

		// should contain the editor role from fixtures and the default admin role
		$this->assertCount(2, $roles);
		$this->assertSame('admin', $roles->first()->name());
		$this->assertSame('editor', $roles->last()->name());
	}

	public function testLoad(): void
	{
		$roles = Roles::load(static::FIXTURES . '/blueprints/users');

		$this->assertInstanceOf(Roles::class, $roles);

		// should contain the base and editor role from fixtures
		// and the default admin role
		$this->assertCount(3, $roles);
		$this->assertSame('admin', $roles->first()->name());
		$this->assertSame('editor', $roles->last()->name());
	}

	public function testLoadFromPlugins(): void
	{
		$app = new App([
			'blueprints' => [
				'users/admin' => [
					'name'  => 'admin',
					'title' => 'Admin'
				],
				'users/editor' => [
					'name'  => 'editor',
					'title' => 'Editor'
				],
			]
		]);

		$roles = Roles::load();

		$this->assertCount(2, $roles);
		$this->assertSame('admin', $roles->first()->name());
		$this->assertSame('editor', $roles->last()->name());
	}

	public function testLoadFromPluginsCallbackString(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null',
				'blueprints' => static::TMP,
			],
			'blueprints' => [
				'users/admin' => function () {
					return static::TMP . '/custom/admin.yml';
				},
				'users/editor' => function () {
					return static::TMP . '/custom/editor.yml';
				}
			]
		]);

		Data::write(static::TMP . '/custom/admin.yml', [
			'name' => 'admin',
			'title' => 'Admin'
		]);

		Data::write(static::TMP . '/custom/editor.yml', [
			'name' => 'editor',
			'title' => 'Editor'
		]);

		$roles = Roles::load();

		$this->assertCount(2, $roles);
		$this->assertSame('admin', $roles->first()->name());
		$this->assertSame('editor', $roles->last()->name());
	}

	public function testLoadFromPluginsCallbackArray(): void
	{
		new App([
			'blueprints' => [
				'users/admin' => function () {
					return [
						'name' => 'admin',
						'title' => 'Admin'
					];
				},
				'users/editor' => function () {
					return [
						'name' => 'editor',
						'title' => 'Editor'
					];
				}
			]
		]);

		$roles = Roles::load();

		$this->assertCount(2, $roles);
		$this->assertSame('admin', $roles->first()->name());
		$this->assertSame('editor', $roles->last()->name());
	}

	public function testCanBeChanged(): void
	{
		$app = new App([
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
			'blueprints' => [
				'users/admin' => [
					'name'  => 'admin',
					'title' => 'Admin'
				],
				'users/editor' => [
					'name'  => 'editor',
					'title' => 'Editor'
				],
			]
		]);

		$roles = Roles::load();
		$this->assertInstanceOf(Roles::class, $roles);
		$this->assertCount(2, $roles);

		$app->impersonate('editor@getkirby.com');
		$canBeChanged = $roles->canBeChanged();
		$this->assertCount(1, $canBeChanged);

		$app->impersonate('admin@getkirby.com');
		$canBeChanged = $roles->canBeChanged();
		$this->assertCount(2, $canBeChanged);
	}

	public function testCanBeCreated(): void
	{
		$app = new App([
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
			'blueprints' => [
				'users/admin' => [
					'name'  => 'admin',
					'title' => 'Admin'
				],
				'users/editor' => [
					'name'  => 'editor',
					'title' => 'Editor'
				],
			]
		]);

		$roles = Roles::load();
		$this->assertInstanceOf(Roles::class, $roles);
		$this->assertCount(2, $roles);

		$app->impersonate('editor@getkirby.com');
		$canBeCreated = $roles->canBeCreated();
		$this->assertCount(1, $canBeCreated);

		$app->impersonate('admin@getkirby.com');
		$canBeCreated = $roles->canBeCreated();
		$this->assertCount(2, $canBeCreated);
	}
}
