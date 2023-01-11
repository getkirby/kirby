<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
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
		$uuid = new PageUuid('page://just-a-file');
		$this->assertSame('just-a-file', $uuid->id());
	}

	/**
	 * @covers ::id
	 */
	public function testIdGenerate()
	{
		$page = $this->app->page('page-b');

		$uuid = $page->uuid();
		$this->assertSame(16, strlen($uuid->id()));
		$this->assertSame($uuid->id(), $page->content()->get('uuid')->value());
	}

	/**
	 * @covers ::id
	 */
	public function testIdGenerateExistingButEmpty()
	{
		$page = $this->app->page('page-b');
		$page->content()->update(['uuid' => '']);

		$uuid = $page->uuid();
		$this->assertSame(16, strlen($uuid->id()));
		$this->assertSame($uuid->id(), $page->content()->get('uuid')->value());
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
						'translations' => [
							[
								'code' => 'en',
								'content' => [
									'title' => 'Foo',
								]
							],
							[
								'code' => 'de',
								'slug' => 'bar',
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
		$this->assertSame($page->readContent('en')['uuid'], $page->uuid()->id());

		// the secondary language must not have the uuid in the content file
		$this->assertNull($page->readContent('de')['uuid'] ?? null);
	}
}
