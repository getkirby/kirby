<?php

namespace Kirby\Cms;

use Exception;

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

	public function testProps(): void
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

	public function testFactory(): void
	{
		$app  = $this->app();
		$role = Role::load(static::FIXTURES . '/blueprints/users/editor.yml');

		$this->assertSame('editor', $role->name());
		$this->assertSame('Editor', $role->title());
		$this->assertSame('This should be inherited', $role->description());
	}

	public function testMissingRole(): void
	{
		$this->expectException(Exception::class);

		$this->app();
		Role::load('does-not-exist');
	}

	public function testDefaultAdmin(): void
	{
		$app  = $this->app();
		$role = Role::defaultAdmin();

		$this->assertSame('admin', $role->name());
		$this->assertSame('Admin', $role->title());
	}

	public function testDefaultNobody(): void
	{
		$app  = $this->app();
		$role = Role::defaultNobody();

		$this->assertSame('nobody', $role->name());
		$this->assertSame('Nobody', $role->title());
		$this->assertTrue($role->isNobody());
	}

	public function testTitleFromName(): void
	{
		$role = new Role([
			'name' => 'editorInChief',
		]);

		$this->assertSame('Editor in chief', $role->title());
	}

	public function testTranslateTitle(): void
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

	public function testTranslateDescription(): void
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

	public function testToArrayAndDebugInfo(): void
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
