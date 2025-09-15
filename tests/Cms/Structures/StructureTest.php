<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

class StructureTest extends TestCase
{
	public function testCreate(): void
	{
		$structure = Structure::factory([
			['test' => 'Test']
		]);

		$this->assertInstanceOf(StructureObject::class, $structure->first());
		$this->assertSame(1, $structure->count());
	}

	public function testParent(): void
	{
		$parent    = new Page(['slug' => 'test']);
		$structure = Structure::factory([
			['test' => 'Test']
		], ['parent' => $parent]);

		$this->assertSame($parent, $structure->first()->parent());
	}

	public function testToArray(): void
	{
		$data = [
			['name' => 'A', 'field' => 'C'],
			['name' => 'B', 'field' => 'D']
		];
		$structure = Structure::factory($data)->toArray();

		$this->assertSame('A', $structure[0]['name']);
		$this->assertSame('C', $structure[0]['field']);
		$this->assertArrayHasKey('id', $structure[0]);
		$this->assertSame('B', $structure[1]['name']);
		$this->assertSame('D', $structure[1]['field']);
		$this->assertArrayHasKey('id', $structure[1]);
	}

	public function testGroup(): void
	{
		$structure = Structure::factory([
			[
				'name' => 'A',
				'category' => 'cat-a'
			],
			[
				'name' => 'B',
				'category' => 'cat-b'
			],
			[
				'name' => 'C',
				'category' => 'cat-a'
			]
		]);

		$grouped = $structure->group('category');

		$this->assertCount(2, $grouped);
		$this->assertCount(2, $grouped->first());
		$this->assertCount(1, $grouped->last());

		$this->assertInstanceOf(Field::class, $grouped->first()->first()->name());
		$this->assertInstanceOf(Field::class, $grouped->first()->last()->name());
		$this->assertSame('A', $grouped->first()->first()->name()->value());
		$this->assertSame('C', $grouped->first()->last()->name()->value());

		$this->assertInstanceOf(Field::class, $grouped->last()->first()->name());
		$this->assertSame('B', $grouped->last()->first()->name()->value());
	}

	public function testSiblings(): void
	{
		$structure = Structure::factory([
			['name' => 'A'],
			['name' => 'B'],
			['name' => 'C']
		]);

		$this->assertInstanceOf(Field::class, $structure->first()->name());
		$this->assertInstanceOf(Field::class, $structure->first()->next()->name());
		$this->assertInstanceOf(Field::class, $structure->last()->name());
		$this->assertInstanceOf(Field::class, $structure->last()->prev()->name());
		$this->assertSame('A', $structure->first()->name()->value());
		$this->assertSame('0', $structure->first()->id());
		$this->assertSame('B', $structure->first()->next()->name()->value());
		$this->assertSame('1', $structure->first()->next()->id());
		$this->assertSame('C', $structure->last()->name()->value());
		$this->assertSame('2', $structure->last()->id());
		$this->assertSame('B', $structure->last()->prev()->name()->value());

		$this->assertSame(2, $structure->last()->indexOf());

		$this->assertTrue($structure->first()->isFirst());
		$this->assertTrue($structure->last()->isLast());
		$this->assertFalse($structure->last()->isFirst());
		$this->assertFalse($structure->first()->isLast());
	}

	public function testWithInvalidData(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid data for Kirby\Cms\StructureObject');

		Structure::factory([
			[
				'name' => 'A',
				'category' => 'cat-a'
			],
			[
				'name' => 'B',
				'category' => 'cat-b'
			],
			'name',
			'category'
		]);
	}
}
