<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FileUuid::class)]
class FileUuidTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Uuid.FileUuid';

	public function testFindByCache(): void
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
		$this->assertIsFile($file, $uuid->model(true));
	}

	public function testFindByIndex(): void
	{
		$file = $this->app->file('page-a/test.pdf');
		$uuid  = new FileUuid('file://my-file');
		$this->assertFalse($uuid->isCached());
		$this->assertNull($uuid->model(true));
		$this->assertIsFile($file, $uuid->model());
		$this->assertTrue($uuid->isCached());

		// not found
		$uuid = new FileUuid('file://does-not-exist');
		$this->assertNull($uuid->model());
	}

	public function testId(): void
	{
		$uuid = new FileUuid('file://just-a-file');
		$this->assertSame('just-a-file', $uuid->id());
	}

	public function testIdGenerate(): void
	{
		$file = $this->app->file('page-b/foo.pdf');

		$uuid = $file->uuid();
		$this->assertSame(16, strlen($uuid->id()));
		$this->assertSame($uuid->id(), $file->content()->get('uuid')->value());
	}

	public function testIdGenerateExistingButEmpty(): void
	{
		$file = $this->app->file('page-b/foo.pdf');
		$file->version()->save(['uuid' => '']);

		$uuid = $file->uuid();
		$this->assertSame(16, strlen($uuid->id()));
		$this->assertSame($uuid->id(), $file->content()->get('uuid')->value());
	}

	public function testIndex(): void
	{
		$index = FileUuid::index();
		$this->assertInstanceOf(Generator::class, $index);
		$this->assertIsFile($index->current());
		$this->assertSame(4, iterator_count($index));
	}

	public function testRetrieveId(): void
	{
		$file = $this->app->file('page-a/test.pdf');
		$this->assertSame('my-file', ModelUuid::retrieveId($file));
	}

	public function testToPermalink(): void
	{
		$file = $this->app->file('page-a/test.pdf');
		$url  = 'https://getkirby.com/@/file/my-file';
		$this->assertSame($url, $file->uuid()->toPermalink());
		$this->assertSame($url, $file->uuid()->url());
	}

	public function testValue(): void
	{
		$file = $this->app->file('page-a/test.pdf');
		$uuid = $file->uuid();
		$expected = ['parent' => 'page://my-page', 'filename' => 'test.pdf'];
		$this->assertSame($expected, $uuid->value());
	}

	public static function multilangProvider(): array
	{
		return [
			['en', 'Foo'],
			['de', 'Bar'],
		];
	}

	#[DataProvider('multilangProvider')]
	public function testMultilang(string $language, string $title): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
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
		$this->assertSame($file->version()->read('en')['uuid'], $file->uuid()->id());

		// the secondary language must not have the uuid in the content file
		$this->assertNull($file->version()->read('de')['uuid'] ?? null);
	}
}
