<?php

namespace Kirby\Cms;

class SitePagesTest extends TestCase
{

    public function testErrorPage()
    {
        $site = new Site([
            'errorPage' => $page = new Page(['id' => 'error'])
        ]);

        $this->assertIsPage($site->errorPage(), $page);
    }

    public function testDefaultErrorPageWithChildren()
    {
        $site = new Site([
            'children' => new Pages([
                new Page(['id' => 'error'])
            ])
        ]);

        $this->assertIsPage($site->errorPage(), 'error');
    }

    public function testHomePage()
    {
        $site = new Site([
            'homePage' => $page = new Page(['id' => 'home'])
        ]);

        $this->assertIsPage($site->homePage(), $page);
    }

    public function testDefaultHomePageWithChildren()
    {
        $site = new Site([
            'children' => new Pages([
                new Page(['id' => 'home'])
            ])
        ]);

        $this->assertIsPage($site->homePage(), 'home');
    }

    public function testPage()
    {
        $site = new Site([
            'page' => $page = new Page(['id' => 'test'])
        ]);

        $this->assertIsPage($site->page(), $page);
    }

    public function testDefaultPageWithChildren()
    {
        $site = new Site([
            'children' => new Pages([
                new Page(['id' => 'home'])
            ])
        ]);

        $this->assertIsPage($site->page(), 'home');
    }

    public function testPageWithPathAndChildren()
    {
        $site = new Site([
            'children' => new Pages([
                new Page(['id' => 'test'])
            ])
        ]);

        $this->assertIsPage($site->page('test'), 'test');
    }

    public function testVisitWithPageObject()
    {
        $site = new Site();
        $page = $site->visit(new Page(['id' => 'test']));

        $this->assertIsPage($site->page(), 'test');
        $this->assertIsPage($site->page(), $page);
    }

    public function testVisitWithId()
    {
        $site = new Site([
            'children' => new Pages([
                new Page(['id' => 'test'])
            ])
        ]);

        $page = $site->visit('test');

        $this->assertIsPage($site->page(), 'test');
        $this->assertIsPage($site->page(), $page);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid page object
     */
    public function testVisitWithInvalidId()
    {
        $site = new Site([
            'children' => new Pages()
        ]);

        $site->visit('test');
    }

}
