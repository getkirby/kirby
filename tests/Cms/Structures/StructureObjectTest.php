<?php

namespace Kirby\Cms;

use TypeError;

class StructureObjectTest extends TestCase
{
	public function testId()
	{
		$object = new StructureObject(['id' => 'test']);
		$this->assertSame('test', $object->id());
	}

	public function testInvalidId()
	{
		$this->expectException(TypeError::class);
		new StructureObject(['id' => []]);
	}

	public function testContent()
	{
		$content = ['test' => 'Test'];
		$object  = new StructureObject([
			'id'      => 'test',
			'content' => $content
		]);

		$this->assertSame($content, $object->content()->toArray());
	}

	public function testToDate()
	{
		$object = new StructureObject([
			'id'      => 'test',
			'content' => [
				'date' => '2012-12-12'
			]
		]);

		$this->assertSame('12.12.2012', $object->date()->toDate('d.m.Y'));
	}

	public function testDefaultContent()
	{
		$object  = new StructureObject([
			'id' => 'test',
		]);

		$this->assertSame([], $object->content()->toArray());
	}

	public function testFields()
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

	public function testFieldsParent()
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

	public function testParent()
	{
		$parent = new Page(['slug' => 'test']);
		$object = new StructureObject([
			'id'     => 'test',
			'parent' => $parent
		]);

		$this->assertSame($parent, $object->parent());
	}

	public function testParentFallback()
	{
		$object = new StructureObject([
			'id'     => 'test',
		]);

		$this->assertIsSite($object->parent());
	}

	public function testInvalidParent()
	{
		$this->expectException(TypeError::class);

		$object = new StructureObject([
			'id'     => 'test',
			'parent' => false
		]);
	}

	public function testToArray()
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
