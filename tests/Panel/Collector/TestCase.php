<?php

namespace Kirby\Panel\Collector;

use Kirby\Cms\App;
use Kirby\Cms\Pagination;
use Kirby\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Collector.ModelsCollector';

	public function setUp(): void
	{
		parent::setUpTmp();

		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);
	}

	public function tearDown(): void
	{
		parent::tearDownTmp();
	}

	public function assertCollect(string $collectorClass, array $expectedKeys): void
	{
		$collector = new $collectorClass();
		$this->assertModelsInCollector($collector, $expectedKeys);
	}

	public function assertCollectByQuery(string $collectorClass, string $query, array $expectedKeys): void
	{
		$collector = new $collectorClass(
			query: $query
		);

		$this->assertModelsInCollector($collector, $expectedKeys);
	}

	public function assertCollectUnauthenticated(string $collectorClass): void
	{
		$collector = new $collectorClass();
		$this->assertModelsInCollector($collector, []);
	}

	public function assertFlip(string $collectorClass, array $expectedKeys): void
	{
		$collector = new $collectorClass(
			flip: true
		);

		$this->assertModelsInCollector($collector, $expectedKeys);
	}

	public function assertIsFlipping(string $collectorClass): void
	{
		$collector = new $collectorClass();

		$this->assertFalse($collector->isFlipping());

		$collector = new $collectorClass(
			flip: true
		);

		$this->assertTrue($collector->isFlipping());

		// the collector is not flipping when the search is active
		$collector = new $collectorClass(
			flip: true,
			search: 'test'
		);

		$this->assertFalse($collector->isFlipping());
	}

	public function assertIsQuerying(string $collectorClass): void
	{
		$collector = new $collectorClass();

		$this->assertFalse($collector->isQuerying());

		$collector = new $collectorClass(
			query: 'site.children'
		);

		$this->assertTrue($collector->isQuerying());
	}

	public function assertIsSearching(string $collectorClass): void
	{
		$collector = new $collectorClass();

		$this->assertFalse($collector->isSearching());

		$collector = new $collectorClass(
			search: ''
		);

		$this->assertFalse($collector->isSearching());

		$collector = new $collectorClass(
			search: ' '
		);

		$this->assertFalse($collector->isSearching());

		$collector = new $collectorClass(
			search: 'test'
		);

		$this->assertTrue($collector->isSearching());
	}

	public function assertIsSorting(string $collectorClass): void
	{
		$collector = new $collectorClass();

		$this->assertFalse($collector->isSorting());

		$collector = new $collectorClass(
			sortBy: 'title desc'
		);

		$this->assertTrue($collector->isSorting());

		// the collector is not sorting when the search is active
		$collector = new $collectorClass(
			sortBy: 'title desc',
			search: 'test'
		);

		$this->assertFalse($collector->isSorting());
	}

	public function assertModels(string $collectorClass, string $collectionClass): void
	{
		$collector = new $collectorClass();

		$this->assertInstanceOf($collectionClass, $collector->models());
	}

	public function assertModelsInCollector(ModelsCollector $collector, array $expectedKeys): void
	{
		$this->assertSame($expectedKeys, $collector->models()->keys());
	}

	public function assertPagination(string $collectorClass): void
	{
		$collector = new $collectorClass();
		$this->assertInstanceOf(Pagination::class, $collector->pagination());
		$this->assertCount(3, $collector->models());

		// paginate
		$collector = new $collectorClass(
			page: 2,
			limit: 1
		);

		$this->assertSame(3, $collector->pagination()->total());
		$this->assertSame(2, $collector->pagination()->page());
		$this->assertSame(1, $collector->pagination()->limit());
	}

	public function assertSearch(string $collectorClass, string $search, array $expectedKeys): void
	{
		$collector = new $collectorClass(
			search: $search
		);

		$this->assertModelsInCollector($collector, $expectedKeys);
	}

	public function assertSearchAndFlip(string $collectorClass, string $search, array $expectedKeys): void
	{
		$collector = new $collectorClass(
			flip: true,
			search: $search,
		);

		$this->assertModelsInCollector($collector, $expectedKeys);
	}

	public function assertSearchAndSortBy(string $collectorClass, string $search, string $sortBy, array $expectedKeys): void
	{
		$collector = new $collectorClass(
			search: $search,
			sortBy: $sortBy
		);

		$this->assertModelsInCollector($collector, $expectedKeys);
	}

	public function assertSortBy(string $collectorClass, string $sortBy, array $expectedKeys): void
	{
		$collector = new $collectorClass(
			sortBy: $sortBy
		);

		$this->assertModelsInCollector($collector, $expectedKeys);
	}
}
