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
            'id' => 'test'
        ]);

        $this->assertEquals('test', $page->id());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Missing "id" property
     */
    public function testEmptyId()
    {
        $page = new Page(['id' => null]);
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setId() must be of the type string, array given
     */
    public function testInvalidId()
    {
        $page = new Page([
            'id' => []
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
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setNum() must be of the type integer or null, array given
     */
    public function testInvalidNum()
    {
        $page = new Page([
            'id'  => 'test',
            'num' => []
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
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setParent() must be an instance of Kirby\Cms\Page or null, string given
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
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setRoot() must be of the type string or null, array given
     */
    public function testInvalidRoot()
    {
        $page = new Page([
            'id'   => 'test',
            'root' => []
        ]);
    }

    public function testSite()
    {
        $site = new Site();
        $page = new Page([
            'id'   => 'test',
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
            'id'   => 'test',
            'site' => 'mysite'
        ]);
    }

    public function testDefaultTemplate()
    {
        $page = new Page([
            'id' => 'test',
        ]);

        $this->assertEquals('default', $page->template());
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
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setTemplate() must be of the type string or null, array given
     */
    public function testInvalidTemplate()
    {
        $page = new Page([
            'id'       => 'test',
            'template' => []
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
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setUrl() must be of the type string or null, array given
     */
    public function testInvalidUrl()
    {
        $page = new Page([
            'id'  => 'test',
            'url' => []
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
