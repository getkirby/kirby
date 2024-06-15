<?php

namespace Kirby\Cms;

use Kirby\Content\Field;
use Kirby\TestCase;

class ItemTest extends TestCase
{
	protected Field $field;
	protected Page $page;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
		]);

		$this->page  = new Page(['slug' => 'test']);
		$this->field = new Field($this->page, 'test', 'abcde');
	}

	public function testConstruct()
	{
		$item = new Item();

		$this->assertNotNull($item->id());
		$this->assertSame($this->app, $item->kirby());
		$this->assertIsSite($item->parent());
		$this->assertInstanceOf(Items::class, $item->siblings());
	}

	public function testField()
	{
		$item = new Item([
			'parent' => $this->page,
			'field'  => $this->field
		]);

		$this->assertSame($this->field, $item->field());
	}

	public function testIs()
	{
		$a = new Item(['name' => 'a']);
		$b = new Item(['name' => 'b']);

		$this->assertTrue($a->is($a));
		$this->assertFalse($a->is($b));
	}

	public function testParent()
	{
		$item = new Item([
			'parent' => $this->page,
		]);

		$this->assertSame($this->page, $item->parent());
	}

	public function testSiblings()
	{
		$items = Items::factory([
			['type' => 'a'],
			['type' => 'b'],
		]);

		$item = new Item([
			'siblings' => $items,
			'type'     => 'c'
		]);

		$this->assertSame($items, $item->siblings());
	}

	public function testToArray()
	{
		$item = new Item();
		$this->assertSame([
			'id' => $item->id(),
		], $item->toArray());
	}
}
