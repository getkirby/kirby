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

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultErrorPageWithoutStore()
    {
        $site = new Site();
        $site->errorPage();
    }

    public function testDefaultErrorPageWithStoreButMissingErrorPage()
    {
        $site = new Site([
            'store' => new Store([
                'site.children' => function ($site) {
                    return new Pages([], $site);
                }
            ])
        ]);

        $this->assertNull($site->errorPage());
    }

    public function testDefaultErrorPageWithStoreAndErrorPage()
    {
        $site = new Site([
            'store' => new Store([
                'site.children' => function ($site) {
                    return new Pages([
                        new Page(['id' => 'error'])
                    ], $site);
                }
            ])
        ]);

        $this->assertIsPage($site->errorPage(), 'error');
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

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultHomePageWithoutStore()
    {
        $site = new Site();
        $site->homePage();
    }

    public function testDefaultHomePageWithStoreButMissingHomePage()
    {
        $site = new Site([
            'store' => new Store([
                'site.children' => function ($site) {
                    return new Pages([], $site);
                }
            ])
        ]);

        $this->assertEquals(null, $site->homePage());
    }

    public function testDefaultHomePageWithStoreAndHomePage()
    {
        $site = new Site([
            'store' => new Store([
                'site.children' => function ($site) {
                    return new Pages([
                        new Page(['id' => 'home'])
                    ], $site);
                }
            ])
        ]);

        $this->assertIsPage($site->homePage(), 'home');
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

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultPageWithoutStore()
    {
        $site = new Site();
        $site->page();
    }

    public function testDefaultPageWithStoreButMissingHomePage()
    {
        $site = new Site([
            'store' => new Store([
                'site.children' => function ($site) {
                    return new Pages([], $site);
                }
            ])
        ]);

        $this->assertEquals(null, $site->page());
    }

    public function testDefaultPageWithStoreAndHomePage()
    {
        $site = new Site([
            'store' => new Store([
                'site.children' => function ($site) {
                    return new Pages([
                        new Page(['id' => 'home'])
                    ], $site);
                }
            ])
        ]);

        $this->assertIsPage($site->page(), 'home');
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

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testPageWithPathAndMissingStore()
    {
        $site = new Site();
        $site->page('test');
    }

    public function testPageWithPathAndStoreButMissingPage()
    {
        $site = new Site([
            'store' => new Store([
                'site.children' => function ($site) {
                    return new Pages([], $site);
                }
            ])
        ]);

        $this->assertNull($site->page('test'));
    }

    public function testPageWithPathAndStore()
    {
        $site = new Site([
            'store' => new Store([
                'site.children' => function ($site) {
                    return new Pages([
                        new Page(['id' => 'test'])
                    ], $site);
                }
            ])
        ]);

        $this->assertIsPage($site->page('test'), 'test');
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
