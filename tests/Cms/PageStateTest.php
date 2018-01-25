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
        $page = new Page(['id' => 'test', 'site' => $site]);

        $site->{'set' . $key}($page);

        $this->assertTrue($page->$method());
    }

    /**
     * @dataProvider pageProvider
     */
    public function testNegativePageState($key, $method)
    {
        $site  = new Site();
        $pageA = new Page(['id' => 'page-a', 'site' => $site]);
        $pageB = new Page(['id' => 'page-b', 'site' => $site]);

        $site->{'set' . $key}($pageB);

        $this->assertFalse($pageA->$method());
    }

    public function testIsOpen()
    {
        $site   = new Site();
        $parent = new Page(['id' => 'test', 'site' => $site]);
        $child  = new Page(['id' => 'test/child', 'parent' => $parent, 'site' => $site]);

        $site->setPage($child);

        $this->assertTrue($parent->isOpen());
        $this->assertTrue($child->isOpen());
    }

    public function testIsNotOpen()
    {
        $site   = new Site();
        $parent = new Page(['id' => 'test', 'site' => $site]);
        $child  = new Page(['id' => 'test/child', 'parent' => $parent, 'site' => $site]);
        $active = new Page(['id' => 'active', 'site' => $site]);

        $site->setPage($active);

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
