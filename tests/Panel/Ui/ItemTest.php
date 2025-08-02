<?php

namespace Kirby\Panel\Ui;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Item::class)]
class ItemTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Item';

	public function testImage(): void
	{
		$item = new Item(
			text: 'test',
			image: [
				'back' => 'green',
			]
		);

		$this->assertSame('green', $item->image()['back']);
	}

	public function testInfo(): void
	{
		$item = new Item(
			text: 'test',
			info: 'Test'
		);

		$this->assertSame('Test', $item->info());
	}

	public function testProps(): void
	{
		$item = new Item(
			text: 'Text',
			info: 'Info',
			image: $image = [
				'back' => 'green',
				'cover' => true,
				'icon' => 'page',
			],
			layout: 'cards'
		);

		$expected = [
			'image'  => $image,
			'info'   => 'Info',
			'layout' => 'cards',
			'text'   => 'Text',
		];

		$this->assertSame($expected, $item->props());
	}

	public function testRender(): void
	{
		$item = new Item(text: 'test');
		$item = $item->render();
		$this->assertSame('k-item', $item['component']);
		$this->assertArrayHasKey('props', $item);
	}

	public function testText(): void
	{
		$item = new Item(
			text: 'Test'
		);

		$this->assertSame('Test', $item->text());
	}
}
