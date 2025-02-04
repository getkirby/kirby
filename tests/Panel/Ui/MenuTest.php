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
	public function testAreasFromMenuOptionWithDivider()
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'menu' => [
						'-'
					]
				]
			]
		]);

		$menu  = new Menu();
		$areas = $menu->areas();

		$this->assertCount(1, $areas);
		$this->assertSame('-', $areas[0]);
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasFromMenuOptionWithUiComponent()
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'menu' => [
						$menuItem = new MenuItem(
							icon: 'test',
							text: 'test',
							link: 'test'
						)
					]
				]
			]
		]);

		$menu  = new Menu();
		$areas = $menu->areas();

		$this->assertCount(1, $areas);
		$this->assertSame($menuItem, $areas[0]);
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasFromMenuOptionWithId()
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'menu' => [
						'site'
					]
				]
			]
		]);

		$menu = new Menu(areas: [
			$area = new Area(id: 'site')
		]);

		$areas = $menu->areas();

		$this->assertCount(1, $areas);
		$this->assertSame($area, $areas[0]);
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasFromMenuOptionWithIdKey()
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'menu' => [
						'site' => true
					]
				]
			]
		]);

		$menu = new Menu(areas: [
			$area = new Area(id: 'site')
		]);

		$areas = $menu->areas();

		$this->assertCount(1, $areas);
		$this->assertSame($area, $areas[0]);
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasFromMenuOptionWithInvalidId()
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'menu' => [
						'does-not-exist'
					]
				]
			]
		]);

		$menu  = new Menu();
		$areas = $menu->areas();

		$this->assertCount(0, $areas);
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasFromMenuOptionWithArray()
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'menu' => [
						'site' => [
							'icon' => 'edit'
						]
					]
				]
			]
		]);

		$menu = new Menu(areas: [
			$area = new Area(id: 'site')
		]);

		// the area does not have the edit icon by default
		$this->assertNotSame('edit', $area->icon());

		// once the areas are loaded, the edit icon
		// should have been injected
		$areas = $menu->areas();

		$this->assertCount(1, $areas);
		$this->assertSame($area, $areas[0]);
		$this->assertSame('edit', $areas[0]->icon());
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasFromMenuOptionWithNewArea()
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'menu' => [
						'todos' => [
							'label' => 'Todos',
							'link'  => 'todos'
						]
					]
				]
			]
		]);

		$menu  = new Menu();
		$areas = $menu->areas();

		$this->assertCount(1, $areas);
		$this->assertSame('todos', $areas[0]->id());
		$this->assertSame('Todos', $areas[0]->label());
		$this->assertSame('todos', $areas[0]->link());
	}

	/**
	 * @covers ::config
	 */
	public function testConfig()
	{
		$menu = new Menu();

		$expected = [
			'site',
			'languages',
			'users',
			'system'
		];

		$this->assertSame($expected, $menu->config());
	}

	/**
	 * @covers ::config
	 */
	public function testConfigWithClosureAndNullAsReturnValue()
	{
		$test = $this;

		$this->app->clone([
			'options' => [
				'panel' => [
					'menu' => function ($kirby) use ($test) {
						$test->assertInstanceOf(App::class, $kirby);
						return null;
					}
				]
			]
		]);

		$menu = new Menu();

		$expected = [
			'site',
			'languages',
			'users',
			'system'
		];

		$this->assertSame($expected, $menu->config());
	}

	/**
	 * @covers ::config
	 */
	public function testConfigWithClosureAndEmptyArrayAsReturnValue()
	{
		$this->app->clone([
			'options' => [
				'panel' => [
					'menu' => function () {
						return [];
					}
				]
			]
		]);

		$menu = new Menu();

		$this->assertSame([], $menu->config());
	}

	/**
	 * @covers ::config
	 */
	public function testConfigWithDefaultOrder()
	{
		$menu = new Menu(areas: [
			new Area(id: 'foo'),
			new Area(id: 'site'),
		]);

		$expected = [
			'site',
			'languages',
			'users',
			'system',
			'foo'
		];

		$this->assertSame($expected, $menu->config());
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

		$this->assertSame('site', $items[0]['props']['link']);
		$this->assertTrue($items[0]['props']['current']);
		$this->assertSame('-', $items[1]);
		$this->assertSame('changes', $items[2]['props']['dialog']);
		$this->assertSame('account', $items[3]['props']['link']);
		$this->assertSame('logout', $items[4]['props']['link']);
	}

	/**
	 * @covers ::options
	 */
	public function testOptions()
	{
		$changes = [
			'dialog'     => 'changes',
			'icon'       => 'edit-line',
			'responsive' => true,
			'text'       => 'Changes',
			'type'       => 'button'
		];

		$account = [
			'icon'       => 'account',
			'link'       => 'account',
			'responsive' => true,
			'text'       => 'Your account',
			'type'       => 'button'
		];

		$logout = [
			'icon'       => 'logout',
			'link'       => 'logout',
			'responsive' => true,
			'text'       => 'Log out',
			'type'       => 'button'
		];

		$menu = new Menu();

		$options = $menu->options();
		$this->assertSame($changes, $options[0]['props']);
		$this->assertSame($account, $options[1]['props']);
		$this->assertSame($logout, $options[2]['props']);
	}
}
