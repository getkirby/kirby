<?php

namespace Kirby\Cms;

class PageChildrenTest extends TestCase
{

    public function testDefaultChildren()
    {
        $page = new Page(['id' => 'test']);
        $this->assertInstanceOf(Children::class, $page->children());
        $this->assertCount(0, $page->children());
    }

    public function testHasChildren()
    {
        $page = new Page([
            'id' => 'test',
            'children' => new Children([
                new Page(['id' => 'a']),
                new Page(['id' => 'b'])
            ])
        ]);

        $this->assertTrue($page->hasChildren());
    }

    public function testHasNoChildren()
    {
        $page = new Page([
            'id'       => 'test',
            'children' => new Children()
        ]);

        $this->assertFalse($page->hasChildren());
    }

}
