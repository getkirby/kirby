<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

class StructureTest extends TestCase
{
	public function testCreate()
	{
		$structure = new Structure([
			['test' => 'Test']
		]);

		$this->assertInstanceOf(StructureObject::class, $structure->first());
		$this->assertSame('0', $structure->first()->id());
	}

	public function testParent()
	{
		$parent    = new Page(['slug' => 'test']);
		$structure = new Structure([
			['test' => 'Test']
		], $parent);

		$this->assertSame($parent, $structure->first()->parent());
	}

	public function testToArray()
	{
		$data = [
			['name' => 'A'],
			['name' => 'B']
		];

		$expected = [
			['id' => '0', 'name' => 'A'],
			['id' => '1', 'name' => 'B'],
		];

		$structure = new Structure($data);

		$this->assertSame($expected, $structure->toArray());
	}

	public function testGroup()
	{
		$structure = new Structure([
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

	public function testSiblings()
	{
		$structure = new Structure([
			['name' => 'A'],
			['name' => 'B'],
			['name' => 'C']
		]);

		$this->assertInstanceOf(Field::class, $structure->first()->name());
		$this->assertInstanceOf(Field::class, $structure->first()->next()->name());
		$this->assertInstanceOf(Field::class, $structure->last()->name());
		$this->assertInstanceOf(Field::class, $structure->last()->prev()->name());
		$this->assertSame('A', $structure->first()->name()->value());
		$this->assertSame('B', $structure->first()->next()->name()->value());
		$this->assertSame('C', $structure->last()->name()->value());
		$this->assertSame('B', $structure->last()->prev()->name()->value());

		$this->assertSame(2, $structure->last()->indexOf());

		$this->assertTrue($structure->first()->isFirst());
		$this->assertTrue($structure->last()->isLast());
		$this->assertFalse($structure->last()->isFirst());
		$this->assertFalse($structure->first()->isLast());
	}

	public function testWithInvalidData()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid structure data');

		$structure = new Structure([
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
