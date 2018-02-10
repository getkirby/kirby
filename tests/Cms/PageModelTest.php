<?php

namespace Kirby\Cms;

class ArticlePage extends Page
{
    public function test()
    {
        return $this->id();
    }
}

class PageModelTest extends TestCase
{

    protected function setUp()
    {
        Page::models([
            'article' => ArticlePage::class
        ]);
    }

    public function testPageModelWithTemplate()
    {
        $page = Page::factory([
            'slug'     => 'test',
            'template' => 'article',
        ]);

        $this->assertInstanceOf(ArticlePage::class, $page);
        $this->assertEquals('test', $page->test());
    }

    public function testMissingPageModel()
    {
        $page = Page::factory([
            'slug'     => 'test',
            'template' => 'project',
        ]);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertFalse(method_exists($page, 'test'));
    }

}
