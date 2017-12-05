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

}
