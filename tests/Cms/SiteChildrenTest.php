<?php

namespace Kirby\Cms;

class SiteChildrenTest extends TestCase
{
    public function testDefaultChildren()
    {
        $site = new Site();
        $this->assertInstanceOf(Pages::class, $site->children());
    }

    /**
     * @expectedException TypeError
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
            'children' => []
        ]);

        $this->assertInstanceOf(Pages::class, $site->children());
    }
}
