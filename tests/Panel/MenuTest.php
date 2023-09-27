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
				'foo' => [
					'label' => 'Bar',
					'link'  => 'heart'
				],
				'site' => [
					'icon'  => 'home',
					'label' => 'Site',
					'link'  => 'site'
				]
			]
		);
		$areas = $menu->areas();
		$this->assertSame(['site', 'foo'], array_column($areas, 'id'));
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
		$this->assertSame('site', $entries[0]['link']);
		$this->assertTrue($entries[0]['current']);
		$this->assertSame('-', $entries[1]);
		$this->assertSame('registration', $entries[2]['dialog']);
		$this->assertSame('changes', $entries[3]['dialog']);
		$this->assertSame('account', $entries[4]['link']);
		$this->assertSame('logout', $entries[5]['link']);
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
		$registration = [
			'icon'     => 'key',
			'dialog'   => 'registration',
			'text'     => 'Register',
			'variant'  => 'filled',
			'theme'    => 'notice'
		];

		$changes = [
			'icon'     => 'edit-line',
			'dialog'   => 'changes',
			'text'     => 'Changes'
		];

		$account = [
			'current'  => false,
			'icon'     => 'account',
			'link'     => 'account',
			'disabled' => false,
			'text'     => 'Your account'
		];

		$logout = [
			'icon' => 'logout',
			'link' => 'logout',
			'text' => 'Log out'
		];

		$menu    = new Menu();
		$options = $menu->options();
		$this->assertSame($registration, $options[0]);
		$this->assertSame($changes, $options[1]);
		$this->assertSame($account, $options[2]);
		$this->assertSame($logout, $options[3]);
	}
}
