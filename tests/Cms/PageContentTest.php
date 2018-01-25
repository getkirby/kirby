<?php

namespace Kirby\Cms;

class PageContentTest extends TestCase
{

    /**
     * Freshly register all field methods
     */
    protected function setUp()
    {
        ContentField::methods(require __DIR__ . '/../../extensions/methods.php');
    }

    public function testDefaultContent()
    {
        $page = new Page(['id' =>  'test']);
        $this->assertInstanceOf(Content::class, $page->content());
    }

    public function testContent()
    {
        $content = new Content([
            'text' => 'lorem ipsum'
        ]);

        $page = new Page([
            'id'      => 'test',
            'content' => $content
        ]);

        $this->assertEquals($content, $page->content());
        $this->assertEquals('lorem ipsum', $page->text()->value());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setContent() must be an instance of Kirby\Cms\Content or null
     */
    public function testInvalidContent()
    {
        $page = new Page([
            'id'      => 'test',
            'content' => 'content'
        ]);
    }

    public function testEmptyTitle()
    {
        $page = new Page([
            'id'      => 'test',
            'content' => new Content()
        ]);

        $this->assertEquals($page->slug(), $page->title()->value());
    }

    public function testTitle()
    {
        $page = new Page([
            'id'      => 'test',
            'content' => new Content([
                'title' => 'Custom Title'
            ])
        ]);

        $this->assertEquals('Custom Title', $page->title()->value());
    }

    public function testDateWithoutFormat()
    {
        $page = new Page([
            'id'      => 'test',
            'content' => new Content([
                'date' => '2012-12-12'
            ])
        ]);

        $this->assertEquals(strtotime('2012-12-12'), $page->date());
    }

    public function testDateWithFormat()
    {
        $page = new Page([
            'id'      => 'test',
            'content' => new Content([
                'date' => '2012-12-12'
            ])
        ]);

        $this->assertEquals('12.12.2012', $page->date('d.m.Y'));
    }

}
