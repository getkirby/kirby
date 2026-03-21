<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
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
			['files', 'edit'],
			['files', 'list'],
			['files', 'read'],
			['files', 'replace'],
			['files', 'save'],

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
			['pages', 'edit'],
			['pages', 'list'],
			['pages', 'move'],
			['pages', 'preview'],
			['pages', 'read'],
			['pages', 'sort'],
			['pages', 'save'],

			['site', 'changeTitle'],
			['site', 'edit'],
			['site', 'save'],

			['users', 'changeEmail'],
			['users', 'changeLanguage'],
			['users', 'changeName'],
			['users', 'changePassword'],
			['users', 'changeRole'],
			['users', 'create'],
			['users', 'delete'],
			['users', 'edit'],
			['users', 'save'],

			['user', 'changeEmail'],
			['user', 'changeLanguage'],
			['user', 'changeName'],
			['user', 'changePassword'],
			['user', 'changeRole'],
			['user', 'delete'],
			['users', 'edit'],
			['users', 'save'],
		];
	}

	public function testActionIsolation(): void
	{
		// disabling a single action leaves the rest of the category intact
		$p = new Permissions(['pages' => ['delete' => false]]);
		$this->assertFalse($p->for('pages', 'delete'));
		$this->assertTrue($p->for('pages', 'read'));
		$this->assertTrue($p->for('pages', 'edit'));
		$this->assertTrue($p->for('pages', 'save'));
		$this->assertTrue($p->for('pages', 'create'));

		// disabling an entire category does not affect other categories
		$p = new Permissions(['pages' => false]);
		$this->assertFalse($p->for('pages', 'read'));
		$this->assertTrue($p->for('files', 'read'));
		$this->assertTrue($p->for('site', 'edit'));
		$this->assertTrue($p->for('site', 'save'));
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

	public function testActionWildcard(): void
	{
		// wildcard disables all actions in a category
		$p = new Permissions(['pages' => ['*' => false]]);
		$this->assertFalse($p->for('pages', 'read'));
		$this->assertFalse($p->for('pages', 'edit'));
		$this->assertFalse($p->for('pages', 'save'));
		$this->assertFalse($p->for('pages', 'delete'));

		// other categories are unaffected
		$this->assertTrue($p->for('files', 'read'));
		$this->assertTrue($p->for('site', 'edit'));
		$this->assertTrue($p->for('site', 'save'));

		// explicit value after wildcard takes precedence
		$p = new Permissions(['pages' => ['*' => false, 'read' => true]]);
		$this->assertTrue($p->for('pages', 'read'));
		$this->assertFalse($p->for('pages', 'edit'));
		$this->assertFalse($p->for('pages', 'save'));
		$this->assertFalse($p->for('pages', 'delete'));

		// explicit value also takes precedence if defined before wildcard
		$p = new Permissions(['pages' => ['read' => true, '*' => false]]);
		$this->assertTrue($p->for('pages', 'read'));
		$this->assertFalse($p->for('pages', 'edit'));
		$this->assertFalse($p->for('pages', 'save'));
		$this->assertFalse($p->for('pages', 'delete'));
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

	public function testExtendActionsWithCategoryBool(): void
	{
		Permissions::$extendedActions = [
			'test-category' => [
				'test-action' => true,
				'another'     => false
			]
		];

		// defaults are used if not overridden
		$p = new Permissions();
		$this->assertTrue($p->for('test-category', 'test-action'));
		$this->assertFalse($p->for('test-category', 'another'));

		// category-level false disables all extended actions
		$p = new Permissions(['test-category' => false]);
		$this->assertFalse($p->for('test-category', 'test-action'));
		$this->assertFalse($p->for('test-category', 'another'));

		// category-level true keeps all extended actions enabled
		$p = new Permissions(['test-category' => true]);
		$this->assertTrue($p->for('test-category', 'test-action'));
		$this->assertTrue($p->for('test-category', 'another'));
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

	public function testForNullCategory(): void
	{
		$p       = new Permissions();
		$message = null;

		set_error_handler(function (int $errno, string $errstr) use (&$message) {
			$message = $errstr;
			return true;
		}, E_USER_DEPRECATED);

		// returns false default and triggers deprecation
		$this->assertFalse($p->for(null));
		$this->assertSame(
			'Passing `$category = null` to `Permissions::for()` is not supported',
			$message
		);

		// returns custom default
		$this->assertTrue($p->for(null, default: true));

		restore_error_handler();
	}

	public function testForWithNonBoolValueThrowsException(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage(
			'The value for the permission "pages.read" must be of type bool, string given'
		);

		new Permissions(['pages' => ['read' => 'yes']]);
	}

	public function testForWithoutActionThrowsException(): void
	{
		$p = new Permissions();

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage(
			'The value for the permission "pages" must be of type bool, array given'
		);

		$p->for('pages');
	}

	public function testMixedSettings(): void
	{
		$p = new Permissions([
			'pages' => false,
			'files' => ['delete' => false],
		]);

		// all page actions disabled
		$this->assertFalse($p->for('pages', 'read'));
		$this->assertFalse($p->for('pages', 'edit'));
		$this->assertFalse($p->for('pages', 'save'));
		$this->assertFalse($p->for('pages', 'delete'));

		// only files.delete disabled, rest untouched
		$this->assertFalse($p->for('files', 'delete'));
		$this->assertTrue($p->for('files', 'read'));
		$this->assertTrue($p->for('files', 'edit'));
		$this->assertTrue($p->for('files', 'save'));

		// unrelated categories unaffected
		$this->assertTrue($p->for('site', 'edit'));
		$this->assertTrue($p->for('site', 'save'));
		$this->assertTrue($p->for('users', 'create'));
	}

	public function testNullSettings(): void
	{
		// null behaves identically to empty array — all defaults apply
		$p = new Permissions(null);
		$this->assertTrue($p->for('pages', 'read'));
		$this->assertTrue($p->for('files', 'edit'));
		$this->assertTrue($p->for('files', 'save'));
		$this->assertTrue($p->for('site', 'changeTitle'));
		$this->assertTrue($p->for('users', 'create'));
	}

	public function testToArray(): void
	{
		// default: all core categories present
		$p     = new Permissions();
		$array = $p->toArray();

		$this->assertArrayHasKey('access', $array);
		$this->assertArrayHasKey('files', $array);
		$this->assertArrayHasKey('languages', $array);
		$this->assertArrayHasKey('pages', $array);
		$this->assertArrayHasKey('site', $array);
		$this->assertArrayHasKey('user', $array);
		$this->assertArrayHasKey('users', $array);

		// default values are true
		$this->assertTrue($array['pages']['read']);
		$this->assertTrue($array['site']['edit']);
		$this->assertTrue($array['site']['save']);

		// modified permissions are reflected
		$p     = new Permissions(['pages' => ['delete' => false]]);
		$array = $p->toArray();
		$this->assertFalse($array['pages']['delete']);
		$this->assertTrue($array['pages']['read']);
	}

	public function testUnknownSettingsIgnored(): void
	{
		// unknown category is silently ignored, all defaults kept
		$p = new Permissions(['nonexistent' => false]);
		$this->assertTrue($p->for('pages', 'read'));
		$this->assertTrue($p->for('files', 'edit'));
		$this->assertTrue($p->for('files', 'save'));

		// unknown action is silently ignored, known actions kept
		$p = new Permissions(['pages' => ['nonexistent' => false]]);
		$this->assertTrue($p->for('pages', 'read'));
		$this->assertTrue($p->for('pages', 'edit'));
		$this->assertTrue($p->for('pages', 'save'));
	}
}
