<?php

namespace Kirby\Cms;

class PageContentTest extends TestCase
{

    /**
     * Freshly register all field methods
     */
    protected function setUp()
    {
        Field::methods(require __DIR__ . '/../../extensions/methods.php');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultContentWithoutStore()
    {
        $page = new Page(['id' =>  'test']);
        $this->assertInstanceOf(Content::class, $page->content());
    }

    public function testDefaultContentWithStore()
    {
        $store = new Store([
            'page.content' => function ($page) {
                return new Content([
                    'text' => 'lorem ipsum'
                ], $page);
            }
        ]);

        $page = new Page([
            'id'    => 'test',
            'store' => $store
        ]);

        $this->assertEquals('lorem ipsum', $page->text()->value());
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
     * @expectedException Exception
     * @expectedExceptionMessage The "content" attribute must be of type "Kirby\Cms\Content"
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
