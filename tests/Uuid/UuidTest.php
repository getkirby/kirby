<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\Page;
use Kirby\Toolkit\Str;

/**
 * @coversDefaultClass \Kirby\Uuid\Uuid
 */
class UuidTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstructUuidString()
	{
		$uuid = new Uuid($uri = 'page://my-page-uuid');
		$this->assertInstanceOf(Uri::class, $uuid->uri);
		$this->assertSame($uri, $uuid->uri->toString());
		$this->assertNull($uuid->model);
		$this->assertNull($uuid->context);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructModel()
	{
		$app      = $this->app;
		$page     = $app->page('page-a');
		$siblings = $app->site()->children();
		$uuid = new PageUuid(
			model: $page,
			context: $siblings
		);
		$this->assertInstanceOf(Uri::class, $uuid->uri);
		$this->assertSame('page://my-page', $uuid->uri->toString());
		$this->assertSame($page, $uuid->model);
		$this->assertSame($siblings, $uuid->context);
	}

	/**
	 * @covers ::clear
	 * @covers ::isCached
	 * @covers ::populate
	 */
	public function testCache()
	{
		$page    = $this->app->page('page-a');
		$subpage = $page->children()->find('subpage-a');

		$page    = $page->uuid();
		$subpage = $subpage->uuid();

		// not cached so far
		$this->assertFalse($page->isCached());
		$this->assertFalse($subpage->isCached());

		// cache them
		$page->populate();
		$subpage->populate();
		$this->assertTrue($page->isCached());
		$this->assertTrue($subpage->isCached());

		// clear only page
		$page->clear();
		$this->assertFalse($page->isCached());
		$this->assertTrue($subpage->isCached());

		// clear recursively
		$page->clear(true);
		$this->assertFalse($page->isCached());
		$this->assertFalse($subpage->isCached());
	}

	/**
	 * @covers ::context
	 */
	public function testContext()
	{
		$uuid = $this->app->page('page-a')->uuid();
		$this->assertInstanceOf(Generator::class, $uuid->context());
		$this->assertSame(0, iterator_count($uuid->context()));

		$uuid = new Uuid(
			uuid: 'page://my-app',
			context: $this->app->site()->children()
		);
		$this->assertInstanceOf(Generator::class, $uuid->context());
		$this->assertSame(2, iterator_count($uuid->context()));
	}

	/**
	 * @covers ::for
	 */
	public function testForUuidString()
	{
		$this->assertInstanceOf(PageUuid::class, Uuid::for('page://my-id'));
		$this->assertInstanceOf(FileUuid::class, Uuid::for('file://my-id'));
		$this->assertInstanceOf(SiteUuid::class, Uuid::for('site://'));
		$this->assertInstanceOf(UserUuid::class, Uuid::for('user://my-id'));
		// TODO: activate for  uuid-block-structure-support
		// $this->assertInstanceOf(BlockUuid::class, Uuid::for('block://my-id'));
		// $this->assertInstanceOf(StructureUuid::class, Uuid::for('struct://my-id'));
	}

	/**
	 * @covers ::for
	 */
	public function testForObject()
	{
		$site   = $this->app->site();
		$page   = $site->find('page-a');
		$file   = $page->file('test.pdf');
		$user   = $this->app->user('my-user');
		$block  = $page->notes()->toBlocks()->first();
		$struct = $page->authors()->toStructure()->first();

		$this->assertInstanceOf(PageUuid::class, Uuid::for($page));
		$this->assertInstanceOf(FileUuid::class, Uuid::for($file));
		$this->assertInstanceOf(SiteUuid::class, Uuid::for($site));
		$this->assertInstanceOf(UserUuid::class, Uuid::for($user));
		// TODO: activate for  uuid-block-structure-support
		// $this->assertInstanceOf(BlockUuid::class, Uuid::for($block));
		// $this->assertInstanceOf(StructureUuid::class, Uuid::for($struct));
	}

	/**
	 * @covers ::generate
	 */
	public function testGenerate()
	{
		// default length
		$id = Uuid::generate();
		$this->assertSame(15, strlen($id));

		// custom length
		$id = Uuid::generate(5);
		$this->assertSame(5, strlen($id));

		// custom generator callback
		Uuid::$generator = fn ($length) => 'veryunique' . $length;
		$this->assertSame('veryunique13', Uuid::generate(13));
		Uuid::$generator = null;
	}

	/**
	 * @covers ::id
	 */
	public function testId()
	{
		$uuid = new Uuid('page://my-uuid-id');
		$this->assertSame('my-uuid-id', $uuid->id());

		$uuid = $this->app->page('page-a')->uuid();
		$this->assertSame('my-page', $uuid->id());
	}


	/**
	 * @covers ::index
	 */
	public function testIndex()
	{
		$this->assertInstanceOf(Generator::class, Uuid::index());
		$this->assertSame(0, iterator_count(Uuid::index()));
	}

	/**
	 * @covers ::indexes
	 */
	public function testIndexes()
	{
		$uuid = new Uuid('page://my-uuid');
		$this->assertInstanceOf(Generator::class, $uuid->indexes());
		$this->assertSame(0, iterator_count($uuid->indexes()));

		$uuid = new Uuid(
			uuid: 'page://my-app',
			context: $this->app->site()->children()
		);
		$this->assertInstanceOf(Generator::class, $uuid->indexes());
		$this->assertSame(2, iterator_count($uuid->indexes()));
	}

	/**
	 * @covers ::is
	 */
	public function testIs()
	{
		$this->assertTrue(Uuid::is('site://'));
		$this->assertTrue(Uuid::is('page://something'));
		$this->assertTrue(Uuid::is('user://something'));
		$this->assertTrue(Uuid::is('file://something'));
		$this->assertTrue(Uuid::is('file://something/else'));
		// TODO: activate for  uuid-block-structure-support
		// $this->assertTrue(Uuid::is('struct://something'));
		// $this->assertTrue(Uuid::is('block://something'));
		// $this->assertTrue(Uuid::is('block://something/else'));

		$this->assertTrue(Uuid::is('site://', 'site'));
		$this->assertTrue(Uuid::is('page://something', 'page'));
		$this->assertTrue(Uuid::is('user://something', 'user'));
		$this->assertTrue(Uuid::is('file://something', 'file'));

		$this->assertFalse(Uuid::is('site://', 'block'));
		$this->assertFalse(Uuid::is('page://something', 'block'));
		$this->assertFalse(Uuid::is('user://something', 'block'));
		$this->assertFalse(Uuid::is('file://something', 'block'));

		$this->assertFalse(Uuid::is('file:/something'));
		$this->assertFalse(Uuid::is('foo://something'));
		$this->assertFalse(Uuid::is('page//something'));
		$this->assertFalse(Uuid::is('page//something', 'page'));
		$this->assertFalse(Uuid::is('not a page://something'));
	}

	/**
	 * @covers ::key
	 */
	public function testKey()
	{
		$uuid = $this->app->page('page-a')->uuid();
		$this->assertSame('page/my/-page', $uuid->key());
	}

	/**
	 * @covers ::id
	 * @covers ::render
	 * @covers ::__toString
	 */
	public function testRender()
	{
		// with ID already stored in content
		$uuid = $this->app->page('page-a')->uuid();
		$this->assertSame('page://my-page', $uuid->render());
		$this->assertSame('page://my-page', (string)$uuid);

		// with creating new ID
		$uuid = $this->app->page('page-b')->uuid();
		$this->assertIsString($id = $uuid->render());
		$this->assertSame($id, (string)$uuid);
		$this->assertSame(Str::after($id, '://'), $this->app->page('page-b')->content()->get('uuid')->value());
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve()
	{
		// for Uuid that was constructed from model
		$page = $this->app->page('page-a');
		$uuid = $page->uuid();
		$this->assertSame($page, $uuid->resolve());

		// from cache (enforce via $lazy)
		$uuid = new PageUuid('page://my-page');
		$this->assertFalse($uuid->isCached());
		$this->assertNull($uuid->resolve(true));
		$uuid->populate();
		$this->assertTrue($uuid->isCached());
		$this->assertSame($page, $uuid->resolve(true));
		$uuid->clear(true);

		// from index
		$uuid = new PageUuid('page://my-page');
		$this->assertFalse($uuid->isCached());
		$this->assertNull($uuid->resolve(true));
		$this->assertSame($page, $uuid->resolve());
		$this->assertTrue($uuid->isCached());
	}

	/**
	 * @covers ::retrieveId
	 */
	public function testRetrieveId()
	{
		$page = $this->app->page('page-a');
		$this->assertSame('page-a', Uuid::retrieveId($page));
	}

	/**
	 * @covers ::value
	 */
	public function testValue()
	{
		$page = $this->app->page($dir = 'page-a/subpage-a');
		$this->assertSame($dir, $page->uuid()->value());
	}
}
