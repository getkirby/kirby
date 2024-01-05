<?php

namespace Kirby\Cms;

use Kirby\Data\Data;

class RolesTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.Roles';

	public function testFactory()
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

	public function testLoad()
	{
		$roles = Roles::load(static::FIXTURES . '/blueprints/users');

		$this->assertInstanceOf(Roles::class, $roles);

		// should contain the editor role from fixtures and the default admin role
		$this->assertCount(2, $roles);
		$this->assertSame('admin', $roles->first()->name());
		$this->assertSame('editor', $roles->last()->name());
	}

	public function testLoadFromPlugins()
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

	public function testLoadFromPluginsCallbackString()
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

	public function testLoadFromPluginsCallbackArray()
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

	public function testCanBeChanged()
	{
		new App([
			'user'  => 'admin@getkirby.com',
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
		$canBeChanged = $roles->canBeChanged();

		$this->assertInstanceOf(Roles::class, $roles);
		$this->assertCount(2, $roles);
		$this->assertCount(1, $canBeChanged);
	}

	public function testCanBeCreated()
	{
		new App([
			'user'  => 'admin@getkirby.com',
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
		$canBeCreated = $roles->canBeCreated();

		$this->assertInstanceOf(Roles::class, $roles);
		$this->assertCount(2, $roles);
		$this->assertCount(2, $canBeCreated);
	}
}
