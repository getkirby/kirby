<?php

namespace Kirby\Cms;

use Iterator;
use Kirby\Toolkit\Obj;
use PHPUnit\Framework\Attributes\CoversClass;

class MockLazyCollection extends LazyCollection
{
	public bool $hydrated = false;
	public bool $iterated = false;
	public array $hydratedElements = [];

	public function getIterator(): Iterator
	{
		$this->iterated = true;
		return parent::getIterator();
	}

	protected function hydrateElement(string $key): object
	{
		// log for test assertions
		$this->hydratedElements[] = $key;

		return $this->data[$key] = new Obj(['id' => $key, 'type' => 'hydrated']);
	}
}

#[CoversClass(LazyCollection::class)]
class LazyCollectionTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.LazyCollection';

	public function testHydrateAll(): void
	{
		$collection = new MockLazyCollection();
		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$collection->hydrateAll();

		$this->assertSame([
			'a' => ['id' => 'a', 'type' => 'static'],
			'b' => ['id' => 'b', 'type' => 'hydrated'],
			'c' => ['id' => 'c', 'type' => 'hydrated']
		], array_map(fn ($element) => $element->toArray(), $collection->data));
		$this->assertSame(['b', 'c'], $collection->hydratedElements);
		$this->assertTrue($collection->hydrated);

		// another invocation should not hydrate again
		$collection->iterated = false;
		$collection->hydrateAll();
		$this->assertFalse($collection->iterated);
	}

	public function testGet(): void
	{
		$collection = new MockLazyCollection();
		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$this->assertSame('a', $collection->get('a')->id);
		$this->assertSame('static', $collection->get('a')->type);
		$this->assertSame('b', $collection->get('b')->id);
		$this->assertSame('hydrated', $collection->get('b')->type);
		$this->assertNull($collection->get('d'));

		$this->assertSame(['b'], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);
	}

	public function testIterate(): void
	{
		$collection = new MockLazyCollection();
		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$i = 0;
		foreach ($collection as $key => $value) {
			$this->assertSame($key, $value->id);
			$this->assertSame($key === 'a' ? 'static' : 'hydrated', $value->type);

			$i++;
		}

		$this->assertSame(3, $i);
		$this->assertSame(['b', 'c'], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);
	}

	public function testFilter(): void
	{
		$collection = new MockLazyCollection();
		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$result1 = $collection->filter('type', 'static');
		$this->assertSame(['a'], $result1->pluck('id'));

		$this->assertSame(['b', 'c'], $collection->hydratedElements);
		$this->assertTrue($collection->hydrated);

		// another operation should still work but no longer needs to hydrate
		$result2 = $collection->filter('type', 'hydrated');
		$this->assertSame(['b', 'c'], $result2->pluck('id'));
	}

	public function testFirst(): void
	{
		$collection = new MockLazyCollection();

		$this->assertNull($collection->first());

		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$this->assertSame('a', $collection->first()->id);
		$this->assertSame('static', $collection->first()->type);

		$this->assertSame([], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);

		$collection->data = [
			'a' => null,
			'b' => null,
			'c' => new Obj(['id' => 'c', 'type' => 'static'])
		];

		$this->assertSame('a', $collection->first()->id);
		$this->assertSame('hydrated', $collection->first()->type);

		$this->assertSame(['a'], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);
	}

	public function testKeyOf(): void
	{
		$objClass = new class () {
			public string $id;

			public function id(): string
			{
				return $this->id;
			}

			public function setId(string $id): static
			{
				$this->id = $id;
				return $this;
			}
		};
		$a = (clone $objClass)->setId('a');
		$b = (clone $objClass)->setId('b');

		$collection = new MockLazyCollection();
		$collection->data = [
			'a' => $a,
			'b' => null,
			'c' => null
		];

		$this->assertSame('a', $collection->keyOf($a));
		$this->assertSame('b', $collection->keyOf($b));

		// objects with ID can be looked up directly
		$this->assertSame([], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);

		$collection->data = [
			$obj = new Obj(['type' => 'no-id'])
		];

		$this->assertSame(0, $collection->keyOf($obj));

		$this->assertSame([], $collection->hydratedElements);
		$this->assertTrue($collection->hydrated);
	}

	public function testLast(): void
	{
		$collection = new MockLazyCollection();

		$this->assertNull($collection->last());

		$collection->data = [
			'a' => null,
			'b' => null,
			'c' => new Obj(['id' => 'c', 'type' => 'static'])
		];

		$this->assertSame('c', $collection->last()->id);
		$this->assertSame('static', $collection->last()->type);

		$this->assertSame([], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);

		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$this->assertSame('c', $collection->last()->id);
		$this->assertSame('hydrated', $collection->last()->type);

		$this->assertSame(['c'], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);
	}

	public function testMap(): void
	{
		$collection = new MockLazyCollection();
		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$result1 = $collection->map(function ($obj) {
			$obj->mapped = $obj->id . ' mapped';
			return $obj;
		});
		$this->assertSame(['a mapped', 'b mapped', 'c mapped'], $result1->pluck('mapped'));

		$this->assertSame(['b', 'c'], $collection->hydratedElements);
		$this->assertTrue($collection->hydrated);

		// another operation should still work but no longer needs to hydrate
		$result2 = $collection->map(function ($obj) {
			$obj->mapped = $obj->id . ' mapped again';
			return $obj;
		});
		$this->assertSame(['a mapped again', 'b mapped again', 'c mapped again'], $result2->pluck('mapped'));
	}

	public function testNth(): void
	{
		$collection = new MockLazyCollection();
		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$this->assertSame('a', $collection->nth(0)->id);
		$this->assertSame('static', $collection->nth(0)->type);
		$this->assertSame('b', $collection->nth(1)->id);
		$this->assertSame('hydrated', $collection->nth(1)->type);
		$this->assertNull($collection->nth(3));

		$this->assertSame(['b'], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);
	}

	public function testSort(): void
	{
		$collection = new MockLazyCollection();
		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'c' => null,
			'b' => null
		];

		$result1 = $collection->sort('id');
		$this->assertSame(['a', 'b', 'c'], $result1->pluck('id'));

		$this->assertSame(['c', 'b'], $collection->hydratedElements);
		$this->assertTrue($collection->hydrated);

		// another operation should still work but no longer needs to hydrate
		$result2 = $collection->sort('id', 'desc');
		$this->assertSame(['c', 'b', 'a'], $result2->pluck('id'));
	}

	public function testToArray(): void
	{
		$collection = new MockLazyCollection();
		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$result = $collection->toArray();
		$this->assertSame([
			'a' => ['id' => 'a', 'type' => 'static'],
			'b' => ['id' => 'b', 'type' => 'hydrated'],
			'c' => ['id' => 'c', 'type' => 'hydrated'],
		], $result);

		$this->assertSame(['b', 'c'], $collection->hydratedElements);
		$this->assertTrue($collection->hydrated);

		// another operation should still work but no longer needs to hydrate
		$result = $collection->toArray(fn ($obj) => $obj->type);
		$this->assertSame([
			'a' => 'static',
			'b' => 'hydrated',
			'c' => 'hydrated',
		], $result);
	}

	public function testValues(): void
	{
		$collection = new MockLazyCollection();
		$collection->data = [
			'a' => $a = new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$result = $collection->values();
		$this->assertSame(3, count($result));
		$this->assertSame($a, $result[0]);

		$this->assertSame(['b', 'c'], $collection->hydratedElements);
		$this->assertTrue($collection->hydrated);

		// another operation should still work but no longer needs to hydrate
		$result = $collection->values(fn ($obj) => $obj->type);
		$this->assertSame([
			'static',
			'hydrated',
			'hydrated',
		], $result);
	}
}
