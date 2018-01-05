<?php

namespace Kirby\Cms;

class SiteChildrenTest extends TestCase
{

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultChildrenWithoutStore()
    {
        $site = new Site();
        $this->assertInstanceOf(Pages::class, $site->children());
    }

    public function testDefaultChildrenWithStore()
    {
        $store = new Store([
            'site.children' => function ($site) {
                return new Pages([], $site);
            }
        ]);

        $site = new Site([
            'store' => $store
        ]);

        $this->assertInstanceOf(Pages::class, $site->children());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "children" property must be of type "Kirby\Cms\Pages"
     */
    public function testInvalidChildren()
    {
        $site = new Site([
            'children' => 'children'
        ]);
    }

    public function testPages()
    {
        $site = new Site([
            'children' => $children = new Children()
        ]);

        $this->assertEquals($children, $site->pages());
        $this->assertEquals($site->children(), $site->pages());
    }

}
