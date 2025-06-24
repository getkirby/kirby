<?php

namespace Kirby\Cms;

use TypeError;

class StructureObjectTest extends TestCase
{
	public function testId(): void
	{
		$object = new StructureObject(['id' => 'test']);
		$this->assertSame('test', $object->id());
	}

	public function testInvalidId(): void
	{
		$this->expectException(TypeError::class);
		new StructureObject(['id' => []]);
	}

	public function testContent(): void
	{
		$content = ['test' => 'Test'];
		$object  = new StructureObject([
			'id'      => 'test',
			'content' => $content
		]);

		$this->assertSame($content, $object->content()->toArray());
	}

	public function testToDate(): void
	{
		$object = new StructureObject([
			'id'      => 'test',
			'content' => [
				'date' => '2012-12-12'
			]
		]);

		$this->assertSame('12.12.2012', $object->date()->toDate('d.m.Y'));
	}

	public function testDefaultContent(): void
	{
		$object  = new StructureObject([
			'id' => 'test',
		]);

		$this->assertSame([], $object->content()->toArray());
	}

	public function testFields(): void
	{
		$object = new StructureObject([
			'id'      => 'test',
			'content' => [
				'title' => 'Title',
				'text'  => 'Text'
			]
		]);

		$this->assertInstanceOf(Field::class, $object->title());
		$this->assertInstanceOf(Field::class, $object->text());

		$this->assertSame('Title', $object->title()->value());
		$this->assertSame('Text', $object->text()->value());
	}

	public function testFieldsParent(): void
	{
		$parent = new Page(['slug' => 'test']);
		$object = new StructureObject([
			'id'      => 'test',
			'content' => [
				'title' => 'Title',
				'text'  => 'Text'
			],
			'parent' => $parent
		]);

		$this->assertSame($parent, $object->title()->parent());
		$this->assertSame($parent, $object->text()->parent());
	}

	public function testParent(): void
	{
		$parent = new Page(['slug' => 'test']);
		$object = new StructureObject([
			'id'     => 'test',
			'parent' => $parent
		]);

		$this->assertSame($parent, $object->parent());
	}

	public function testParentFallback(): void
	{
		$object = new StructureObject([
			'id'     => 'test',
		]);

		$this->assertIsSite($object->parent());
	}

	public function testInvalidParent(): void
	{
		$this->expectException(TypeError::class);

		$object = new StructureObject([
			'id'     => 'test',
			'parent' => false
		]);
	}

	public function testToArray(): void
	{
		$content = [
			'title' => 'Title',
			'text'  => 'Text'
		];

		$expected = [
			'title' => 'Title',
			'text'  => 'Text',
			'id'    => 'test',
		];

		$object = new StructureObject([
			'id'      => 'test',
			'content' => $content
		]);

		$this->assertSame($expected, $object->toArray());
	}
}
