<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Panel\Ui\MenuItem;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MenuItem::class)]
class MenuItemTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.MenuItem';

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
		Dir::remove(static::TMP);
	}

	public function test__ConstructUnsetLinkWhenDialogOrDrawer(): void
	{
		$item = new MenuItem(
			id: 'account',
			link: 'account',
			dialog: 'account',
		);

		$this->assertNull($item->link());
		$this->assertSame('account', $item->dialog());
	}

	public function testIsAlternative(): void
	{
		$item = new MenuItem(
			id: 'foo',
			disabled: true,
		);

		$this->assertFalse($item->isAlternative());

		$item = new MenuItem(
			id: 'foo',
			link: 'foo',
			dialog: 'foo',
		);

		$this->assertFalse($item->isAlternative());

		$item = new MenuItem(
			id: 'logout',
		);

		$this->assertFalse($item->isAlternative());

		$item = new MenuItem(
			id: 'foo',
			link: 'foo',
		);

		$this->assertTrue($item->isAlternative());
	}

	public function testProps(): void
	{
		$item = new MenuItem(
			id: 'page',
			icon: 'page',
			text: 'page'
		);

		$this->assertSame([
			'class'      => null,
			'style'      => null,
			'badge'      => null,
			'current'    => false,
			'dialog'     => null,
			'disabled'   => false,
			'drawer'     => null,
			'dropdown'   => null,
			'icon'       => 'page',
			'link'       => null,
			'responsive' => true,
			'size'       => null,
			'target'     => null,
			'text'       => 'Page',
			'theme'      => null,
			'title'      => null,
			'type'       => 'button',
			'variant'    => null,
		], $item->props());
	}
}
