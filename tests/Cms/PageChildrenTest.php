<?php

namespace Kirby\Cms;

class PageChildrenTest extends TestCase
{

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultChildrenWithoutStore()
    {
        $page = new Page(['id' => 'test']);
        $this->assertInstanceOf(Children::class, $page->children());
    }

    public function testDefaultChildrenWithStore()
    {
        $store = new Store([
            'page.children' => function ($page) {
                return new Children([], $page);
            }
        ]);

        $page = new Page([
            'id'    => 'test',
            'store' => $store
        ]);

        $this->assertInstanceOf(Children::class, $page->children());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "children" attribute must be of type "Kirby\Cms\Children"
     */
    public function testInvalidChildren()
    {
        $page = new Page([
            'id'       => 'test',
            'children' => 'children'
        ]);
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
