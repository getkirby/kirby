<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class LayoutsTest extends TestCase
{
	public function testFactory(): void
	{
		$layouts = Layouts::factory([
			[
				'columns' => [
					[
						'width' => '1/2'
					],
					[
						'width' => '1/2'
					]
				]
			]
		]);

		$this->assertInstanceOf(Layout::class, $layouts->first());
		$this->assertSame('1/2', $layouts->first()->columns()->first()->width());
	}

	public function testFactoryIsWrappingBlocks(): void
	{
		$layouts = Layouts::factory([
			[
				'type'    => 'heading',
				'content' => ['text' => 'Heading'],
			],
			[
				'type'    => 'text',
				'content' => ['text' => 'Text'],
			]
		]);

		$this->assertInstanceOf(Layout::class, $layouts->first());

		$columns = $layouts->first()->columns();
		$blocks  = $columns->first()->blocks();

		$this->assertSame('heading', $blocks->first()->type());
		$this->assertInstanceOf(Field::class, $blocks->first()->text());
		$this->assertSame('Heading', $blocks->first()->text()->value());
		$this->assertSame('text', $blocks->last()->type());
		$this->assertInstanceOf(Field::class, $blocks->last()->text());
		$this->assertSame('Text', $blocks->last()->text()->value());
	}

	public function testHasBlockType(): void
	{
		$layouts = Layouts::factory([
			[
				'type'    => 'heading',
				'content' => ['text' => 'Heading'],
			],
			[
				'type'    => 'text',
				'content' => ['text' => 'Text'],
			]
		]);

		$this->assertTrue($layouts->hasBlockType('heading'));
		$this->assertFalse($layouts->hasBlockType('code'));
	}

	public function testParse(): void
	{
		$data = [
			[
				'type'    => 'heading',
				'content' => ['text' => 'Heading'],
			],
			[
				'type'    => 'text',
				'content' => ['text' => 'Text'],
			]
		];
		$json = json_encode($data);

		$result = Layouts::parse($json);
		$this->assertSame($data, $result);
	}

	public function testParseArray(): void
	{
		$data = [
			[
				'type'    => 'heading',
				'content' => ['text' => 'Heading'],
			],
			[
				'type'    => 'text',
				'content' => ['text' => 'Text'],
			]
		];

		$result = Layouts::parse($data);
		$this->assertSame($data, $result);
	}

	public function testParseEmpty(): void
	{
		$result = Layouts::parse(null);
		$this->assertSame([], $result);

		$result = Layouts::parse('');
		$this->assertSame([], $result);

		$result = Layouts::parse('[]');
		$this->assertSame([], $result);

		$result = Layouts::parse([]);
		$this->assertSame([], $result);

		$result = Layouts::parse('invalid json string');
		$this->assertSame([], $result);
	}

	public function testToBlocks(): void
	{
		$data = [
			[
				'type'    => 'heading',
				'content' => ['text' => 'Heading'],
			],
			[
				'type'    => 'text',
				'content' => ['text' => 'Text'],
			]
		];

		$blocks = Layouts::factory($data)->toBlocks();

		$this->assertCount(2, $blocks);
		$this->assertInstanceOf(Blocks::class, $blocks);
	}

	public function testHiddenBlocks(): void
	{
		$data = [
			[
				'type'     => 'heading',
				'content'  => ['text' => 'Heading'],
			],
			[
				'type'     => 'text',
				'content'  => ['text' => 'Text'],
				'isHidden' => true,
			]
		];

		$layouts = Layouts::factory($data);

		$this->assertCount(1, $layouts->toBlocks());
		$this->assertCount(2, $layouts->toBlocks(true));
	}
}
