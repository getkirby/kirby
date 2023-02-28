<?php

namespace Kirby\Cms;

use ReflectionClass;

class CollectionTestCase extends TestCase
{
	public $collection = null;
	public $collectionOptions = [];
	public $collectionType = null;

	protected function _app()
	{
		return new App([
			'roots' => [
				'collections' => __DIR__ . '/fixtures/collections'
			]
		]);
	}

	public function collection($name = null, $collectionOptions = [])
	{
		return $this->kirby()->collection($name ?? $this->collection, $collectionOptions ?? $this->collectionOptions);
	}

	public function collectionRoot(): string
	{
		return $this->kirby()->root('collections') . '/' . $this->collectionName() . '.php';
	}

	public function collectionName(): string
	{
		if ($this->collection !== null) {
			return $this->collection;
		}

		$reflect   = new ReflectionClass($this);
		$className = $reflect->getShortName();

		return strtolower(str_replace('CollectionTest', '', $className));
	}

	public function collectionPagination()
	{
		return $this->collection()->pagination();
	}

	public function assertCollectionCount(int $count)
	{
		$this->assertCount($count, $this->collection());
	}

	public function assertCollectionHasPagination()
	{
		$this->assertInstanceOf(Pagination::class, $this->collectionPagination());
	}

	public function assertCollectionHasNoPagination()
	{
		$this->assertNotInstanceOf(Pagination::class, $this->collectionPagination());
	}

	public function testCollectionHasOptions()
	{
		$app = $this->_app();
		$result = $app->collection('options', ['a' => 10, 'b' => 10, 'c' => 10]);

		$this->assertSame(30, $result);
	}

	public function testCollectionHasDefaultOptions()
	{
		$app = $this->_app();
		$result = $app->collection('options_with_default', ['a' => 10, 'b' => 10, 'c' => 10]);

		$this->assertSame($result['kirby'], $app);
		$this->assertSame($result['result'], 30);
	}

	public function testCollectionHasDefaultOptionsDoesntOverwrite()
	{
		$app = $this->_app();
		$result = $app->collection('options_with_default', ['a' => 10, 'b' => 10, 'c' => 10, 'kirby' => 'i_am_not_kirby']);

		$this->assertSame($result['kirby'], $app);
		$this->assertSame($result['result'], 30);
	}

	public function testCollectionHasDefaultsToNullWhenNoOptionSpecified()
	{
		$app = $this->_app();
		$result = $app->collection('options_with_default_null');

		$this->assertNull($result['iShouldBeNull']);
	}

	public function testCollectionType()
	{
		if ($this->collectionType === null) {
			$this->markTestSkipped();
		}

		$this->assertInstanceOf($this->collectionType, $this->collection());
	}
}
