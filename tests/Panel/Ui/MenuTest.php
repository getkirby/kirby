<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Panel\Area;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Menu
 * @covers ::__construct
 */
class MenuTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Menu';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);

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
		$menu = new Menu(areas: [
			new Area(
				id: 'site',
			)
		]);

		$areas = $menu->areas();

		$this->assertCount(1, $areas);
		$this->assertSame('site', $areas[0]->id());
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasDefaultOrder()
	{
		$menu = new Menu(areas: [
			new Area(id: 'foo'),
			new Area(id: 'site'),
		]);

		$areas = $menu->areas();

		$this->assertSame('site', $areas[0]->id());
		$this->assertSame('foo', $areas[1]->id());
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
			areas: [
				new Area(
					id: 'license',
					label: 'Register',
					icon: 'key'
				),
				new Area(
					id: 'site',
					label: 'Site',
					icon: 'home'
				),
				new Area(
					id: 'users',
					label: 'Users',
					icon: 'users'
				),
			]
		);
		$areas = $menu->areas();

		$this->assertSame('site', $areas[0]->id());
		$this->assertSame('home', $areas[0]->icon());
		$this->assertSame('-', $areas[1]);
		$this->assertSame('todos', $areas[2]->id());
		$this->assertTrue($areas[2]->menu());
		$this->assertSame('users', $areas[3]->id());
		$this->assertSame('users', $areas[3]->icon());
		$this->assertSame('Buddies', $areas[3]->label());
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasConfigOptionClosure()
	{
		$test = $this;

		$this->app->clone([
			'options' => [
				'panel' => [
					'menu' => function ($kirby) use ($test) {
						$test->assertInstanceOf(App::class, $kirby);
						return [];
					}
				]
			]
		]);

		$menu  = new Menu();
		$areas = $menu->areas();
		$this->assertCount(0, $areas);
	}

	/**
	 * @covers ::item
	 */
	public function testItem()
	{
		$menu = new Menu();

		$item = $menu->item(new Area(
			id: 'account',
			link: 'foo',
			label: 'Foo',
			menu: true
		));

		$this->assertInstanceOf(MenuItem::class, $item);
	}

	/**
	 * @covers ::item
	 */
	public function testItemWithoutArea()
	{
		$menu = new Menu();
		$item = $menu->item(null);

		$this->assertNull($item);
	}

	/**
	 * @covers ::items
	 */
	public function testItems()
	{
		$menu = new Menu(
			areas: [
				new Area(
					id: 'site',
					label: 'Site',
					icon: 'home',
					menu: true,
					link: 'site'
				)
			],
			current: 'site'
		);

		$items = $menu->items();

		$this->assertSame('site', $items[0]['link']);
		$this->assertTrue($items[0]['current']);
		$this->assertSame('-', $items[1]);
		$this->assertSame('changes', $items[2]['dialog']);
		$this->assertSame('account', $items[3]['link']);
		$this->assertSame('logout', $items[4]['link']);
	}

	/**
	 * @covers ::options
	 */
	public function testOptions()
	{
		$changes = [
			'dialog' => 'changes',
			'icon'   => 'edit-line',
			'text'   => 'Changes'
		];

		$account = [
			'icon' => 'account',
			'link' => 'account',
			'text' => 'Your account'
		];

		$logout = [
			'icon' => 'logout',
			'link' => 'logout',
			'text' => 'Log out'
		];

		$menu = new Menu();

		$options = $menu->options();
		$this->assertSame($changes, $options[0]);
		$this->assertSame($account, $options[1]);
		$this->assertSame($logout, $options[2]);
	}
}
