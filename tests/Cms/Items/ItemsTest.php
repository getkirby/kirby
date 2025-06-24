<?php

namespace Kirby\Cms;

use Kirby\Content\Field;
use Kirby\TestCase;

class ItemsTest extends TestCase
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
		$items = new Items();

		$a = new Item(['type' => 'a']);
		$b = new Item(['type' => 'b']);

		$items->append($a->id(), $a);
		$items->append($b->id(), $b);

		$this->assertCount(2, $items);
		$this->assertSame($a->id(), $items->first()->id());
		$this->assertSame($b->id(), $items->last()->id());
	}

	public function testFactoryFromArray()
	{
		$items = Items::factory(
			[
				[
					'id' => 'a',
				],
				[
					'id' => 'b',
				]
			],
			[
				'parent' => $this->page,
				'field'  => $this->field
			]
		);

		$this->assertCount(2, $items);
		$this->assertSame($items, $items->first()->siblings());
		$this->assertSame('a', $items->first()->id());
		$this->assertSame('b', $items->last()->id());
		$this->assertSame($this->page, $items->first()->parent());
		$this->assertSame($this->field, $items->first()->field());
	}

	public function testField()
	{
		$items = new Items([]);

		$this->assertNull($items->field());

		$items = new Blocks([], [
			'parent' => $this->page,
			'field'  => $this->field
		]);

		$this->assertSame($this->field, $items->field());
	}

	public function testParent()
	{
		$items = new Items([]);

		$this->assertSame($this->app->site(), $items->parent());

		$items = new Blocks([], [
			'parent' => $this->page
		]);

		$this->assertSame($this->page, $items->parent());
	}

	public function testToArray()
	{
		$items = new Items();

		$a = new Item(['id' => 'a']);
		$b = new Item(['id' => 'b']);

		$items->append($a->id(), $a);
		$items->append($b->id(), $b);

		$expected = [
			$a->toArray(),
			$b->toArray(),
		];

		$this->assertSame($expected, $items->toArray());
	}
}
