<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * @coversDefaultClass \Kirby\Cms\Permissions
 */
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

			['languages', 'access'],
			['languages', 'create'],
			['languages', 'delete'],
			['languages', 'list'],
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

			['site', 'access'],
			['site', 'changeTitle'],
			['site', 'update'],

			['users', 'access'],
			['users', 'changeEmail'],
			['users', 'changeLanguage'],
			['users', 'changeName'],
			['users', 'changePassword'],
			['users', 'changeRole'],
			['users', 'create'],
			['users', 'delete'],
			['users', 'list'],
			['users', 'update'],

			['user', 'access'],
			['user', 'changeEmail'],
			['user', 'changeLanguage'],
			['user', 'changeName'],
			['user', 'changePassword'],
			['user', 'changeRole'],
			['user', 'delete'],
			['user', 'list'],
			['user', 'update'],
		];
	}

	/**
	 * @covers ::__construct
	 * @covers ::for
	 * @dataProvider actionsProvider
	 */
	public function testActions(string $category, $action)
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

	public function testExtendActions()
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

	public function testExtendActionsCoreOverride()
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

	/**
	 * @covers ::for
	 */
	public function testForDefault()
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
