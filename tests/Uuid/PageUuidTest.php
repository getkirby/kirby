<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PageUuid::class)]
class PageUuidTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Uuid.PageUuid';

	public function testFindByCache(): void
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
		$this->assertIsPage($page, $uuid->model(true));
	}

	public function testFindByIndex(): void
	{
		$page = $this->app->page('page-a');
		$uuid  = new PageUuid('page://my-page');
		$this->assertFalse($uuid->isCached());
		$this->assertNull($uuid->model(true));
		$this->assertIsPage($page, $uuid->model());
		$this->assertTrue($uuid->isCached());

		// not found
		$uuid = new PageUuid('page://does-not-exist');
		$this->assertNull($uuid->model());
	}

	public function testId(): void
	{
		$uuid = new PageUuid('page://just-a-file');
		$this->assertSame('just-a-file', $uuid->id());
	}

	public function testIdGenerate(): void
	{
		$page = $this->app->page('page-b');

		$uuid = $page->uuid();
		$this->assertSame(16, strlen($uuid->id()));
		$this->assertSame($uuid->id(), $page->content()->get('uuid')->value());
	}

	public function testIdGenerateExistingButEmpty(): void
	{
		$page = $this->app->page('page-b');
		$page->version()->save(['uuid' => '']);

		$uuid = $page->uuid();
		$this->assertSame(16, strlen($uuid->id()));
		$this->assertSame($uuid->id(), $page->content()->get('uuid')->value());
	}

	public function testIndex(): void
	{
		$index = PageUuid::index();
		$this->assertInstanceOf(Generator::class, $index);
		$this->assertIsPage($index->current());
		$this->assertSame(3, iterator_count($index));
	}

	public function testRetrieveId(): void
	{
		$page = $this->app->page('page-a');
		$this->assertSame('my-page', ModelUuid::retrieveId($page));
	}

	public function testToPermalink(): void
	{
		$page = $this->app->page('page-a');
		$url  = 'https://getkirby.com/@/page/my-page';
		$this->assertSame($url, $page->uuid()->toPermalink());
	}

	public function testUrlWithLanguageWithCustomUrl(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'options' => [
				'languages' => true
			],
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
					'url'     => '/'
				],
				[
					'code'    => 'de',
				]
			],
			'site' => [
				'children' => [
					['slug' => 'foo', 'content' => ['uuid' => 'my-page']]
				]
			]
		]);

		$page = $app->page('foo');
		$url  = 'https://getkirby.com/@/page/my-page';
		$this->assertSame($url, $page->uuid()->toPermalink());
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
			'urls' => [
				'index' => 'https://getkirby.com'
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
		]);

		$page = $app->call($language . '/foo');

		// the title should be translated properly
		$this->assertSame($title, $page->title()->value());

		// the uuid should have been created
		$this->assertSame(16, strlen($page->uuid()->id()));

		// the uuid must match between languages
		$this->assertTrue($page->content('en')->get('uuid')->value() === $page->content('de')->get('uuid')->value());

		// the translation for the default language must be updated
		$this->assertSame($page->translation('en')->content()['uuid'], $page->uuid()->id());

		// the translation for the secondary language must inherit the UUID
		$this->assertSame($page->translation('de')->content()['uuid'], $page->uuid()->id());

		// the uuid must be stored in the primary language file
		$this->assertSame($page->version()->read('en')['uuid'], $page->uuid()->id());

		// the secondary language must not have the uuid in the content file
		$this->assertNull($page->version()->read('de')['uuid'] ?? null);

		$this->assertStringStartsWith('https://getkirby.com/' . $language . '/@/page/', $page->uuid()->toPermalink());
	}
}
