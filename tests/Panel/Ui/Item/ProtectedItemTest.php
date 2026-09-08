<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProtectedItem::class)]
class ProtectedItemTest extends TestCase
{
	public function testProps(): void
	{
		$item = new ProtectedItem('page://my-uuid');

		$this->assertSame([
			'image' => [
				'back'  => 'pattern',
				'color' => 'gray-500',
				'cover' => false,
				'icon'  => 'protected'
			],
			'info'        => null,
			'layout'      => 'list',
			'text'        => '–',
			'id'          => 'page://my-uuid',
			'link'        => false,
			'permissions' => [],
			'uuid'        => 'page://my-uuid',
		], $item->props());
	}

	public function testPropsWithLayout(): void
	{
		$item = new ProtectedItem('page://my-uuid', layout: 'cards');
		$this->assertSame('cards', $item->props()['layout']);
	}
}
