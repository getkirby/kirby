<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\Page;

/**
 * @coversDefaultClass \Kirby\Uuid\PageUuid
 */
class PageUuidTest extends TestCase
{
	/**
	 * @covers ::findByCache
	 */
	public function testFindByCache()
	{
		$page = $this->app->page('page-a');

		// not yet in cache
		$uuid  = new PageUuid('page://my-page');
		$this->assertFalse($uuid->isCached());
		$this->assertNull($uuid->model(true));

		// fill cache
		$page->uuid()->populate();

		// retrieve from cache
		$this->assertTrue($uuid->isCached());
		$this->assertTrue($page->is($uuid->model(true)));
	}

	/**
	 * @covers ::findByIndex
	 */
	public function testFindByIndex()
	{
		$page = $this->app->page('page-a');
		$uuid  = new PageUuid('page://my-page');
		$this->assertFalse($uuid->isCached());
		$this->assertNull($uuid->model(true));
		$this->assertTrue($page->is($uuid->model()));
		$this->assertTrue($uuid->isCached());

		// not found
		$uuid = new PageUuid('page://does-not-exist');
		$this->assertNull($uuid->model());
	}

	/**
	 * @covers ::id
	 */
	public function testId()
	{
		// with UUID string
		$uuid = new PageUuid('page://just-a-file');
		$this->assertSame('just-a-file', $uuid->id());

		// with model with nothing in its content file yet
		$page = $this->app->page('page-b');
		$uuid = $page->uuid();
		$this->assertNull($page->content()->get('uuid')->value());

		$id   = $uuid->id();
		$page = $this->app->page('page-b'); // since $page is immutable
		$this->assertSame(16, strlen($id));
		$this->assertSame($page->content()->get('uuid')->value(), $id);
	}

	/**
	 * @covers ::index
	 */
	public function testIndex()
	{
		$index = PageUuid::index();
		$this->assertInstanceOf(Generator::class, $index);
		$this->assertInstanceOf(Page::class, $index->current());
		$this->assertSame(3, iterator_count($index));
	}

	/**
	 * @covers ::retrieveId
	 */
	public function testRetrieveId()
	{
		$page = $this->app->page('page-a');
		$this->assertSame('my-page', ModelUuid::retrieveId($page));
	}

	/**
	 * @covers ::url
	 */
	public function testUrl()
	{
		$page = $this->app->page('page-a');
		$url  = 'https://getkirby.com/@/page/my-page';
		$this->assertSame($url, $page->uuid()->url());
	}
}
