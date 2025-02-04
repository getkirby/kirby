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
		$menu = new Menu(
			config: [
				'-'
			]
		);

		$areas = $menu->areas();

		$this->assertCount(1, $areas);
		$this->assertSame('-', $areas[0]);
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasFromMenuOptionWithUiComponent()
	{
		$menu = new Menu(
			config: [
				$menuItem = new MenuItem(
					icon: 'test',
					text: 'test',
					link: 'test'
				)
			]
		);

		$areas = $menu->areas();

		$this->assertCount(1, $areas);
		$this->assertSame($menuItem, $areas[0]);
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasFromMenuOptionWithId()
	{
		$menu = new Menu(
			areas: [
				$area = new Area(id: 'site')
			],
			config: [
				'site'
			]
		);

		$areas = $menu->areas();

		$this->assertCount(1, $areas);
		$this->assertSame($area, $areas[0]);
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasFromMenuOptionWithIdKey()
	{
		$menu = new Menu(
			areas: [
				$area = new Area(id: 'site')
			],
			config: [
				'site' => true
			]
		);

		$areas = $menu->areas();

		$this->assertCount(1, $areas);
		$this->assertSame($area, $areas[0]);
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasFromMenuOptionWithInvalidId()
	{
		$menu = new Menu(
			config: [
				'does-not-exist'
			]
		);

		$this->assertCount(0, $menu->areas());
	}

	/**
	 * @covers ::areas
	 */
	public function testAreasFromMenuOptionWithArray()
	{
		$menu = new Menu(
			areas: [
				$area = new Area(id: 'site')
			],
			config: [
				'site' => [
					'icon' => 'edit'
				]
			]
		);

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
		$menu = new Menu(
			config: [
				'todos' => [
					'label' => 'Todos',
					'link'  => 'todos'
				]
			]
		);

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
		$menu = new Menu(
			config: function ($kirby) use ($test) {
				$test->assertInstanceOf(App::class, $kirby);
				return null;
			}
		);

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
		$menu = new Menu(
			config: function () {
				return [];
			}
		);

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
	public function testItemsWithDivider()
	{
		$menu = new Menu(
			areas: [
				'site'   => new Area(id: 'site', menu: true),
				'system' => new Area(id: 'system', menu: true),
			],
			config: [
				'site',
				'-',
				'system',
			],
			permissions: [
				'access' => [
					'site'   => true,
					'system' => true
				]
			]
		);

		$items = $menu->items();

		$this->assertSame('site', $items[0]['props']['link']);
		$this->assertSame('-', $items[1]);
		$this->assertSame('system', $items[2]['props']['link']);
	}

	/**
	 * @covers ::items
	 */
	public function testItemsWithComponent()
	{
		$menu = new Menu(
			areas: [
				'site'   => new Area(id: 'site', menu: true),
				'system' => new Area(id: 'system', menu: true),
			],
			config: [
				'site',
				'test' => new MenuItem(
					icon: 'test',
					text: 'test',
					link: 'test'
				),
				'system',
			],
			permissions: [
				'access' => [
					'site'   => true,
					'system' => true
				]
			]
		);

		$items = $menu->items();

		$this->assertSame('site', $items[0]['props']['link']);
		$this->assertSame('test', $items[1]['props']['link']);
		$this->assertSame('system', $items[2]['props']['link']);
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
