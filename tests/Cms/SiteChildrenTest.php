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
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Site::setChildren() must be an instance of Kirby\Cms\Pages or null, string given
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
            'children' => $pages = new Pages()
        ]);

        $this->assertInstanceOf(Pages::class, $site->children());
    }

}
