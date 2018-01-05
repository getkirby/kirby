<?php

namespace Kirby\Cms;

class PagePropsTest extends TestCase
{

    /**
     * Deregister any plugins for the page
     *
     * @return void
     */
    protected function setUp()
    {
        Page::use('kirby', null);
        Page::use('site', null);
        Page::use('store', null);
    }

    public function testId()
    {
        $page = new Page([
            'id' => 'test'
        ]);

        $this->assertEquals('test', $page->id());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "id" property is required and must not be null
     */
    public function testEmptyId()
    {
        $page = new Page();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "id" property must be of type "string" not "boolean"
     */
    public function testInvalidId()
    {
        $page = new Page([
            'id' => false
        ]);
    }

    public function testNum()
    {
        $page = new Page([
            'id'  => 'test',
            'num' => 1
        ]);

        $this->assertEquals(1, $page->num());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "num" property must be of type "integer"
     */
    public function testInvalidNum()
    {
        $page = new Page([
            'id'  => 'test',
            'num' => false
        ]);
    }

    public function testEmptyNum()
    {
        $page = new Page([
            'id'  => 'test',
            'num' => null
        ]);

        $this->assertNull($page->num());
    }

    public function testParent()
    {
        $parent = new Page([
            'id' => 'test'
        ]);

        $page = new Page([
            'id'     => 'test/child',
            'parent' => $parent
        ]);

        $this->assertEquals($parent, $page->parent());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "parent" property must be of type "Kirby\Cms\Page"
     */
    public function testInvalidParent()
    {
        $page = new Page([
            'id'     => 'test/child',
            'parent' => 'some parent'
        ]);
    }

    public function testRoot()
    {
        $page = new Page([
            'id'   => 'test',
            'root' => '/test'
        ]);

        $this->assertEquals('/test', $page->root());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "root" property must be of type "string"
     */
    public function testInvalidRoot()
    {
        $page = new Page([
            'id'   => 'test',
            'root' => false
        ]);
    }

    public function testSiteProp()
    {
        $site = new Site();
        $page = new Page([
            'id'   => 'test',
            'site' => $site
        ]);

        $this->assertEquals($site, $page->site());
    }

    public function testSitePlugin()
    {
        $site = new Site();
        $page = new Page(['id'   => 'test']);

        Page::use('site', $site);

        $this->assertEquals($site, $page->site());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "site" property must be of type "Kirby\Cms\Site"
     */
    public function testInvalidSite()
    {
        $page = new Page([
            'id'   => 'test',
            'site' => 'mysite'
        ]);
    }

    public function testStoreProp()
    {
        $store = new Store();
        $page  = new Page([
            'id'    => 'test',
            'store' => $store
        ]);

        $this->assertEquals($store, $page->store());
    }

    public function testStorePlugin()
    {
        $store = new Store();
        $page  = new Page(['id'   => 'test']);

        Page::use('store', $store);

        $this->assertEquals($store, $page->store());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "store" property must be of type "Kirby\Cms\Store"
     */
    public function testInvalidStore()
    {
        $page = new Page([
            'id'    => 'test',
            'store' => 'mystore'
        ]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testTemplateWithoutStore()
    {
        $page = new Page([
            'id' => 'test',
        ]);

        $this->assertEquals('template', $page->template());
    }

    public function testTemplateWithStore()
    {
        $store = new Store([
            'page.template' => function ($page) {
                return 'my-template';
            }
        ]);

        $page = new Page([
            'id'    => 'test',
            'store' => $store
        ]);

        $this->assertEquals('my-template', $page->template());
    }

    public function testTemplate()
    {
        $page = new Page([
            'id'       => 'test',
            'template' => 'testTemplate'
        ]);

        $this->assertEquals('testTemplate', $page->template());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "template" property must be of type "string"
     */
    public function testInvalidTemplate()
    {
        $page = new Page([
            'id'       => 'test',
            'template' => false
        ]);
    }

    public function testUrl()
    {
        $page = new Page([
            'id'  => 'test',
            'url' => 'https://getkirby.com/test'
        ]);

        $this->assertEquals('https://getkirby.com/test', $page->url());
    }

    public function testDefaultUrl()
    {
        $page = new Page([
            'id' => 'test'
        ]);

        $this->assertEquals('/test', $page->url());
    }

    public function testDefaultUrlWithDuplicateLeadingSlash()
    {
        $page = new Page([
            'id' => '/test'
        ]);

        $this->assertEquals('/test', $page->url());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "url" property must be of type "string"
     */
    public function testInvalidUrl()
    {
        $page = new Page([
            'id'  => 'test',
            'url' => false
        ]);
    }

    public function slugProvider()
    {
        return [
            ['test', 'test'],
            ['test/child', 'child'],
            ['test/child/grand-child', 'grand-child'],
        ];
    }

    /**
     * @dataProvider slugProvider
     */
    public function testSlug($id, $slug)
    {
        $page = new Page(['id' => $id]);
        $this->assertEquals($slug, $page->slug());
    }

    /**
     * @dataProvider slugProvider
     */
    public function testUid($id, $slug)
    {
        $page = new Page(['id' => $id]);
        $this->assertEquals($slug, $page->uid());
    }

}
