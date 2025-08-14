<?php

namespace Kirby\Panel\Collector;

use Kirby\Cms\Files;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FilesCollector::class)]
class FilesCollectorTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Collector.FilesCollector';

	public function setUpFiles()
	{
		$this->app = $this->app->clone([
			'site' => [
				'files' => [
					[
						'filename' => 'image-a.jpg',
						'template' => 'default',
					],
					[
						'filename' => 'image-b.jpg',
						'template' => 'hero',
					],
					[
						'filename' => 'image-c.jpg',
						'template' => 'hero',
					],
				]
			]
		]);
	}

	public function testCollect(): void
	{
		$this->setUpFiles();
		$this->app->impersonate('kirby');
		$this->assertCollect(FilesCollector::class, ['image-a.jpg', 'image-b.jpg', 'image-c.jpg']);
	}

	public function testCollectByQuery(): void
	{
		$this->setUpFiles();
		$this->app->impersonate('kirby');
		$this->assertCollectByQuery(FilesCollector::class, 'site.files.template("hero")', ['image-b.jpg', 'image-c.jpg']);
	}

	public function testCollectSorted(): void
	{
		$this->setUpFiles();
		$this->app->impersonate('kirby');

		$this->app->site()->files()->find('image-c.jpg')->changeSort(1);
		$this->app->site()->files()->find('image-a.jpg')->changeSort(2);
		$this->app->site()->files()->find('image-b.jpg')->changeSort(3);

		$this->assertCollect(FilesCollector::class, ['image-c.jpg', 'image-a.jpg', 'image-b.jpg']);
	}

	public function testCollectUnauthenticated(): void
	{
		$this->setUpFiles();
		$this->assertCollectUnauthenticated(FilesCollector::class);
	}

	public function testFilterByTemplates(): void
	{
		$this->setUpFiles();
		$this->app->impersonate('kirby');

		$collector = new FilesCollector(
			template: 'hero'
		);

		$this->assertModelsInCollector($collector, ['image-b.jpg', 'image-c.jpg'], );
	}

	public function testFlip(): void
	{
		$this->setUpFiles();
		$this->app->impersonate('kirby');

		$this->assertFlip(FilesCollector::class, ['image-c.jpg', 'image-b.jpg', 'image-a.jpg']);
	}

	public function testIsFlipping(): void
	{
		$this->assertIsFlipping(FilesCollector::class);
	}

	public function testIsQuerying(): void
	{
		$this->assertIsQuerying(FilesCollector::class);
	}

	public function testIsSearching(): void
	{
		$this->assertIsSearching(FilesCollector::class);
	}

	public function testIsSorting(): void
	{
		$collector = new FilesCollector();

		// The files collector is always sorting.
		// Either by the sortBy parameter or by the default sort order.
		$this->assertTrue($collector->isSorting());

		$collector = new FilesCollector(
			sortBy: 'filename desc'
		);

		$this->assertTrue($collector->isSorting());

		// the collector is even sorting when the search is active
		$collector = new FilesCollector(
			sortBy: 'filename desc',
			search: 'test'
		);

		$this->assertTrue($collector->isSorting());
	}

	public function testModels(): void
	{
		$this->assertModels(FilesCollector::class, Files::class);
	}

	public function testPagination(): void
	{
		$this->setUpFiles();
		$this->app->impersonate('kirby');
		$this->assertPagination(FilesCollector::class);
	}

	public function testSearch(): void
	{
		$this->setUpFiles();
		$this->app->impersonate('kirby');
		$this->app->site()->files()->find('image-c.jpg')->update([
			'alt' => 'Searchword'
		]);

		$this->assertSearch(FilesCollector::class, 'Searchword', ['image-c.jpg']);
	}

	public function testSearchAndFlip(): void
	{
		$this->setUpFiles();
		$this->app->impersonate('kirby');

		$this->app->site()->files()->find('image-b.jpg')->update([
			'alt' => 'Searchword'
		]);

		$this->app->site()->files()->find('image-c.jpg')->update([
			'alt' => 'Searchword'
		]);

		// flipping should be ignored for the search
		$this->assertSearchAndFlip(FilesCollector::class, 'Searchword', ['image-b.jpg', 'image-c.jpg']);
	}

	public function testSearchAndSortBy(): void
	{
		$this->setUpFiles();
		$this->app->impersonate('kirby');

		$this->app->site()->files()->find('image-b.jpg')->update([
			'alt' => 'Searchword'
		]);

		$this->app->site()->files()->find('image-c.jpg')->update([
			'alt' => 'Searchword'
		]);

		// the sorting should be ignored for the search
		$this->assertSearchAndSortBy(FilesCollector::class, 'Searchword', 'filename desc', ['image-b.jpg', 'image-c.jpg']);
	}

	public function testSortBy(): void
	{
		$this->setUpFiles();
		$this->app->impersonate('kirby');
		$this->assertSortBy(FilesCollector::class, 'filename desc', ['image-c.jpg', 'image-b.jpg', 'image-a.jpg']);
	}
}
