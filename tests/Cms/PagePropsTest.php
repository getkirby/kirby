<?php

namespace Kirby\Cms;

class PagePropsTest extends TestCase
{

    /**
     * Deregister any plugins for the page
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        App::destroy();
        new App();
    }

    public function testDragText()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $this->assertEquals('(link: test text: test)', $page->dragText());
        $this->assertEquals('[test](/test)', $page->dragText('markdown'));
    }

    public function testDragTextWithTitle()
    {
        $page = new Page([
            'slug' => 'test',
            'content' => [
                'title' => 'Test Title'
            ]
        ]);

        $this->assertEquals('(link: test text: Test Title)', $page->dragText());
        $this->assertEquals('[Test Title](/test)', $page->dragText('markdown'));
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

    /**
     * @expectedException TypeError
     */
    public function testInvalidUrl()
    {
        $page = new Page([
            'slug'  => 'test',
            'url' => []
        ]);
    }

    public function testSlug()
    {
        $page = new Page(['slug' => 'test']);
        $this->assertEquals('test', $page->slug());
    }

    public function testUid()
    {
        $page = new Page(['slug' => 'test']);
        $this->assertEquals('test', $page->uid());
    }

}
