<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
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

		$this->assertNull($item->toArray()['link']);
		$this->assertSame('account', $item->toArray()['dialog']);
	}

	public function testTextI18n(): void
	{
		$item = new MenuItem(
			id: 'account',
			text: 'page'
		);

		$this->assertSame('Page', $item->toArray()['text']);
	}

	public function testToArray(): void
	{
		$item = new MenuItem(
			id: 'account',
			icon: 'account',
			text: 'Account'
		);

		$this->assertSame([
			'current'  => false,
			'disabled' => false,
			'icon'     => 'account',
			'link'     => null,
			'dialog'   => null,
			'drawer'   => null,
			'target'   => null,
			'text'     => 'Account',
			'title'    => null,
		], $item->toArray());
	}
}
