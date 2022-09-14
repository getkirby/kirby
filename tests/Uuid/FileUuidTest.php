<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\File;

/**
 * @coversDefaultClass \Kirby\Uuid\FileUuid
 */
class FileUuidTest extends TestCase
{
	/**
	 * @covers ::findByCache
	 */
	public function testFindByCache()
	{
		$file = $this->app->file('page-a/test.pdf');

		// not yet in cache
		$uuid  = new FileUuid('file://my-file');
		$this->assertFalse($uuid->isCached());
		$this->assertNull($uuid->model(true));

		// fill cache
		$file->uuid()->populate();

		// retrieve from cache
		$this->assertTrue($uuid->isCached());
		$this->assertTrue($file->is($uuid->model(true)));
	}

	/**
	 * @covers ::findByIndex
	 */
	public function testFindByIndex()
	{
		$file = $this->app->file('page-a/test.pdf');
		$uuid  = new FileUuid('file://my-file');
		$this->assertFalse($uuid->isCached());
		$this->assertNull($uuid->model(true));
		$this->assertTrue($file->is($uuid->model()));
		$this->assertTrue($uuid->isCached());

		// not found
		$uuid = new FileUuid('file://does-not-exist');
		$this->assertNull($uuid->model());
	}

	/**
	 * @covers ::id
	 */
	public function testId()
	{
		// with UUID string
		$uuid = new FileUuid('file://just-a-file');
		$this->assertSame('just-a-file', $uuid->id());

		// with model with nothing in its content file yet
		$file = $this->app->file('page-b/foo.pdf');
		$uuid = $file->uuid();
		$this->assertNull($file->content()->get('uuid')->value());

		$id   = $uuid->id();
		$file = $this->app->file('page-b/foo.pdf'); // since $file is immutable
		$this->assertSame(16, strlen($id));
		$this->assertSame($file->content()->get('uuid')->value(), $id);
	}

	/**
	 * @covers ::index
	 */
	public function testIndex()
	{
		$index = FileUuid::index();
		$this->assertInstanceOf(Generator::class, $index);
		$this->assertInstanceOf(File::class, $index->current());
		$this->assertSame(4, iterator_count($index));
	}

	/**
	 * @covers ::retrieveId
	 */
	public function testRetrieveId()
	{
		$file = $this->app->file('page-a/test.pdf');
		$this->assertSame('my-file', ModelUuid::retrieveId($file));
	}

	/**
	 * @covers ::url
	 */
	public function testUrl()
	{
		$file = $this->app->file('page-a/test.pdf');
		$url  = 'https://getkirby.com/@/file/my-file';
		$this->assertSame($url, $file->uuid()->url());
	}

	/**
	 * @covers ::value
	 */
	public function testValue()
	{
		$file = $this->app->file('page-a/test.pdf');
		$uuid = $file->uuid();
		$expected = ['parent' => 'page://my-page', 'filename' => 'test.pdf'];
		$this->assertSame($expected, $uuid->value());
	}
}
