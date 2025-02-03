<?php

namespace Kirby\Panel\Ui;

use Kirby\Exception\Exception;
use Kirby\TestCase;
use Kirby\Toolkit\I18n;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\MenuItem
 */
class MenuItemTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$menuItem = new MenuItem(
			icon: 'edit',
			link: 'site',
			text: 'Test',
		);

		$this->assertSame('edit', $menuItem->icon());
		$this->assertSame('Test', $menuItem->text());
		$this->assertFalse($menuItem->current());
		$this->assertNull($menuItem->dialog());
		$this->assertFalse($menuItem->disabled());
		$this->assertNull($menuItem->drawer());
		$this->assertSame('site', $menuItem->link());
	}

	public function testConstructWithMissingLink()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('You must define a dialog, drawer or link for the menu item');

		new MenuItem(
			icon: 'edit',
			text: 'Test',
		);
	}

	/**
	 * @covers ::__call
	 */
	public function testCall()
	{
		$menuItem = new MenuItem(
			icon: 'edit',
			link: 'test',
			text: 'Test',
		);

		$this->assertSame('edit', $menuItem->icon());
		$this->assertSame('test', $menuItem->link());
		$this->assertSame('Test', $menuItem->text());
	}

	public function testCurrent()
	{
		$menuItem = new MenuItem(
			icon: 'edit',
			link: 'test',
			text: 'Test',
		);

		$this->assertFalse($menuItem->current());

		$menuItem = new MenuItem(
			current: true,
			icon: 'edit',
			link: 'test',
			text: 'Test',
		);

		$this->assertTrue($menuItem->current());
	}

	public function testDialog()
	{
		$menuItem = new MenuItem(
			icon: 'edit',
			dialog: 'test',
			text: 'Test',
		);

		$this->assertSame('test', $menuItem->dialog());
	}

	public function testDisabled()
	{
		$menuItem = new MenuItem(
			icon: 'edit',
			link: 'test',
			text: 'Test',
		);

		$this->assertFalse($menuItem->disabled());

		$menuItem = new MenuItem(
			disabled: true,
			icon: 'edit',
			link: 'test',
			text: 'Test',
		);

		$this->assertTrue($menuItem->disabled());
	}

	public function testDrawer()
	{
		$menuItem = new MenuItem(
			icon: 'edit',
			drawer: 'test',
			text: 'Test',
		);

		$this->assertSame('test', $menuItem->drawer());
	}

	/**
	 * @covers ::link
	 */
	public function testLink()
	{
		$menuItem = new MenuItem(
			icon: 'edit',
			link: 'test',
			text: 'Test',
		);

		$this->assertSame('test', $menuItem->link());
	}

	/**
	 * @covers ::link
	 */
	public function testLinkWithDialog()
	{
		$menuItem = new MenuItem(
			dialog: 'test',
			icon: 'edit',
			link: 'test',
			text: 'Test',
		);

		$this->assertSame('test', $menuItem->dialog());
		$this->assertNull($menuItem->link());
	}

	/**
	 * @covers ::link
	 */
	public function testLinkWithDrawer()
	{
		$menuItem = new MenuItem(
			drawer: 'test',
			icon: 'edit',
			link: 'test',
			text: 'Test',
		);

		$this->assertSame('test', $menuItem->drawer());
		$this->assertNull($menuItem->link());
	}

	/**
	 * @covers ::merge
	 */
	public function testMerge()
	{
		$menuItem = new MenuItem(
			icon: 'edit',
			link: 'test',
			text: 'Test',
		);

		$this->assertFalse($menuItem->disabled());

		$menuItem->merge([
			'disabled' => true
		]);

		$this->assertTrue($menuItem->disabled());
	}

	/**
	 * @covers ::text
	 */
	public function testText()
	{
		$menuItem = new MenuItem(
			icon: 'edit',
			link: 'test',
			text: 'Test',
		);

		$this->assertSame('Test', $menuItem->text());
	}

	/**
	 * @covers ::text
	 */
	public function testTextWithTranslationKey()
	{
		I18n::$translations = [
			'en' => [
				'logout' => 'Logout'
			]
		];

		$menuItem = new MenuItem(
			icon: 'edit',
			link: 'test',
			text: 'logout',
		);

		$this->assertSame('Logout', $menuItem->text());
	}

	/**
	 * @covers ::text
	 */
	public function testTextWithTranslationArray()
	{
		$menuItem = new MenuItem(
			icon: 'edit',
			link: 'test',
			text: [
				'en' => 'Logout',
				'de' => 'Abmelden'
			],
		);

		$this->assertSame('Logout', $menuItem->text());
	}

	/**
	 * @covers ::toArray
	 */
	public function testToArray()
	{
		$menuItem = new MenuItem(
			icon: 'edit',
			link: 'site',
			text: 'Test',
		);

		$expected = [
			'icon' => 'edit',
			'link' => 'site',
			'text' => 'Test'
		];

		$this->assertSame($expected, $menuItem->toArray());
	}
}
