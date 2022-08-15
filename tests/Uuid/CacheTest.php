<?php

namespace Kirby\Uuid;

use Kirby\Cache\Cache as BaseCache;
use Kirby\Cache\MemoryCache;
use Kirby\Cache\NullCache;
use Kirby\Cms\Field;
use Kirby\Cms\Page;
use Kirby\Cms\StructureObject;

/**
 * @coversDefaultClass \Kirby\Uuid\Cache
 */
class CacheTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::clear
	 * @covers ::exists
	 * @covers ::populate
	 */
	public function testActions()
	{
		$model = new Page(['slug' => 'a', 'content' => ['uuid' => 'my-id']]);
		$uuid  = Uuid::for($model);
		$cache = $uuid->cache();

		$this->assertFalse($cache->exists());
		$cache->populate();
		$this->assertTrue($cache->exists());
		$cache->clear();
		$this->assertFalse($cache->exists());
	}

	/**
	 * @covers ::find
	 */
	public function testFindPage()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'my-id'],
						'files'   => [['filename' => 'test.jpg']]
					]
				]
			]
		]);

		$page = $app->page('a');
		$uuid = Uuid::for($page);
		$uuid->populate();

		$uuid = Uuid::for('page://my-id');
		$this->assertSame($page, Cache::find($uuid));
	}

	/**
	 * @covers ::find
	 */
	public function testFindFile()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'my-id'],
						'files'   => [
							[
								'filename' => 'test.jpg',
								'content' => ['uuid' => 'my-file-id']
							]
						]
					]
				]
			]
		]);

		$file = $app->file('a/test.jpg');
		$uuid = Uuid::for($file);
		$uuid->populate();

		$uuid = Uuid::for('file://my-file-id');
		$this->assertSame($file, Cache::find($uuid));
	}

	/**
	 * @covers ::find
	 */
	public function testFindStructure()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => [
							'uuid' => 'my-id',
							'foo' => '
-
  uuid: my-struct-1
-
  uuid: my-struct-2
'
						]
					]
				]
			]
		]);

		$model = $app->page('a')->foo()->toStructure()->nth(1);
		$uuid  = Uuid::for($model);
		$uuid->populate();

		$uuid = Uuid::for('struct://my-struct-2');
		$this->assertTrue($model->is(Cache::find($uuid)));
	}

	/**
	 * @covers ::find
	 */
	public function testFindNotCached()
	{
		$uuid = Uuid::for('page://foo');
		$this->assertNull(Cache::find($uuid));
	}

	/**
	 * @covers ::key
	 */
	public function testKey()
	{
		$model = new Page(['slug' => 'a', 'content' => ['uuid' => 'my-id']]);
		$uuid  = Uuid::for($model);
		$cache = $uuid->cache();

		$this->assertSame('page/my/-id', $cache->key());
	}

	/**
	 * @covers ::store
	 */
	public function testStore()
	{
		$this->assertInstanceOf(BaseCache::class, Cache::store());

		$this->app->clone([
			'options' => [
				'cache' => [
					'uuid' => ['type' => 'memory']
				]
			]
		]);
		$this->assertInstanceOf(MemoryCache::class, Cache::store());

		$this->app->clone([
			'options' => [
				'cache' => [
					'uuid' => false
				]
			]
		]);
		$this->assertInstanceOf(NullCache::class, Cache::store());
	}

	/**
	 * @covers ::value
	 */
	public function testValuePage()
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

		$page  = $app->page('a');
		$uuid  = Uuid::for($page);
		$cache = $uuid->cache();

		$this->assertSame($page->id(), $cache->value());
		$this->assertSame('a', $cache->value());
	}

	/**
	 * @covers ::value
	 */
	public function testValueFile()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'my-id'],
						'files'   => [['filename' => 'test.jpg']]
					]
				]
			]
		]);

		$file  = $app->file('a/test.jpg');
		$uuid  = Uuid::for($file);
		$cache = $uuid->cache();

		$this->assertSame('page://my-id/test.jpg', $cache->value());
	}

	/**
	 * @covers ::value
	 */
	public function testValueStructure()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'my-id']
					]
				]
			]
		]);

		$page  = $app->page('a');
		$field = new Field($page, 'foo', '');
		$model = new StructureObject([
			'id'     => 'my-struct',
			'parent' => $page,
			'field'  => $field
		]);
		$uuid  = Uuid::for($model);
		$cache = $uuid->cache();

		$this->assertSame('page://my-id/foo/my-struct', $cache->value());
	}

	/**
	 * @covers ::value
	 */
	public function testValueModelNotFound()
	{
		$uuid  = Uuid::for('page://foo');

		$this->expectException('Kirby\Exception\LogicException');
		$this->expectExceptionMessage('UUID could not be resolved to model');

		$uuid->cache()->value();
	}
}
