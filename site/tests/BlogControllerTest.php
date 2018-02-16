<?php

namespace Kirby\Cms;

class BlogControllerTest extends ControllerTestCase
{

    public $controller = 'blog';
    public $page       = 'blog';

    public function testArticles()
    {
        $result = $this->controller();

        $this->assertArrayHasKey('articles', $result);
        $this->assertInstanceOf(Pages::class, $result['articles']);
        $this->assertCount(2, $result['articles']);
    }

    public function testPagination()
    {
        $result = $this->controller();

        $this->assertArrayHasKey('pagination', $result);
        $this->assertInstanceOf(Pagination::class, $result['pagination']);
    }

}
