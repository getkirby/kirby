<?php

namespace Kirby\Cms;

class PageContentTest extends TestCase
{

    /**
     * Freshly register all field methods
     */
    protected function setUp()
    {
        ContentField::$methods = require __DIR__ . '/../../extensions/methods.php';
    }

    public function testDefaultContent()
    {
        $page = new Page(['slug' =>  'test']);
        $this->assertInstanceOf(Content::class, $page->content());
    }

    public function testContent()
    {

        $page = new Page([
            'slug'    => 'test',
            'content' => $content = ['text' => 'lorem ipsum']
        ]);

        $this->assertEquals($content, $page->content()->toArray());
        $this->assertEquals('lorem ipsum', $page->text()->value());
    }

    /**
     * @expectedException TypeError
     */
    public function testInvalidContent()
    {
        $page = new Page([
            'slug'    => 'test',
            'content' => 'content'
        ]);
    }

    public function testEmptyTitle()
    {
        $page = new Page([
            'slug'    => 'test',
            'content' => []
        ]);

        $this->assertEquals($page->slug(), $page->title()->value());
    }

    public function testTitle()
    {
        $page = new Page([
            'slug'    => 'test',
            'content' => [
                'title' => 'Custom Title'
            ]
        ]);

        $this->assertEquals('Custom Title', $page->title()->value());
    }

    public function testDateWithoutFormat()
    {
        $page = new Page([
            'slug'    => 'test',
            'content' => [
                'date' => '2012-12-12'
            ]
        ]);

        $this->assertEquals(strtotime('2012-12-12'), $page->date());
    }

    public function testDateWithFormat()
    {
        $page = new Page([
            'slug'    => 'test',
            'content' => [
                'date' => '2012-12-12'
            ]
        ]);

        $this->assertEquals('12.12.2012', $page->date('d.m.Y'));
    }

}
