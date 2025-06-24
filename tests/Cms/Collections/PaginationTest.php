<?php

namespace Kirby\Cms;

use Kirby\Http\Uri;

class PaginationTest extends TestCase
{
	protected function pagination(array $options = [])
	{
		return new Pagination([
			'page'  => 1,
			'limit' => 10,
			'total' => 120,
			'url'   => new Uri('https://getkirby.com'),
			...$options
		]);
	}

	public function testCustomAppUrl()
	{
		$this->app->clone([
			'options' => [
				'url' => 'https://getkirby.com'
			]
		]);

		$pagination = new Pagination([
			'page'  => 1,
			'limit' => 10,
			'total' => 120,
		]);

		$this->assertSame('https://getkirby.com/page:2', $pagination->nextPageUrl());
	}

	public function testSubfolderUrl()
	{
		$this->app->clone([
			'options' => [
				'url' => 'http://localhost/starterkit'
			]
		]);

		$pagination = new Pagination([
			'page'  => 1,
			'limit' => 10,
			'total' => 120,
		]);

		$this->assertSame('http://localhost/starterkit/page:2', $pagination->nextPageUrl());
	}

	public function testCurrentPageUrl()
	{
		$pagination = $this->pagination([
			'page' => 2
		]);

		$this->assertSame('https://getkirby.com/page:2', $pagination->pageUrl());
	}

	public function testCurrentPageUrlWithFirstPage()
	{
		$pagination = $this->pagination([
			'page' => 1
		]);

		$this->assertSame('https://getkirby.com', $pagination->pageUrl());
	}

	public function testPageUrl()
	{
		$pagination = $this->pagination();

		$this->assertSame('https://getkirby.com', $pagination->pageUrl(1));
		$this->assertSame('https://getkirby.com/page:12', $pagination->pageUrl(12));

		$this->assertNull($pagination->pageUrl(0));
		$this->assertNull($pagination->pageUrl(13));
	}

	public function testFirstPageUrl()
	{
		$pagination = $this->pagination();
		$this->assertSame('https://getkirby.com', $pagination->firstPageUrl());
	}

	public function testLastPageUrl()
	{
		$pagination = $this->pagination();
		$this->assertSame('https://getkirby.com/page:12', $pagination->lastPageUrl());
	}

	public function testFirstLastPageUrlNull()
	{
		$pagination = new Pagination([
			'page'  => 1,
			'limit' => 10,
			'total' => 0,
			'url'   => new Uri('https://getkirby.com')
		]);

		$this->assertNull($pagination->firstPageUrl());
		$this->assertNull($pagination->lastPageUrl());
	}

	public function testNextPageUrl()
	{
		$pagination = $this->pagination([
			'page' => 2
		]);

		$this->assertSame('https://getkirby.com/page:3', $pagination->nextPageUrl());
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

		$this->assertSame('https://getkirby.com/page:2', $pagination->prevPageUrl());
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

		$this->assertSame('https://getkirby.com', $pagination->prevPageUrl());
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
