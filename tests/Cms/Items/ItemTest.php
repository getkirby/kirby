<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
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
        $item = new Item();

        $this->assertNotNull($item->id());
        $this->assertSame($this->app, $item->kirby());
        $this->assertInstanceOf('Kirby\Cms\Site', $item->parent());
        $this->assertInstanceOf('Kirby\Cms\Items', $item->siblings());
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
