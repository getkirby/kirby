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

	protected function hydrateElement(string $key): object|null
	{
		// log for test assertions
		$this->hydratedElements[] = $key;

		return $this->data[$key] = new Obj(['id' => $key, 'type' => 'hydrated']);
	}
}

class MockLazyCollectionWithInitialization extends MockLazyCollection
{
	public bool $initialized = false;
	public array|null $targetData = null;

	protected function hydrateElement(string $key): object|null
	{
		if (
			is_array($this->targetData) &&
			array_key_exists($key, $this->targetData) === false
		) {
			// log for test assertions
			$this->hydratedElements[] = $key;

			return null;
		}

		return parent::hydrateElement($key);
	}

	public function initialize(): void
	{
		if ($this->initialized === true) {
			return;
		}

		if (is_array($this->targetData)) {
			$existing = $this->data;
			$this->data = $this->targetData;

			foreach ($existing as $id => $user) {
				$this->data[$id] = $user;
			}
		}

		$this->initialized = true;
	}
}

#[CoversClass(LazyCollection::class)]
class LazyCollectionTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.LazyCollection';

	public function testHydrate(): void
	{
		$collection = new MockLazyCollection();
		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$collection->hydrate();

		$this->assertSame([
			'a' => ['id' => 'a', 'type' => 'static'],
			'b' => ['id' => 'b', 'type' => 'hydrated'],
			'c' => ['id' => 'c', 'type' => 'hydrated']
		], array_map(fn ($element) => $element->toArray(), $collection->data));
		$this->assertSame(['b', 'c'], $collection->hydratedElements);
		$this->assertTrue($collection->hydrated);

		// another invocation should not hydrate again
		$collection->iterated = false;
		$collection->hydrate();
		$this->assertFalse($collection->iterated);
	}

	public function testInitialize(): void
	{
		$collection1 = new MockLazyCollection();
		$collection1->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		// should not throw an exception because there is no need to initialize
		$collection1->initialize();

		$collection2 = new MockLazyCollectionWithInitialization();
		$collection2->targetData = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$collection2->initialize();

		$this->assertSame($collection2->targetData, $collection2->data);
		$this->assertSame([], $collection2->hydratedElements);
		$this->assertFalse($collection2->hydrated);
		$this->assertTrue($collection2->initialized);

		// another invocation should not initialize again
		$collection2->iterated = false;
		$collection2->initialize();
		$this->assertFalse($collection2->iterated);
	}

	public function testEmpty(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null
		];

		$newCollection = $collection->empty();

		$this->assertSame([], $newCollection->toArray());
	}

	public function testEmptyUnitialized(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
			'a' => new Obj(['id' => 'a', 'type' => 'initialized']),
			'b' => null
		];

		$newCollection = $collection->empty();

		$this->assertSame([], $newCollection->toArray());
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

	public function testGetUnitialized(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
			'a' => new Obj(['id' => 'a', 'type' => 'initialized']),
			'b' => null,
			'c' => null
		];

		// getting a single element shouldn't have to initialize the
		// entire structure, but just hydrate the single requested value
		$this->assertSame('a', $collection->get('a')->id);
		$this->assertSame('hydrated', $collection->get('a')->type);

		$this->assertNull($collection->get('d'));

		$this->assertFalse($collection->initialized);
		$this->assertSame(['a', 'd'], $collection->hydratedElements);
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

	public function testIterateUnitialized(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
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
		$this->assertTrue($collection->initialized);
	}

	public function testUnset(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->data = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		unset($collection->a, $collection->b);

		$this->assertSame([], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);

		$this->assertSame([
			'c' => ['id' => 'c', 'type' => 'hydrated']
		], $collection->toArray());
	}

	public function testUnsetUnitialized(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
			'a' => new Obj(['id' => 'a', 'type' => 'initialized']),
			'b' => null,
			'c' => null
		];

		unset($collection->a, $collection->b);

		$this->assertSame([], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);
		$this->assertTrue($collection->initialized);

		$this->assertSame([
			'c' => ['id' => 'c', 'type' => 'hydrated']
		], $collection->toArray());
	}

	public function testChunk(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$result = $collection->chunk(2);

		$this->assertSame([], $result->hydratedElements);
		$this->assertFalse($result->hydrated);
		$this->assertTrue($result->initialized);

		$this->assertSame(2, $result->count());
		$this->assertSame(['a', 'b'], $result->first()->pluck('id'));
		$this->assertSame(['c'], $result->last()->pluck('id'));
	}

	public function testCount(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$this->assertSame(3, $collection->count());

		$this->assertSame([], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);
		$this->assertTrue($collection->initialized);
	}

	public function testFlip(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$result = $collection->flip();

		$this->assertSame([], $result->hydratedElements);
		$this->assertFalse($result->hydrated);
		$this->assertTrue($result->initialized);

		$this->assertSame(3, $result->count());
		$this->assertSame(['c', 'b', 'a'], $result->pluck('id'));
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

	public function testFirstUnitialized(): void
	{
		$collection = new MockLazyCollectionWithInitialization();

		$this->assertNull($collection->first());
		$this->assertTrue($collection->initialized);

		$collection->initialized = false;
		$collection->targetData = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$this->assertSame('a', $collection->first()->id);
		$this->assertSame('static', $collection->first()->type);

		$this->assertSame([], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);
		$this->assertTrue($collection->initialized);
	}

	public function testHas(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$this->assertTrue($collection->has('a'));
		$this->assertTrue($collection->has('b'));
		$this->assertFalse($collection->has('d'));

		$this->assertSame([], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);
		$this->assertTrue($collection->initialized);
	}

	public function testKeys(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$this->assertSame(['a', 'b', 'c'], $collection->keys());

		$this->assertSame([], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);
		$this->assertTrue($collection->initialized);
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

	public function testLastUnitialized(): void
	{
		$collection = new MockLazyCollectionWithInitialization();

		$this->assertNull($collection->last());
		$this->assertTrue($collection->initialized);

		$collection->initialized = false;
		$collection->targetData = [
			'a' => null,
			'b' => null,
			'c' => new Obj(['id' => 'c', 'type' => 'static'])
		];

		$this->assertSame('c', $collection->last()->id);
		$this->assertSame('static', $collection->last()->type);

		$this->assertSame([], $collection->hydratedElements);
		$this->assertFalse($collection->hydrated);
		$this->assertTrue($collection->initialized);
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

	public function testNthUnitialized(): void
	{
		$collection = new MockLazyCollectionWithInitialization();

		$this->assertNull($collection->nth(3));
		$this->assertTrue($collection->initialized);

		$collection->initialized = false;
		$collection->targetData = [
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
		$this->assertTrue($collection->initialized);
	}

	public function testPrepend(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->data = [
			'b' => new Obj(['id' => 'b', 'type' => 'static']),
			'c' => null
		];

		$collection->prepend(new Obj(['id' => 'a', 'type' => 'prepended']));

		$this->assertSame([
			'a' => ['id' => 'a', 'type' => 'prepended'],
			'b' => ['id' => 'b', 'type' => 'static'],
			'c' => ['id' => 'c', 'type' => 'hydrated']
		], $collection->toArray());
	}

	public function testPrependUnitialized(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
			'b' => new Obj(['id' => 'b', 'type' => 'initialized']),
			'c' => null
		];

		$collection->prepend(new Obj(['id' => 'a', 'type' => 'prepended']));

		$this->assertSame([
			'a' => ['id' => 'a', 'type' => 'prepended'],
			'b' => ['id' => 'b', 'type' => 'initialized'],
			'c' => ['id' => 'c', 'type' => 'hydrated']
		], $collection->toArray());
	}

	public function testRandom(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$result = $collection->random(2);

		$this->assertSame([], $result->hydratedElements);
		$this->assertFalse($result->hydrated);
		$this->assertTrue($result->initialized);

		$this->assertSame(2, $result->count());
		$this->assertInstanceOf(Obj::class, $result->first());
		$this->assertInstanceOf(Obj::class, $result->last());
	}

	public function testShuffle(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
			'a' => new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$result = $collection->shuffle();

		$this->assertSame([], $result->hydratedElements);
		$this->assertFalse($result->hydrated);
		$this->assertTrue($result->initialized);

		$this->assertSame(3, $result->count());
		$this->assertInstanceOf(Obj::class, $result->first());
		$this->assertInstanceOf(Obj::class, $result->last());
	}

	public function testSlice(): void
	{
		$collection = new MockLazyCollectionWithInitialization();
		$collection->targetData = [
			'a' => $a = new Obj(['id' => 'a', 'type' => 'static']),
			'b' => null,
			'c' => null
		];

		$result1 = $collection->slice(1, 1);

		$this->assertSame([], $result1->hydratedElements);
		$this->assertFalse($result1->hydrated);
		$this->assertTrue($result1->initialized);

		$this->assertSame(1, $result1->count());
		$this->assertSame(['b' => null], $result1->data);

		$result2 = $collection->slice(0, 2);

		$this->assertSame([], $result2->hydratedElements);
		$this->assertFalse($result2->hydrated);
		$this->assertTrue($result2->initialized);

		$this->assertSame(2, $result2->count());
		$this->assertSame(['a' => $a, 'b' => null], $result2->data);
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
