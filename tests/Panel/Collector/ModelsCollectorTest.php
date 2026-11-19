<?php

namespace Kirby\Panel\Collector;

use Kirby\Cms\File;
use Kirby\Cms\Files;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Cms\Users;
use PHPUnit\Framework\Attributes\CoversClass;

class ModelsCollectorMock extends ModelsCollector
{
	protected function collect(): Files|Pages|Users
	{
		return Pages::factory([
			[
				'slug' => 'a',
			],
			[
				'slug' => 'b',
			],
			[
				'slug' => 'c',
				'content' => [
					'text' => 'Searchword',
				]
			],
		]);
	}

	protected function collectByQuery(): Files|Pages|Users
	{
		return $this->collect()->not('c');
	}

	protected function filter(Files|Pages|Users $models): Files|Pages|Users
	{
		return $models;
	}

	public function parentModel(): Site|Page|User
	{
		return $this->parent();
	}

	public function queryDataArray(): array
	{
		return $this->queryData();
	}

	public function queryModel(): ModelWithContent
	{
		return $this->model();
	}
}

#[CoversClass(ModelsCollector::class)]
class ModelsCollectorTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Collector.ModelsCollector';

	public function testCollectByQuery(): void
	{
		$this->assertCollectByQuery(ModelsCollectorMock::class, 'test', ['a', 'b']);
	}

	public function testFlip(): void
	{
		$this->assertFlip(ModelsCollectorMock::class, ['c', 'b', 'a']);
	}

	public function testIsFlipping(): void
	{
		$this->assertIsFlipping(ModelsCollectorMock::class);
	}

	public function testIsQuerying(): void
	{
		$this->assertIsQuerying(ModelsCollectorMock::class);
	}

	public function testIsSearching(): void
	{
		$this->assertIsSearching(ModelsCollectorMock::class);
	}

	public function testIsSorting(): void
	{
		$this->assertIsSorting(ModelsCollectorMock::class);
	}

	public function testModel(): void
	{
		// falls back to the site
		$collector = new ModelsCollectorMock();
		$this->assertInstanceOf(Site::class, $collector->queryModel());

		// falls back to the parent
		$parent    = new Page(['slug' => 'test']);
		$collector = new ModelsCollectorMock(parent: $parent);
		$this->assertSame($parent, $collector->queryModel());

		// the model takes precedence over the parent
		// and can be a model that cannot be a parent
		$model     = new File(['filename' => 'test.jpg', 'parent' => $parent]);
		$collector = new ModelsCollectorMock(
			model:  $model,
			parent: $parent
		);
		$this->assertSame($model, $collector->queryModel());
	}

	public function testModels(): void
	{
		$this->assertModels(ModelsCollectorMock::class, Pages::class);
	}

	public function testPagination(): void
	{
		$this->assertPagination(ModelsCollectorMock::class);
	}

	public function testParent(): void
	{
		$collector = new ModelsCollectorMock();

		$this->assertInstanceOf(Site::class, $collector->parentModel());

		$collector = new ModelsCollectorMock(parent: new Page(['slug' => 'test']));

		$this->assertInstanceOf(Page::class, $collector->parentModel());
	}

	public function testQueryData(): void
	{
		// no parent, no parent entry
		$collector = new ModelsCollectorMock();
		$this->assertSame([], $collector->queryDataArray());

		$parent    = new Page(['slug' => 'test']);
		$collector = new ModelsCollectorMock(parent: $parent);
		$this->assertSame(['parent' => $parent], $collector->queryDataArray());
	}

	public function testSearch(): void
	{
		$this->assertSearch(ModelsCollectorMock::class, 'Searchword', ['c']);
	}

	public function testSortBy(): void
	{
		$this->assertSortBy(ModelsCollectorMock::class, 'slug desc', ['c', 'b', 'a']);
	}
}
