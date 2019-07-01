<?php

namespace Kirby\Cms;

class SitePagesTest extends TestCase
{
    public function testErrorPage()
    {
        $site = new Site([
            'children' => [
                ['slug' => 'error']
            ]
        ]);

        $this->assertIsPage($site->errorPage(), 'error');
    }

    public function testHomePage()
    {
        $site = new Site([
            'children' => [
                ['slug' => 'home']
            ]
        ]);

        $this->assertIsPage($site->homePage(), 'home');
    }

    public function testPage()
    {
        $site = new Site([
            'page' => $page = new Page(['slug' => 'test'])
        ]);

        $this->assertIsPage($site->page(), $page);
    }

    public function testDefaultPageWithChildren()
    {
        $site = new Site([
            'children' => [
                ['slug' => 'home']
            ]
        ]);

        $this->assertIsPage($site->page(), 'home');
    }

    public function testPageWithPathAndChildren()
    {
        $site = new Site([
            'children' => [
                ['slug' => 'test']
            ]
        ]);

        $this->assertIsPage($site->page('test'), 'test');
    }

    public function testVisitWithPageObject()
    {
        $site = new Site();
        $page = $site->visit(new Page(['slug' => 'test']));

        $this->assertIsPage($site->page(), 'test');
        $this->assertIsPage($site->page(), $page);
    }

    public function testVisitWithId()
    {
        $site = new Site([
            'children' => [
                ['slug' => 'test']
            ]
        ]);

        $page = $site->visit('test');

        $this->assertIsPage($site->page(), 'test');
        $this->assertIsPage($site->page(), $page);
    }
}
