<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class ItemsTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
        ]);

        $this->page = new Page(['slug' => 'test']);
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
        $items = Items::factory([
            [
                'id' => 'a',
            ],
            [
                'id' => 'b',
            ]
        ]);

        $this->assertCount(2, $items);
        $this->assertSame($items, $items->first()->siblings());
        $this->assertEquals('a', $items->first()->id());
        $this->assertEquals('b', $items->last()->id());
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
