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
        App::destroy();
    }

    public function testId()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $this->assertEquals('test', $page->id());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The property "slug" is required
     */
    public function testEmptyId()
    {
        $page = new Page(['slug' => null]);
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setSlug() must be of the type string, array given
     */
    public function testInvalidId()
    {
        $page = new Page([
            'slug' => []
        ]);
    }

    public function testNum()
    {
        $page = new Page([
            'slug'  => 'test',
            'num' => 1
        ]);

        $this->assertEquals(1, $page->num());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setNum() must be of the type integer or null, array given
     */
    public function testInvalidNum()
    {
        $page = new Page([
            'slug'  => 'test',
            'num' => []
        ]);
    }

    public function testEmptyNum()
    {
        $page = new Page([
            'slug'  => 'test',
            'num' => null
        ]);

        $this->assertNull($page->num());
    }

    public function testParent()
    {
        $parent = new Page([
            'slug' => 'test'
        ]);

        $page = new Page([
            'slug'     => 'test/child',
            'parent' => $parent
        ]);

        $this->assertEquals($parent, $page->parent());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setParent() must be an instance of Kirby\Cms\Page or null, string given
     */
    public function testInvalidParent()
    {
        $page = new Page([
            'slug'     => 'test/child',
            'parent' => 'some parent'
        ]);
    }

    public function testSite()
    {
        $site = new Site();
        $page = new Page([
            'slug'   => 'test',
            'site' => $site
        ]);

        $this->assertEquals($site, $page->site());
    }

    /**
     * @expectedException TypeError
     */
    public function testInvalidSite()
    {
        $page = new Page([
            'slug'   => 'test',
            'site' => 'mysite'
        ]);
    }

    public function testDefaultTemplate()
    {
        $page = new Page([
            'slug' => 'test',
        ]);

        $this->assertEquals('default', $page->template());
    }

    public function testTemplate()
    {
        $page = new Page([
            'slug'     => 'test',
            'template' => 'testTemplate'
        ]);

        $this->assertEquals('testtemplate', $page->template());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setTemplate() must be of the type string or null, array given
     */
    public function testInvalidTemplate()
    {
        $page = new Page([
            'slug'       => 'test',
            'template' => []
        ]);
    }

    public function testUrl()
    {
        $page = new Page([
            'slug'  => 'test',
            'url' => 'https://getkirby.com/test'
        ]);

        $this->assertEquals('https://getkirby.com/test', $page->url());
    }

    public function testDefaultUrl()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $this->assertEquals('/test', $page->url());
    }

    public function testDefaultUrlWithDuplicateLeadingSlash()
    {
        $page = new Page([
            'slug' => '/test'
        ]);

        $this->assertEquals('/test', $page->url());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setUrl() must be of the type string or null, array given
     */
    public function testInvalidUrl()
    {
        $page = new Page([
            'slug'  => 'test',
            'url' => []
        ]);
    }

    public function slugProvider()
    {
        return [
            ['test', 'test'],
            ['test/child', 'test-child'],
            ['test/child/grand-child', 'test-child-grand-child'],
        ];
    }

    /**
     * @dataProvider slugProvider
     */
    public function testSlug($id, $slug)
    {
        $page = new Page(['slug' => $id]);
        $this->assertEquals($slug, $page->slug());
    }

    /**
     * @dataProvider slugProvider
     */
    public function testUid($id, $slug)
    {
        $page = new Page(['slug' => $id]);
        $this->assertEquals($slug, $page->uid());
    }

}
