<?php

namespace Kirby\Panel\Collector;

use Kirby\Cms\Pages;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PagesCollector::class)]
class PagesCollectorTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Collector.PagesCollector';

	public function setUpPages()
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'a',
						'template' => 'default',
					],
					[
						'slug'     => 'b',
						'template' => 'article',
					],
					[
						'slug'     => 'c',
						'template' => 'article',
					],
				]
			]
		]);
	}

	public function testCollect(): void
	{
		$this->setUpPages();
		$this->app->impersonate('kirby');
		$this->assertCollect(PagesCollector::class, ['a', 'b', 'c']);
	}

	public function testCollectByStatus(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'b', 'num' => 1],
					['slug' => 'c'],
				],
				'drafts' => [
					['slug' => 'a'],
				]
			]
		]);

		$this->app->impersonate('kirby');

		// all
		$collector = new PagesCollector();

		$this->assertModelsInCollector($collector, ['b', 'c', 'a'], );

		// listed
		$collector = new PagesCollector(
			status: 'listed'
		);

		$this->assertModelsInCollector($collector, ['b'], );

		// unlisted
		$collector = new PagesCollector(
			status: 'unlisted'
		);

		$this->assertModelsInCollector($collector, ['c'], );

		// published
		$collector = new PagesCollector(
			status: 'published'
		);

		$this->assertModelsInCollector($collector, ['b', 'c'], );

		// drafts
		$collector = new PagesCollector(
			status: 'draft'
		);

		$this->assertModelsInCollector($collector, ['a'], );
	}

	public function testCollectByQuery(): void
	{
		$this->setUpPages();
		$this->app->impersonate('kirby');
		$this->assertCollectByQuery(PagesCollector::class, 'site.children.filterBy("intendedTemplate", "article")', ['b', 'c']);
	}

	public function testCollectUnauthenticated(): void
	{
		$this->setUpPages();
		$this->assertCollectUnauthenticated(PagesCollector::class);
	}

	public function testFilterByTemplates(): void
	{
		$this->setUpPages();
		$this->app->impersonate('kirby');

		$collector = new PagesCollector(
			templates: ['article']
		);

		$this->assertModelsInCollector($collector, ['b', 'c'], );
	}

	public function testFilterByTemplatesIgnore(): void
	{
		$this->setUpPages();
		$this->app->impersonate('kirby');

		$collector = new PagesCollector(
			templatesIgnore: ['article']
		);

		$this->assertModelsInCollector($collector, ['a'], );
	}

	public function testFlip(): void
	{
		$this->setUpPages();
		$this->app->impersonate('kirby');

		$this->assertFlip(PagesCollector::class, ['c', 'b', 'a']);
	}

	public function testIsFlipping(): void
	{
		$this->assertIsFlipping(PagesCollector::class);
	}

	public function testIsQuerying(): void
	{
		$this->assertIsQuerying(PagesCollector::class);
	}

	public function testIsSearching(): void
	{
		$this->assertIsSearching(PagesCollector::class);
	}

	public function testIsSorting(): void
	{
		$this->assertIsSorting(PagesCollector::class);
	}

	public function testModels(): void
	{
		$this->assertModels(PagesCollector::class, Pages::class);
	}

	public function testPagination(): void
	{
		$this->setUpPages();
		$this->app->impersonate('kirby');
		$this->assertPagination(PagesCollector::class);
	}

	public function testSearch(): void
	{
		$this->setUpPages();
		$this->app->impersonate('kirby');
		$this->app->site()->children()->find('c')->update([
			'title' => 'Searchword'
		]);

		$this->assertSearch(PagesCollector::class, 'Searchword', ['c']);
	}

	public function testSearchAndFlip(): void
	{
		$this->setUpPages();
		$this->app->impersonate('kirby');

		$this->app->site()->children()->find('b')->update([
			'title' => 'Searchword'
		]);

		$this->app->site()->children()->find('c')->update([
			'title' => 'Searchword'
		]);

		// flipping should be ignored for the search
		$this->assertSearchAndFlip(PagesCollector::class, 'Searchword', ['b', 'c']);
	}

	public function testSearchAndSortBy(): void
	{
		$this->setUpPages();
		$this->app->impersonate('kirby');

		$this->app->site()->children()->find('b')->update([
			'title' => 'Searchword'
		]);

		$this->app->site()->children()->find('c')->update([
			'title' => 'Searchword'
		]);

		// the sorting should be ignored for the search
		$this->assertSearchAndSortBy(PagesCollector::class, 'Searchword', 'slug desc', ['b', 'c']);
	}

	public function testSortBy(): void
	{
		$this->setUpPages();
		$this->app->impersonate('kirby');
		$this->assertSortBy(PagesCollector::class, 'slug desc', ['c', 'b', 'a']);
	}
}
