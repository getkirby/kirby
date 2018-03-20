<?php

namespace Kirby\Cms;

class FakePageStore extends PageStoreDefault
{

    public function exists(): bool
    {
        return true;
    }

}

class PageRulesTest extends TestCase
{

    public function appWithAdmin()
    {
        return new App([
            'user' => 'test@getkirby.com',
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ]);
    }

    public function testChangeNum()
    {
        $page = new Page([
            'slug'  => 'test',
            'kirby' => $this->appWithAdmin(),
        ]);

        $this->assertTrue(PageRules::changeNum($page, 2));
        $this->assertTrue(PageRules::changeNum($page));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The page order number cannot be negative
     */
    public function testInvalidChangeNum()
    {
        $page = new Page([
            'slug'  => 'test',
            'kirby' => $this->appWithAdmin(),
        ]);

        PageRules::changeNum($page, -1);
    }

    public function testChangeSlug()
    {
        $page = new Page([
            'slug'  => 'test',
            'kirby' => $this->appWithAdmin(),
        ]);

        $this->assertTrue(PageRules::changeSlug($page, 'test-a'));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The slug for this page cannot be changed
     */
    public function testChangeSlugWithHomepage()
    {
        $site = new Site();
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'site'  => $site
        ]);

        $site->setHomepage($page);
        PageRules::changeSlug($page, 'test-a');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The slug for this page cannot be changed
     */
    public function testChangeSlugWithErrorPage()
    {
        $site = new Site();
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'site'  => $site
        ]);

        $site->setErrorPage($page);
        PageRules::changeSlug($page, 'test-a');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The URL appendix "project-b" exists
     */
    public function testChangeSlugWithDuplicate()
    {
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
        ]);

        $pages = new Pages([
            $page->clone(['slug' => 'project-a']),
            $page->clone(['slug' => 'project-b']),
            $page->clone(['slug' => 'project-c'])
        ]);

        PageRules::changeSlug($pages->first(), 'project-b');
    }

    public function testChangeTemplate()
    {
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
        ]);

        $this->assertTrue(PageRules::changeTemplate($page, 'project'));
    }

    public function testUpdate()
    {
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
        ]);

        $this->assertTrue(PageRules::update($page, [
            'color' => 'red'
        ]));
    }

    public function testDelete()
    {
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'store' => FakePageStore::class
        ]);

        $this->assertTrue(PageRules::delete($page));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The page does not exist
     */
    public function testDeleteNotExists()
    {
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
        ]);

        PageRules::delete($page);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage This page cannot be deleted
     */
    public function testDeleteHomepage()
    {
        $site = new Site();
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'store' => FakePageStore::class,
            'site'  => $site
        ]);
        $site->setHomepage($page);
        PageRules::delete($page);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage This page cannot be deleted
     */
    public function testDeleteErrorPage()
    {
        $site = new Site();
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'store' => FakePageStore::class,
            'site'  => $site
        ]);
        $site->setErrorPage($page);
        PageRules::delete($page);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The page has children
     */
    public function testDeleteWithChildren()
    {
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'children' => new Children([
                new Page(['slug' => 'a']),
                new Page(['slug' => 'b'])
            ]),
            'store' => FakePageStore::class,
        ]);

        PageRules::delete($page);
    }

    public function testDeleteWithChildrenForce()
    {
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'children' => new Children([
                new Page(['slug' => 'a']),
                new Page(['slug' => 'b'])
            ]),
            'store' => FakePageStore::class,
        ]);


        $this->assertTrue(PageRules::delete($page, true));
    }

}
