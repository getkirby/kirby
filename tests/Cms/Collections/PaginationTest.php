<?php

namespace Kirby\Cms;

use Kirby\Http\Server;
use Kirby\Http\Uri;

class PaginationTest extends TestCase
{
    protected function pagination(array $options = [])
    {
        return new Pagination(array_merge([
            'page'  => 1,
            'limit' => 10,
            'total' => 120,
            'url'   => new Uri('https://getkirby.com')
        ], $options));
    }

    public function testCustomAppUrl()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'https://getkirby.com'
            ]
        ]);

        $pagination = new Pagination([
            'page'  => 1,
            'limit' => 10,
            'total' => 120,
        ]);

        $this->assertEquals('https://getkirby.com/page:2', $pagination->nextPageUrl());
    }

    public function testSubfolderUrl()
    {
        $server = $_SERVER;

        // remove any cached uri object
        Uri::$current = null;

        // if cli detection is activated the index url detection
        // will fail and fall back to /
        Server::$cli = false;

        // no additional path
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SCRIPT_NAME'] = '/starterkit/index.php';

        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $pagination = new Pagination([
            'page'  => 1,
            'limit' => 10,
            'total' => 120,
        ]);

        $this->assertEquals('http://localhost/starterkit/page:2', $pagination->nextPageUrl());

        $_SERVER = $server;
        Server::$cli = true;
        Uri::$current = null;
    }

    public function testCurrentPageUrl()
    {
        $pagination = $this->pagination([
            'page' => 2
        ]);

        $this->assertEquals('https://getkirby.com/page:2', $pagination->pageUrl());
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
        $this->assertEquals('https://getkirby.com/page:12', $pagination->pageUrl(12));

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
        $this->assertEquals('https://getkirby.com/page:12', $pagination->lastPageUrl());
    }

    public function testNextPageUrl()
    {
        $pagination = $this->pagination([
            'page' => 2
        ]);

        $this->assertEquals('https://getkirby.com/page:3', $pagination->nextPageUrl());
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

        $this->assertEquals('https://getkirby.com/page:2', $pagination->prevPageUrl());
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

    public function testMethod()
    {
        $pagination = $this->pagination([
            'page' => 2
        ]);
        $this->assertSame('https://getkirby.com/page:2', $pagination->pageUrl());

        $pagination = $this->pagination([
            'page'   => 2,
            'method' => 'query'
        ]);
        $this->assertSame('https://getkirby.com?page=2', $pagination->pageUrl());

        $pagination = $this->pagination([
            'page'   => 2,
            'method' => 'param'
        ]);
        $this->assertSame('https://getkirby.com/page:2', $pagination->pageUrl());

        $pagination = $this->pagination([
            'page'   => 2,
            'method' => 'none'
        ]);
        $this->assertNull($pagination->pageUrl());
    }
}
