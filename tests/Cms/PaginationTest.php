<?php

namespace Kirby\Cms;

class PaginationTest extends TestCase
{

    protected function pagination(array $options = []): Pagination
    {
        return new Pagination(array_merge([
            'page'  => 1,
            'limit' => 10,
            'total' => 120,
            'url'   => 'https://getkirby.com'
        ], $options));
    }

    public function testCurrentPageUrl()
    {
        $pagination = $this->pagination([
            'page' => 2
        ]);

        $this->assertEquals('https://getkirby.com?page=2', $pagination->pageUrl());
    }

    public function testCurrentPageUrlWithFirstPage()
    {
        $pagination = $this->pagination([
            'page' => 1
        ]);

        $this->assertEquals('https://getkirby.com', $pagination->pageUrl());
    }

    public function testPageUrl()
    {
        $pagination = $this->pagination();

        $this->assertEquals('https://getkirby.com', $pagination->pageUrl(1));
        $this->assertEquals('https://getkirby.com?page=12', $pagination->pageUrl(12));

        $this->assertNull($pagination->pageUrl(0));
        $this->assertNull($pagination->pageUrl(13));
    }

    public function testFirstPageUrl()
    {
        $pagination = $this->pagination();
        $this->assertEquals('https://getkirby.com', $pagination->firstPageUrl());
    }

    public function testLastPageUrl()
    {
        $pagination = $this->pagination();
        $this->assertEquals('https://getkirby.com?page=12', $pagination->lastPageUrl());
    }

    public function testNextPageUrl()
    {
        $pagination = $this->pagination([
            'page' => 2
        ]);

        $this->assertEquals('https://getkirby.com?page=3', $pagination->nextPageUrl());
    }

    public function testNonExistingNextPage()
    {
        $pagination = $this->pagination(['page' => 12]);
        $this->assertNull($pagination->nextPageUrl());
    }

    public function testPrevPageUrl()
    {
        $pagination = $this->pagination([
            'page' => 3
        ]);

        $this->assertEquals('https://getkirby.com?page=2', $pagination->prevPageUrl());
    }

    public function testNonExistingPrevPage()
    {
        $pagination = $this->pagination(['page' => 1]);
        $this->assertNull($pagination->prevPageUrl());
    }

    public function testPrevPageUrlWithFirstPage()
    {
        $pagination = $this->pagination([
            'page' => 2
        ]);

        $this->assertEquals('https://getkirby.com', $pagination->prevPageUrl());
    }

}
