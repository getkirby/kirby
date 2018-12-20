<?php

namespace Kirby\Cms;

class PageChildrenTest extends TestCase
{
    public function testDefaultChildren()
    {
        $page = new Page(['slug' => 'test']);
        $this->assertInstanceOf(Pages::class, $page->children());
        $this->assertCount(0, $page->children());
    }

    public function testHasChildren()
    {
        $page = new Page([
            'slug' => 'test',
            'children' => [
                ['slug' => 'a'],
                ['slug' => 'b']
            ]
        ]);

        $this->assertTrue($page->hasChildren());
    }

    public function testHasNoChildren()
    {
        $page = new Page([
            'slug'     => 'test',
            'children' => []
        ]);

        $this->assertFalse($page->hasChildren());
    }
}
