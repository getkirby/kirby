<?php

namespace Kirby\Cms;

class PageStateTest extends TestCase
{

    public function pageProvider()
    {
        return [
            ['page', 'isActive'],
            ['errorPage', 'isErrorPage'],
            ['homePage', 'isHomePage']
        ];
    }

    /**
     * @dataProvider pageProvider
     */
    public function testPageState($key, $method)
    {
        $page = new Page(['id' => 'test']);
        $site = new Site([$key => $page]);

        Page::use('site', $site);

        $this->assertTrue($page->$method());
    }

    /**
     * @dataProvider pageProvider
     */
    public function testNegativePageState($key, $method)
    {
        $pageA = new Page(['id' => 'page-a']);
        $pageB = new Page(['id' => 'page-b']);
        $site  = new Site([$key => $pageB]);

        Page::use('site', $site);

        $this->assertFalse($pageA->$method());
    }

    public function testIsOpen()
    {
        $parent = new Page(['id' => 'test']);
        $child  = new Page(['id' => 'test/child', 'parent' => $parent]);
        $site   = new Site(['page' => $child]);

        Page::use('site', $site);

        $this->assertTrue($parent->isOpen());
        $this->assertTrue($child->isOpen());
    }

    public function testIsNotOpen()
    {
        $parent = new Page(['id' => 'test']);
        $child  = new Page(['id' => 'test/child', 'parent' => $parent]);
        $active = new Page(['id' => 'active']);
        $site   = new Site(['page' => $active]);

        Page::use('site', $site);

        $this->assertFalse($parent->isOpen());
        $this->assertFalse($child->isOpen());
    }

    public function testIsVisible()
    {
        $page = new Page(['id' => 'test']);

        $this->assertFalse($page->isVisible());

        $page = new Page(['id' => 'test', 'num' => 1]);

        $this->assertTrue($page->isVisible());
    }

    public function testIsInvisible()
    {
        $page = new Page(['id' => 'test']);

        $this->assertTrue($page->isInvisible());

        $page = new Page(['id' => 'test', 'num' => 1]);

        $this->assertFalse($page->isInvisible());
    }

}
