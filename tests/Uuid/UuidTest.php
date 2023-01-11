<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\Page;
use Kirby\Exception\LogicException;
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
	 * @covers ::__construct
	 */
	public function testConstructModelStringNoMatch()
	{
		$app  = $this->app;
		$page = $app->page('page-a');

		new PageUuid(
			model: $page,
			uuid: 'page://my-page'
		);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('UUID: can\'t create new instance from both model and UUID string that do not match');

		new PageUuid(
			model: $page,
			uuid: 'your-page'
		);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructConfigDisabled()
	{
		$this->app->clone(['options' => ['content' => ['uuid' => false]]]);
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('UUIDs have been disabled via the `content.uuid` config option.');
		new Uuid('page://my-page-uuid');
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
		$this->assertTrue($page->populate());
		$this->assertTrue($subpage->populate());
		$this->assertTrue($page->isCached());
		$this->assertTrue($subpage->isCached());
		$this->assertTrue($page->populate());

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
	 * @covers ::for
	 */
	public function testForConfigDisabled()
	{
		$this->app->clone(['options' => ['content' => ['uuid' => false]]]);
		$this->assertNull(Uuid::for('page://my-page-uuid'));
	}

	/**
	 * @covers ::generate
	 */
	public function testGenerate()
	{
		// default length
		$id = Uuid::generate();
		$this->assertSame(16, strlen($id));

		// custom length
		$id = Uuid::generate(5);
		$this->assertSame(5, strlen($id));

		// UUID v4 mode
		$this->app->clone([
			'options' => [
				'content' => [
					'uuid' => 'uuid-v4'
				]
			]
		]);
		$id = Uuid::generate();
		$this->assertSame(36, strlen($id));

		// UUID v4 mode with custom length (no effect)
		$id = Uuid::generate(5);
		$this->assertSame(36, strlen($id));

		// custom generator callback (overrides the UUID v4 mode)
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

		$this->assertFalse(Uuid::is('page://'));
		$this->assertFalse(Uuid::is('file://'));
		$this->assertFalse(Uuid::is('user://'));
		$this->assertFalse(Uuid::is('page://', 'page'));
		$this->assertFalse(Uuid::is('file://', 'file'));
		$this->assertFalse(Uuid::is('user://', 'user'));
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
	 * @covers ::is
	 */
	public function testIsConfigDisabled()
	{
		$this->app->clone(['options' => ['content' => ['uuid' => false]]]);
		$this->assertFalse(Uuid::is('site://'));
		$this->assertFalse(Uuid::is('page://something'));
		$this->assertFalse(Uuid::is('user://something'));
		$this->assertFalse(Uuid::is('file://something'));
		$this->assertFalse(Uuid::is('file://something/else'));
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
	 * @covers ::model
	 */
	public function testModel()
	{
		// for Uuid that was constructed from model
		$page = $this->app->page('page-a');
		$uuid = $page->uuid();
		$this->assertSame($page, $uuid->model());

		// from cache (enforce via $lazy)
		$uuid = new PageUuid('page://my-page');
		$this->assertFalse($uuid->isCached());
		$this->assertNull($uuid->model(true));
		$uuid->populate();
		$this->assertTrue($uuid->isCached());
		$this->assertSame($page, $uuid->model(true));
		$uuid->clear(true);

		// from index
		$uuid = new PageUuid('page://my-page');
		$this->assertFalse($uuid->isCached());
		$this->assertNull($uuid->model(true));
		$this->assertSame($page, $uuid->model());
		$this->assertTrue($uuid->isCached());
	}

	/**
	 * @covers ::model
	 */
	public function testModelNotFound()
	{
		$this->assertNull(Uuid::for('page://something')->model());
		$this->assertNull(Uuid::for('user://something')->model());
		$this->assertNull(Uuid::for('file://something')->model());
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
	 * @covers ::id
	 * @covers ::toString
	 * @covers ::__toString
	 */
	public function testToString()
	{
		// with ID already stored in content
		$uuid = $this->app->page('page-a')->uuid();
		$this->assertSame('page://my-page', $uuid->toString());
		$this->assertSame('page://my-page', (string)$uuid);

		// with creating new ID
		$uuid = $this->app->page('page-b')->uuid();
		$this->assertIsString($id = $uuid->toString());
		$this->assertSame($id, (string)$uuid);
		$this->assertSame(Str::after($id, '://'), $this->app->page('page-b')->content()->get('uuid')->value());
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
