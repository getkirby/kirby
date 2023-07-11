<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Menu
 * @covers ::__construct
 */
class MenuTest extends TestCase
{
	protected $app;
	protected $tmp = __DIR__ . '/tmp';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->tmp,
			]
		]);

		Dir::make($this->tmp);
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove($this->tmp);

		// clear fake json requests
		$_GET = [];

		// clean up $_SERVER
		unset($_SERVER['SERVER_SOFTWARE']);
	}

	/**
	 * @covers ::areas
	 */
	public function testAreas()
	{
		$menu  = new Menu(
			[
				'site' => [
					'icon'  => 'home',
					'label' => 'Site',
					'link'  => 'site'
				]
			]
		);
		$areas = $menu->areas();
		$this->assertSame('site', $areas[0]['id']);
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasDefaultOrder()
	{
		$menu  = new Menu(
			[
				'license' => [
					'label' => 'Register',
					'link'  => 'key'
				],
				'site' => [
					'icon'  => 'home',
					'label' => 'Site',
					'link'  => 'site'
				]
			]
		);
		$areas = $menu->areas();
		$this->assertSame(['site', 'license'], array_column($areas, 'id'));
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasConfigOption()
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'menu' => [
						'site',
						'-',
						'todos' => [
							'label' => 'todos',
							'link'  => 'todos'
						],
						'gone',
						'users' => [
							'label' => 'Buddies'
						]
					]
				]
			]
		]);

		$menu  = new Menu(
			[
				'license' => [
					'label' => 'Register',
					'link'  => 'key'
				],
				'site' => [
					'icon'  => 'home',
					'label' => 'Site',
					'link'  => 'site'
				],
				'users' => [
					'icon'  => 'users',
					'label' => 'Users',
					'link'  => 'users'
				]
			]
		);
		$areas = $menu->areas();
		$this->assertSame('site', $areas[0]['id']);
		$this->assertSame('home', $areas[0]['icon']);
		$this->assertSame('-', $areas[1]);
		$this->assertSame('todos', $areas[2]['id']);
		$this->assertTrue($areas[2]['menu']);
		$this->assertSame('users', $areas[3]['id']);
		$this->assertSame('users', $areas[3]['icon']);
		$this->assertSame('Buddies', $areas[3]['label']);
		$this->assertSame('license', $areas[4]['id']);
	}

	/**
	 * @covers ::entry
	 */
	public function testEntry()
	{
		$menu = new Menu([], [], 'account');
		$this->assertTrue($menu->hasPermission('account'));

		$entry = $menu->entry([
			'id'    => 'account',
			'link'  => 'foo',
			'label' => 'Foo',
			'menu'  => true
		]);
		$this->assertSame([
			'current' => true,
			'id'      => 'account',
			'link'    => 'foo',
			'text'    => 'Foo'
		], $entry);
	}

	/**
	 * @covers ::entry
	 */
	public function testEntryDialog()
	{
		$menu = new Menu([], [], 'account');

		$entry = $menu->entry([
			'id'    => 'account',
			'link'  => 'foo',
			'label' => 'Foo',
			'menu'  => ['dialog' => 'foo']
		]);

		$this->assertSame([
			'current' => true,
			'id'      => 'account',
			'dialog'  => 'foo',
			'text'    => 'Foo'
		], $entry);
	}

	/**
	 * @covers ::entry
	 */
	public function testEntryMenu()
	{
		$menu = new Menu([], [], 'account');
		$this->assertFalse($menu->entry(['id' => 'account']));
		$this->assertFalse($menu->entry(['id' => 'account', 'menu' => false]));
		$this->assertFalse($menu->entry(['id' => 'account', 'menu' => fn () => false]));

		$test = $this;
		$menu->entry(['id' => 'account', 'menu' => function ($areas, $permissions, $current) use ($test) {
			$test->assertSame([], $areas);
			$test->assertSame([], $permissions);
			$test->assertSame('account', $current);
			return false;
		}]);

		$entry = $menu->entry([
			'id' => 'account',
			'link'  => 'foo',
			'label' => 'Foo',
			'menu'  => 'disabled'
		]);
		$this->assertTrue($entry['disabled']);
	}

	/**
	 * @covers ::entry
	 */
	public function testEntryNoPermission()
	{
		$menu = new Menu([], ['access' => ['account' => false]]);
		$this->assertFalse($menu->entry(['id' => 'account']));
	}

	/**
	 * @covers ::entries
	 */
	public function testEntries()
	{
		$menu = new Menu(
			[
				'site' => [
					'icon'  => 'home',
					'label' => 'Site',
					'link'  => 'site'
				]
			],
			[],
			'site'
		);

		$entries = $menu->entries();
		$this->assertSame('site', $entries[0]['id']);
		$this->assertTrue($entries[0]['current']);
		$this->assertSame('-', $entries[1]);
		$this->assertSame('changes', $entries[2]['id']);
		$this->assertSame('account', $entries[3]['id']);
		$this->assertSame('logout', $entries[4]['id']);
	}

	/**
	 * @covers ::hasPermission
	 */
	public function testHasPermission()
	{
		$menu = new Menu([], []);
		$this->assertTrue($menu->hasPermission('account'));

		$menu = new Menu([], ['access' => ['account' => true]]);
		$this->assertTrue($menu->hasPermission('account'));

		$menu = new Menu([], ['access' => ['account' => false]]);
		$this->assertFalse($menu->hasPermission('account'));
	}

	/**
	 * @covers ::isCurrent
	 */
	public function testIsCurrent()
	{
		$menu = new Menu([], [], 'account');

		$this->assertTrue($menu->isCurrent('account'));
		$this->assertTrue($menu->isCurrent('foo', true));
		$this->assertTrue($menu->isCurrent('foo', fn () => true));
		$this->assertFalse($menu->isCurrent('site'));
		$this->assertFalse($menu->isCurrent('foo', false));
		$this->assertFalse($menu->isCurrent('foo', fn () => false));

		$test = $this;
		$menu->isCurrent('foo', function (string $current) use ($test) {
			$test->assertSame('account', $current);
			return true;
		});
	}


	/**
	 * @covers ::options
	 */
	public function testOptions()
	{
		$menu    = new Menu();
		$entries = $menu->entries();

		$changes = [
			'icon'     => 'edit-sheet',
			'id'       => 'changes',
			'dialog'   => 'changes',
			'text'     => 'Changes'
		];

		$account = [
			'current'  => false,
			'icon'     => 'account',
			'id'       => 'account',
			'link'     => 'account',
			'disabled' => false,
			'text'     => 'Your account'
		];

		$logout = [
			'icon' => 'logout',
			'id'   => 'logout',
			'link' => 'logout',
			'text' => 'Log out'
		];

		$this->assertSame($changes, $entries[1]);
		$this->assertSame($account, $entries[2]);
		$this->assertSame($logout, $entries[3]);
	}


	// /**
	//  * @covers ::menu
	//  */
	// public function testMenuAccess()
	// {
	// 	$menu = View::menu(
	// 		[
	// 			'site' => [
	// 				'icon'  => 'home',
	// 				'label' => 'Site',
	// 				'link'  => 'site',
	// 				'menu'  => true,
	// 			]
	// 		],
	// 		[
	// 			'access' => [
	// 				'site' => false
	// 			]
	// 		],
	// 		'site'
	// 	);

	// 	$this->assertCount(4, $menu);
	// 	$this->assertSame('-', $menu[0]);
	// }

	// /**
	//  * @covers ::menu
	//  */
	// public function testMenuCurrentCallback()
	// {
	// 	$unit = $this;
	// 	$menu = View::menu(
	// 		[
	// 			'site' => [
	// 				'icon'    => 'home',
	// 				'label'   => 'Site',
	// 				'link'    => 'site',
	// 				'current' => function (string|null $current) use ($unit) {
	// 					$unit->assertNull($current);
	// 					return true;
	// 				}
	// 			]
	// 		],
	// 	);

	// 	$this->assertCount(5, $menu);
	// 	$this->assertSame('Site', $menu[0]['text']);
	// }

	// /**
	//  * @covers ::menu
	//  */
	// public function testMenuCallback()
	// {
	// 	$menu = View::menu(
	// 		[
	// 			'site' => [
	// 				'icon'  => 'home',
	// 				'label' => 'Site',
	// 				'link'  => 'site',
	// 				'menu'  => fn () => true
	// 			]
	// 		],
	// 	);

	// 	$this->assertCount(5, $menu);
	// 	$this->assertSame('Site', $menu[0]['text']);
	// }

	// /**
	//  * @covers ::menu
	//  */
	// public function testMenuCallbackDisabled()
	// {
	// 	$menu = View::menu(
	// 		[
	// 			'site' => [
	// 				'icon'  => 'home',
	// 				'label' => 'Site',
	// 				'link'  => 'site',
	// 				'menu'  => fn () => 'disabled',
	// 			]
	// 		],
	// 	);

	// 	$this->assertCount(5, $menu);
	// 	$this->assertTrue($menu[0]['disabled']);
	// }

	// /**
	//  * @covers ::menu
	//  */
	// public function testMenuCallbackAddtionalOptions()
	// {
	// 	$menu = View::menu(
	// 		[
	// 			'site' => [
	// 				'icon'  => 'home',
	// 				'label' => 'Site',
	// 				'menu'  => fn () => [
	// 					'dialog' => 'test'
	// 				],
	// 			]
	// 		],
	// 	);

	// 	$this->assertCount(5, $menu);
	// 	$this->assertSame('test', $menu[0]['dialog']);
	// }

	// /**
	//  * @covers ::menu
	//  */
	// public function testMenuCallbackReturningFalse()
	// {
	// 	$menu = View::menu(
	// 		[
	// 			'site' => [
	// 				'icon'  => 'home',
	// 				'label' => 'Site',
	// 				'link'  => 'site',
	// 				'menu'  => fn () => false
	// 			]
	// 		],
	// 	);

	// 	$this->assertCount(4, $menu);
	// }

	// /**
	//  * @covers ::menu
	//  */
	// public function testMenuAccountPermissions()
	// {
	// 	$menu = View::menu([], [
	// 		'access' => [
	// 			'account' => true
	// 		]
	// 	]);

	// 	$this->assertFalse($menu[2]['disabled']);
	// }

	// /**
	//  * @covers ::menu
	//  */
	// public function testMenuAccountIsCurrent()
	// {
	// 	$menu = View::menu([], [], 'account');

	// 	$this->assertTrue($menu[2]['current']);
	// }
}
