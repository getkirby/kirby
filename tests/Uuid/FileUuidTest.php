<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
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
		$uuid = new FileUuid('file://just-a-file');
		$this->assertSame('just-a-file', $uuid->id());
	}

	/**
	 * @covers ::id
	 */
	public function testIdGenerate()
	{
		$file = $this->app->file('page-b/foo.pdf');

		$uuid = $file->uuid();
		$this->assertSame(16, strlen($uuid->id()));
		$this->assertSame($uuid->id(), $file->content()->get('uuid')->value());
	}

	/**
	 * @covers ::id
	 */
	public function testIdGenerateExistingButEmpty()
	{
		$file = $this->app->file('page-b/foo.pdf');
		$file->content()->update(['uuid' => '']);

		$uuid = $file->uuid();
		$this->assertSame(16, strlen($uuid->id()));
		$this->assertSame($uuid->id(), $file->content()->get('uuid')->value());
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

	public function providerForMultilang(): array
	{
		return [
			['en', 'Foo'],
			['de', 'Bar'],
		];
	}

	/**
	 * @dataProvider providerForMultilang
	 * @covers ::id
	 */
	public function testMultilang(string $language, string $title)
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
			],
			'options' => [
				'languages' => true
			],
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
				],
				[
					'code'    => 'de',
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'foo',
						'files' => [
							[
								'filename' => 'a.jpg',
								'translations' => [
									[
										'code' => 'en',
										'content' => [
											'title' => 'Foo',
										]
									],
									[
										'code' => 'de',
										'content' => [
											'title' => 'Bar',
										]
									],
								]
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$page = $app->call($language . '/foo');
		$file = $page->files()->first();

		// the title should be translated properly
		$this->assertSame($title, $file->title()->value());

		// the uuid should have been created
		$this->assertSame(16, strlen($file->uuid()->id()));

		// the uuid must match between languages
		$this->assertTrue($file->content('en')->get('uuid')->value() === $file->content('de')->get('uuid')->value());

		// the translation for the default language must be updated
		$this->assertSame($file->translation('en')->content()['uuid'], $file->uuid()->id());

		// the translation for the secondary language must inherit the UUID
		$this->assertSame($file->translation('de')->content()['uuid'], $file->uuid()->id());

		// the uuid must be stored in the primary language file
		$this->assertSame($file->readContent('en')['uuid'], $file->uuid()->id());

		// the secondary language must not have the uuid in the content file
		$this->assertNull($file->readContent('de')['uuid'] ?? null);
	}
}
