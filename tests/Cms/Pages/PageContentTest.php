<?php

namespace Kirby\Cms;

class PageContentTest extends TestCase
{
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

    public function testInvalidContent()
    {
        $this->expectException('TypeError');

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
}
