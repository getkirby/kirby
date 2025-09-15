<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Permissions::class)]
class PermissionsTest extends TestCase
{
	public function tearDown(): void
	{
		Permissions::$extendedActions = [];
	}

	public static function actionsProvider(): array
	{
		return [
			['files', 'access'],
			['files', 'changeName'],
			['files', 'changeTemplate'],
			['files', 'create'],
			['files', 'delete'],
			['files', 'list'],
			['files', 'read'],
			['files', 'replace'],
			['files', 'update'],

			['languages', 'create'],
			['languages', 'delete'],
			['languages', 'update'],

			['pages', 'access'],
			['pages', 'changeSlug'],
			['pages', 'changeStatus'],
			['pages', 'changeTemplate'],
			['pages', 'changeTitle'],
			['pages', 'create'],
			['pages', 'delete'],
			['pages', 'duplicate'],
			['pages', 'list'],
			['pages', 'move'],
			['pages', 'preview'],
			['pages', 'read'],
			['pages', 'sort'],
			['pages', 'update'],

			['site', 'changeTitle'],
			['site', 'update'],

			['users', 'changeEmail'],
			['users', 'changeLanguage'],
			['users', 'changeName'],
			['users', 'changePassword'],
			['users', 'changeRole'],
			['users', 'create'],
			['users', 'delete'],
			['users', 'update'],

			['user', 'changeEmail'],
			['user', 'changeLanguage'],
			['user', 'changeName'],
			['user', 'changePassword'],
			['user', 'changeRole'],
			['user', 'delete'],
			['user', 'update'],
		];
	}

	#[DataProvider('actionsProvider')]
	public function testActions(string $category, $action): void
	{
		// default
		$p = new Permissions();
		$this->assertTrue($p->for($category, $action));

		// globally disabled
		$p = new Permissions([$category => false]);
		$this->assertFalse($p->for($category, $action));

		// monster off switch
		$p = new Permissions(false);
		$this->assertFalse($p->for($category, $action));

		// monster on switch
		$p = new Permissions(true);
		$this->assertTrue($p->for($category, $action));

		// locally disabled
		$p = new Permissions([
			$category => [
				$action => false
			]
		]);

		$this->assertFalse($p->for($category, $action));

		// locally enabled
		$p = new Permissions([
			$category => [
				$action => true
			]
		]);

		$this->assertTrue($p->for($category, $action));
	}

	public function testExtendActions(): void
	{
		Permissions::$extendedActions = [
			'test-category' => [
				'test-action' => true,
				'another'     => false
			]
		];

		// default values
		$permissions = new Permissions();
		$this->assertTrue($permissions->for('test-category', 'test-action'));
		$this->assertFalse($permissions->for('test-category', 'another'));
		$this->assertFalse($permissions->for('test-category', 'does-not-exist'));

		// overridden values
		$permissions = new Permissions([
			'test-category' => [
				'*'       => false,
				'another' => true
			]
		]);
		$this->assertFalse($permissions->for('test-category', 'test-action'));
		$this->assertTrue($permissions->for('test-category', 'another'));
		$this->assertFalse($permissions->for('test-category', 'does-not-exist'));
	}

	public function testExtendActionsCoreOverride(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The action pages is already a core action');

		Permissions::$extendedActions = [
			'pages' => [
				'test-action' => true
			]
		];

		new Permissions();
	}

	public function testForDefault(): void
	{
		// exists
		$p = new Permissions();
		$this->assertTrue($p->for('access', 'site'));

		// category does not exist
		$p = new Permissions();
		$this->assertFalse($p->for('foo'));

		// category does not exist with custom default
		$p = new Permissions();
		$this->assertTrue($p->for('foo', default:true));

		// action does not exist
		$p = new Permissions();
		$this->assertFalse($p->for('access', 'foo'));

		// action does not exist with custom default
		$p = new Permissions();
		$this->assertTrue($p->for('access', 'foo', default:true));
	}
}
