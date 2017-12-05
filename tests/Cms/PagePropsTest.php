<?php

namespace Kirby\Cms;

class PagePropsTest extends TestCase
{

    public function testChildren()
    {
        $this->markTestIncomplete();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "children" attribute must be of type "Kirby\Cms\Children"
     */
    public function testInvalidChildren()
    {
        $page = new Page([
            'id'       => 'test',
            'children' => 'children'
        ]);
    }

    public function testCollection()
    {
        $this->markTestIncomplete();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "collection" attribute must be of type "Kirby\Cms\Pages"
     */
    public function testInvalidCollection()
    {
        $page = new Page([
            'id'         => 'test',
            'collection' => 'collection'
        ]);
    }

    public function testFiles()
    {
        $this->markTestIncomplete();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "files" attribute must be of type "Kirby\Cms\Files"
     */
    public function testInvalidFiles()
    {
        $page = new Page([
            'id'    => 'test',
            'files' => 'files'
        ]);
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
     * @expectedExceptionMessage The "id" prop is missing
     */
    public function testEmptyId()
    {
        $page = new Page();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "id" attribute must be of type "string"
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
     * @expectedExceptionMessage The "num" attribute must be of type "integer"
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
     * @expectedExceptionMessage The "parent" attribute must be of type "Kirby\Cms\Page"
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
     * @expectedExceptionMessage The "root" attribute must be of type "string"
     */
    public function testInvalidRoot()
    {
        $page = new Page([
            'id'   => 'test',
            'root' => false
        ]);
    }

    public function testTemplate()
    {
        $page = new Page([
            'id'       => 'test',
            'template' => 'testTemplate'
        ]);

        $this->assertEquals('testTemplate', $page->template());
    }

    public function testDefaultTemplate()
    {
        $this->markTestIncomplete();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "template" attribute must be of type "string"
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
     * @expectedExceptionMessage The "url" attribute must be of type "string"
     */
    public function testInvalidUrl()
    {
        $page = new Page([
            'id'  => 'test',
            'url' => false
        ]);
    }

}
