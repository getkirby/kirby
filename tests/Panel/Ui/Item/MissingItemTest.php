<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MissingItem::class)]
class MissingItemTest extends TestCase
{
	public function testProps(): void
	{
		$item = new MissingItem('page://my-uuid');

		$this->assertSame([
			'image' => [
				'back'  => 'pattern',
				'color' => 'red-600',
				'cover' => false,
				'icon'  => 'alert'
			],
			'info'        => null,
			'layout'      => 'list',
			'text'        => 'page://my-uuid',
			'id'          => 'page://my-uuid',
			'link'        => false,
			'permissions' => [],
			'uuid'        => 'page://my-uuid',
		], $item->props());
	}

	public function testPropsWithLayout(): void
	{
		$item = new MissingItem('page://my-uuid', layout: 'cards');
		$this->assertSame('cards', $item->props()['layout']);
	}
}
