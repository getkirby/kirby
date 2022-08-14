<?php

namespace Kirby\Uuid;

use Kirby\Cms\Block;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\StructureObject;
use Kirby\Cms\User;

/**
 * @coversDefaultClass \Kirby\Uuid\Uuid
 */
class UuidTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstructInvalidType()
	{
		$this->expectException('Kirby\Exception\InvalidArgumentException');
		$this->expectExceptionMessage('Invalid URL scheme: foo');
		new Uuid(uuid: 'foo://bar');
	}

	/**
	 * @covers ::cache
	 */
	public function testCache()
	{
		$uuid = new Uuid();
		$this->assertInstanceOf(Cache::class, $uuid->cache());
	}

	/**
	 * @covers ::clear
	 */
	public function testClear()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'children' => [
							[
								'slug' => 'b'
							]
						]
					]
				]
			]
		]);

		$page    = $app->page('a')->uuid();
		$subpage = $app->page('a/b')->uuid();

		$page->populate();
		$subpage->populate();

		$this->assertTrue($page->cache()->exists());
		$this->assertTrue($subpage->cache()->exists());

		// only page
		$page->clear();

		$this->assertFalse($page->cache()->exists());
		$this->assertTrue($subpage->cache()->exists());

		// recursively also subpage
		$page->clear(true);

		$this->assertFalse($page->cache()->exists());
		$this->assertFalse($subpage->cache()->exists());
	}

	/**
	 * @covers ::context
	 */
	public function testContext()
	{
		// without
		$uuid = new Uuid();
		$this->assertNull($uuid->context());

		// with
		$uuid = new Uuid(context: $expected = new Collection());
		$this->assertSame($expected, $uuid->context());
	}

	/**
	 * @covers ::create
	 */
	public function testCreate()
	{
		$model = new Block(['type' => 'a']);
		$uuid  = Uuid::for($model);

		$this->expectException('Kirby\Exception\LogicException');
		$this->expectExceptionMessage('Can only create and write ID string to model with content');

		// calling protected `create` method
		(fn () => $this->create())->call($uuid);
	}

	/**
	 * @covers ::__construct
	 * @covers ::for
	 */
	public function testFor()
	{
		// with string
		$uuid = Uuid::for('page://my-id');
		$this->assertSame('page', $uuid->type());
		$this->assertSame('my-id', $uuid->id());

		// with object
		$model = new Page(['slug' => 'a', 'content' => ['uuid' => 'my-id']]);
		$uuid = Uuid::for($model);
		$this->assertSame('page', $uuid->type());
		$this->assertSame('my-id', $uuid->id());
	}

	/**
	 * @covers ::id
	 */
	public function testId()
	{
		// with string
		$uuid = Uuid::for('page://my-id');
		$this->assertSame('my-id', $uuid->id());

		// with object
		$model = new Page(['slug' => 'a']);
		$uuid = Uuid::for($model);
		$this->assertSame(15, strlen($uuid->id()));
		$this->assertSame(Id::get($uuid->resolve()), $uuid->id());
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
		$this->assertTrue(Uuid::is('struct://something'));
		$this->assertTrue(Uuid::is('block://something'));
		$this->assertTrue(Uuid::is('block://something/else'));

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
	 * @covers ::isCached
	 */
	public function testIsCached()
	{
		$model = new Page(['slug' => 'a', 'content' => ['uuid' => 'my-id']]);
		$uuid  = Uuid::for($model);
		$this->assertFalse($uuid->isCached());
		$uuid->populate();
		$this->assertTrue($uuid->isCached());
	}

	/**
	 * @covers ::populate
	 */
	public function testPopulate()
	{
		$model = new Page(['slug' => 'a', 'content' => ['uuid' => 'my-id']]);
		$uuid = Uuid::for($model);

		$this->assertFalse($uuid->cache()->exists());
		$uuid->populate();
		$this->assertTrue($uuid->cache()->exists());
	}

	/**
	 * @covers ::create
	 * @covers ::render
	 * @covers ::__toString
	 */
	public function testRender()
	{
		// with ID already stored in content
		$model = new Page(['slug' => 'a', 'content' => ['uuid' => 'my-id']]);
		$uuid  = Uuid::for($model);
		$this->assertSame('page://my-id', $uuid->render());
		$this->assertSame('page://my-id', (string)$uuid);

		// with creating new ID
		$model = new Page(['slug' => 'a']);
		$uuid  = Uuid::for($model);
		$this->assertIsString($id = $uuid->render());
		$this->assertSame($id, (string)$uuid);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolvePageFromCache()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a'
					]
				]
			]
		]);

		$page = $app->page('a');
		$uuid = Uuid::for($page);
		$uuid->populate();
		$id = $uuid->render();

		$model = Uuid::for($id)->resolve(true);
		$this->assertTrue($page->is($model));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolvePageFromIndex()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => ['uuid' => 'test-a']
					]
				]
			]
		]);

		$page = $app->page('a');

		// with UUID string
		$model = Uuid::for('page://test-a')->resolve();
		$this->assertSame($page, $model);

		// with object instance
		$model = Uuid::for($page)->resolve();
		$this->assertSame($page, $model);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveSite()
	{
		// with UUID string
		$site = site();
		$model = Uuid::for('site://')->resolve();
		$this->assertSame($site, $model);

		// with site instance
		$model = Uuid::for($site)->resolve();
		$this->assertSame($site, $model);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveUser()
	{
		$app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com'
				],
				[
					'id'    => 'homer',
					'email' => 'homer@simpson.com'
				]
			]
		]);

		$user1 = $app->user('test');
		$user2 = $app->user('homer');

		// with UUID string
		$model = Uuid::for('user://test')->resolve();
		$this->assertSame($user1, $model);
		$model = Uuid::for('user://homer')->resolve();
		$this->assertSame($user2, $model);

		// with user instance
		$model = Uuid::for($user1)->resolve();
		$this->assertSame($user1, $model);
		$model = Uuid::for($user2)->resolve();
		$this->assertSame($user2, $model);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveNotFound()
	{
		$this->assertNull(Uuid::for('file://homer-simpson')->resolve());
	}

	public function typeProvider()
	{
		return [
			// UUID strings
			['site://', 'site'],
			['page://page-id', 'page'],
			['file://file-id', 'file'],
			['user://user-id', 'user'],
			// TODO: ativate after implemting suport
			// ['block://block-id', 'block'],
			// ['struct://structure-id', 'struct'],
			// model objects
			[new Site(), 'site'],
			[$p = new Page(['slug' => 'a']), 'page'],
			[new File(['filename' => 'a', 'parent' => $p]), 'file'],
			[new User([]), 'user'],
			// TODO: ativate after implemting suport
			// [new Block(['type' => 'a']), 'block'],
			// [new StructureObject(['id' => 'a']), 'struct'],
		];
	}

	/**
	 * @covers ::type
	 * @dataProvider typeProvider
	 */
	public function testType(string|Identifiable $seed, string $type)
	{
		$uuid = Uuid::for($seed);
		$this->assertSame($type, $uuid->type());
	}

	/**
	 * @covers ::url
	 */
	public function testUrl()
	{
		$url = Uuid::for('page://a-b-c')->url();
		$this->assertSame('//@/page/a-b-c', $url);
	}
}
