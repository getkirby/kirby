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
        $site = new Site();
        $page = new Page(['slug' => 'test', 'site' => $site]);

        $site->{'set' . $key}($page);

        $this->assertTrue($page->$method());
    }

    /**
     * @dataProvider pageProvider
     */
    public function testNegativePageState($key, $method)
    {
        $site  = new Site();
        $pageA = new Page(['slug' => 'page-a', 'site' => $site]);
        $pageB = new Page(['slug' => 'page-b', 'site' => $site]);

        $site->{'set' . $key}($pageB);

        $this->assertFalse($pageA->$method());
    }

    public function testIsOpen()
    {
        $site   = new Site();
        $parent = new Page(['slug' => 'test', 'site' => $site]);
        $child  = new Page(['slug' => 'test/child', 'parent' => $parent, 'site' => $site]);

        $site->setPage($child);

        $this->assertTrue($parent->isOpen());
        $this->assertTrue($child->isOpen());
    }

    public function testIsNotOpen()
    {
        $site   = new Site();
        $parent = new Page(['slug' => 'test', 'site' => $site]);
        $child  = new Page(['slug' => 'test/child', 'parent' => $parent, 'site' => $site]);
        $active = new Page(['slug' => 'active', 'site' => $site]);

        $site->setPage($active);

        $this->assertFalse($parent->isOpen());
        $this->assertFalse($child->isOpen());
    }

    public function testIsVisible()
    {
        $page = new Page(['slug' => 'test']);

        $this->assertFalse($page->isVisible());

        $page = new Page(['slug' => 'test', 'num' => 1]);

        $this->assertTrue($page->isVisible());
    }

    public function testIsInvisible()
    {
        $page = new Page(['slug' => 'test']);

        $this->assertTrue($page->isInvisible());

        $page = new Page(['slug' => 'test', 'num' => 1]);

        $this->assertFalse($page->isInvisible());
    }

}
