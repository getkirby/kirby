<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Panel\Ui\Button;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Menu::class)]
class MenuTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Menu';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'admin',
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');
	}

	public function testAreas(): void
	{
		// areas from Panel object
		$menu = new Menu();
		$this->assertInstanceOf(Areas::class, $menu->areas);
		$this->assertSame(['account', 'lab', 'logout', 'search', 'site', 'system', 'users'], $menu->areas->keys());

		// provided areas
		$areas = new Areas([new Area('foo'), new Area('bar')]);
		$menu = new Menu(areas: $areas);
		$this->assertSame(['foo', 'bar'], $menu->areas->keys());
	}

	public function testConfig(): void
	{
		// default config
		$menu = new Menu();
		$this->assertSame(['site', 'users', 'system', 'account', 'lab', 'logout', 'search'], $menu->config);

		// global config
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'menu' => function ($kirby) {
						$this->assertInstanceOf(App::class, $kirby);
						return ['fox', 'baz'];
					}
				]
			]
		]);

		$menu = new Menu();
		$this->assertSame(['fox', 'baz'], $menu->config);

		// provided config
		$menu = new Menu(config: ['foo', 'bar']);
		$this->assertSame(['foo', 'bar'], $menu->config);
	}

	public function testCurrent(): void
	{
		$menu = new Menu(current: 'account');
		$this->assertSame('account', $menu->current);
	}

	public function testDefaults(): void
	{
		$menu = new Menu();
		$this->assertSame(['site', 'users', 'system', 'account', 'lab', 'logout', 'search'], $menu->defaults());
	}

	public function testDefaultsWithCustomAreas(): void
	{
		$areas = new Areas([new Area('foo'), new Area('bar')]);
		$menu = new Menu(areas: $areas);
		$this->assertSame(['foo', 'bar'], $menu->defaults());

		$this->app = $this->app->clone([
			'areas' => [
				'fox' => [
					'label' => 'Fox',
					'link'  => 'fox'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$menu = new Menu();
		$this->assertSame(['site', 'users', 'system', 'account', 'lab', 'logout', 'search', 'fox'], $menu->defaults());
	}

	public function testHasPermission(): void
	{
		$menu = new Menu(permissions: []);
		$this->assertTrue($menu->hasPermission('account'));

		$menu = new Menu(permissions: ['access' => ['account' => true]]);
		$this->assertTrue($menu->hasPermission('account'));

		$menu = new Menu(permissions: ['access' => ['account' => false]]);
		$this->assertFalse($menu->hasPermission('account'));
	}

	public function testIsCurrent(): void
	{
		$menu = new Menu(current:'account');

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

	public function testItem(): void
	{
		$menu = new Menu();
		$item = $menu->item('users');
		$this->assertInstanceOf(Button::class, $item);
		$this->assertSame('users', $item->props()['link']);
		$this->assertSame('Users', $item->props()['text']);
		$this->assertFalse($item->props()['disabled']);
	}

	public function testItemWithExtraProps(): void
	{
		$menu = new Menu();
		$item = $menu->item('users', ['text' => 'Buddies']);
		$this->assertInstanceOf(Button::class, $item);
		$this->assertSame('users', $item->props()['link']);
		$this->assertSame('Buddies', $item->props()['text']);
	}

	public function testItemWithMenu(): void
	{
		$menu = new Menu();
		$item = $menu->item('users', ['menu' => function ($areas, $permissions, $current) {
			$this->assertInstanceOf(Areas::class, $areas);
			$this->assertIsArray($permissions);
			$this->assertNull($current);
			return 'disabled';
		}]);
		$this->assertInstanceOf(Button::class, $item);
		$this->assertSame('users', $item->props()['link']);
		$this->assertSame('Users', $item->props()['text']);
		$this->assertTrue($item->props()['disabled']);
	}

	public function testItemWithMenuFalse(): void
	{
		$menu = new Menu();
		$item = $menu->item('users', ['menu' => false]);
		$this->assertNull($item);
	}

	public function testItemWithNoPermission(): void
	{
		$menu = new Menu(permissions: ['access' => ['users' => false]]);
		$item = $menu->item('users');
		$this->assertNull($item);
	}

	public function testItemCurrent(): void
	{
		$menu = new Menu();
		$item = $menu->item('users');
		$this->assertFalse($item->props()['current']);

		$menu = new Menu(current: 'users');
		$item = $menu->item('users');
		$this->assertTrue($item->props()['current']);
	}

	public function testItems(): void
	{
		$menu = new Menu();
		$items = $menu->items();

		$this->assertCount(7, $items);
		$this->assertSame('site', $items[0]->props()['link']);
		$this->assertSame('users', $items[1]->props()['link']);
		$this->assertSame('system', $items[2]->props()['link']);
		$this->assertSame('-', $items[3]);
		$this->assertSame('changes', $items[4]->props()['dialog']);
		$this->assertSame('account', $items[5]->props()['link']);
		$this->assertSame('logout', $items[6]->props()['link']);
	}

	public function testItemsWithCustomConfig(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'menu' => [
						'foo' => [
							'label' => 'Heart',
							'link'  => 'heart'
						],
						'-',
						'nothing',
						'site' => [
							'icon' => 'home',
							'link' => 'site'
						],
						'users',
						'system' => true,
						'magic'  => new Button(link: 'magic')
					]
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$menu = new Menu();
		$items = $menu->items();

		$this->assertCount(10, $items);
		$this->assertSame('heart', $items[0]->props()['link']);
		$this->assertSame('-', $items[1]);
		$this->assertSame('home', $items[2]->props()['icon']);
		$this->assertSame('Site', $items[2]->props()['text']);
		$this->assertSame('site', $items[2]->props()['link']);
		$this->assertSame('users', $items[3]->props()['link']);
		$this->assertSame('system', $items[4]->props()['link']);
		$this->assertSame('magic', $items[5]->props()['link']);
		$this->assertSame('-', $items[6]);
		$this->assertSame('changes', $items[7]->props()['dialog']);
		$this->assertSame('account', $items[8]->props()['link']);
		$this->assertSame('logout', $items[9]->props()['link']);
	}
}
