<?php

namespace Kirby\Toolkit;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

class StringObject
{
	public function __construct(
		protected string $value
	) {
	}

	public function __toString(): string
	{
		return $this->value;
	}
}

#[CoversClass(Collection::class)]
class CollectionTest extends TestCase
{
	protected Collection $collection;
	protected array $sampleData;

	public function setUp(): void
	{
		$this->sampleData = [
			'first'  => 'My first element',
			'second' => 'My second element',
			'third'  => 'My third element',
		];

		$this->collection = new Collection($this->sampleData);
	}

	protected function assertIsUntouched(): void
	{
		// the original collection must to be untouched
		$this->assertSame($this->sampleData, $this->collection->toArray());
	}

	public function test__debuginfo()
	{
		$collection = new Collection(['a' => 'A', 'b' => 'B']);
		$this->assertSame(['a', 'b'], $collection->__debugInfo());
	}

	public function test__toString()
	{
		$collection = new Collection(['a' => 'A', 'b' => 'B']);
		$this->assertSame('a<br />b', $collection->__toString());
		$this->assertSame('a<br />b', (string)$collection);
	}

	public function testAppend()
	{
		// simple
		$collection = new Collection();
		$collection = $collection->append('a');
		$collection = $collection->append('b');
		$collection = $collection->append('c');

		$this->assertSame([0, 1, 2], $collection->keys());
		$this->assertSame(['a', 'b', 'c'], $collection->values());

		// with key
		$collection = new Collection();
		$collection = $collection->append('a', 'A');
		$collection = $collection->append('b', 'B');
		$collection = $collection->append('c', 'C');

		$this->assertSame(['a', 'b', 'c'], $collection->keys());
		$this->assertSame(['A', 'B', 'C'], $collection->values());

		// with too many params
		$collection = new Collection();
		$collection = $collection->append('a', 'A', 'ignore this');
		$collection = $collection->append('b', 'B', 'ignore this');
		$collection = $collection->append('c', 'C', 'ignore this');

		$this->assertSame(['a', 'b', 'c'], $collection->keys());
		$this->assertSame(['A', 'B', 'C'], $collection->values());
	}

	public function testCaseSensitive()
	{
		$normalCollection = new Collection([
			'lowercase' => 'test1',
			'UPPERCASE' => 'test2',
			'MiXeD'     => 'test3'
		]);
		$normalCollection->set('AnOtHeR', 'test4');
		$normalCollection->remove('upperCase');

		$this->assertSame([
			'lowercase' => 'test1',
			'mixed'     => 'test3',
			'another'   => 'test4'
		], $normalCollection->data());
		$this->assertSame('test1', $normalCollection->get('lowercase'));
		$this->assertSame('test3', $normalCollection->get('MiXeD'));
		$this->assertSame('test4', $normalCollection->get('AnOtHeR'));
		$this->assertSame('test1', $normalCollection->get('LowerCase'));
		$this->assertSame('test3', $normalCollection->get('mIxEd'));
		$this->assertSame('test4', $normalCollection->get('another'));

		$sensitiveCollection = new Collection([
			'lowercase' => 'test1',
			'UPPERCASE' => 'test2',
			'MiXeD'     => 'test3'
		], true);
		$sensitiveCollection->set('AnOtHeR', 'test4');
		$sensitiveCollection->remove('upperCase');
		$sensitiveCollection->remove('MiXeD');

		$this->assertSame([
			'lowercase' => 'test1',
			'UPPERCASE' => 'test2',
			'AnOtHeR'   => 'test4'
		], $sensitiveCollection->data());
		$this->assertSame('test1', $sensitiveCollection->get('lowercase'));
		$this->assertSame('test2', $sensitiveCollection->get('UPPERCASE'));
		$this->assertSame('test4', $sensitiveCollection->get('AnOtHeR'));
		$this->assertNull($sensitiveCollection->get('Lowercase'));
		$this->assertNull($sensitiveCollection->get('uppercase'));
		$this->assertNull($sensitiveCollection->get('another'));
	}

	public function testCount()
	{
		$this->assertSame(3, $this->collection->count());
		$this->assertCount(3, $this->collection);
	}

	public function testData()
	{
		$collection = new Collection($data = ['a' => 'A', 'b' => 'B']);
		$this->assertSame($data, $collection->data());

		$collection->data($data = ['c' => 'C', 'd' => 'D']);
		$this->assertSame($data, $collection->data());
	}

	public function testEmpty()
	{
		$collection = new Collection($data = ['a' => 'A', 'b' => 'B']);
		$this->assertSame($data, $collection->data());

		$collection = $collection->empty();
		$this->assertSame([], $collection->data());
	}

	public function testFilter()
	{
		$filtered = $this->collection->filter(
			fn ($element) => $element === 'My second element'
		);

		$this->assertSame('My second element', $filtered->first());
		$this->assertSame('My second element', $filtered->last());
		$this->assertCount(1, $filtered);
		$this->assertIsUntouched();
	}

	public function testFirst()
	{
		$this->assertSame('My first element', $this->collection->first());
	}

	public function testFlip()
	{
		$this->assertSame(array_reverse($this->sampleData, true), $this->collection->flip()->toArray());
		$this->assertSame($this->sampleData, $this->collection->flip()->flip()->toArray());
		$this->assertIsUntouched();
	}

	public function testGetAttributeFromArray()
	{
		$collection = new Collection([
			'a' => [
				'username' => 'Homer',
				'tags' => 'simpson, male'
			],
			'b' => [
				'username' => 'Marge',
				'tags' => 'simpson, female'
			],
		]);

		$this->assertSame('Homer', $collection->getAttribute($collection->first(), 'username'));
		$this->assertSame('Marge', $collection->getAttribute($collection->last(), 'username'));

		// split
		$this->assertSame(['simpson', 'male'], $collection->getAttribute($collection->first(), 'tags', true));
		$this->assertSame(['simpson', 'female'], $collection->getAttribute($collection->last(), 'tags', true));
	}

	public function testGetAttributeFromObject()
	{
		$collection = new Collection([
			'a' => new Obj([
				'username' => 'Homer'
			]),
			'b' => new Obj([
				'username' => 'Marge'
			]),
		]);

		$this->assertSame('Homer', $collection->getAttribute($collection->first(), 'username'));
		$this->assertSame('Marge', $collection->getAttribute($collection->last(), 'username'));
	}

	public function testGetters()
	{
		$this->assertSame('My first element', $this->collection->first);
		$this->assertSame('My second element', $this->collection->second);
		$this->assertSame('My third element', $this->collection->third);

		$this->assertSame('My first element', $this->collection->first());
		$this->assertSame('My second element', $this->collection->second());
		$this->assertSame('My third element', $this->collection->third());

		$this->assertSame('My first element', $this->collection->get('first'));
		$this->assertSame('My second element', $this->collection->get('second'));
		$this->assertSame('My third element', $this->collection->get('third'));

		$this->assertNull($this->collection->get('fourth'));
	}

	public function testGettersCaseSensitive()
	{
		$collection = new Collection($this->sampleData, true);

		$this->assertSame('My first element', $collection->get('first'));
		$this->assertNull($collection->get('FIRst'));
	}

	public function testGroup()
	{
		$collection = new Collection();

		$collection->user1 = [
			'username' => 'peter',
			'group'    => 'admin'
		];

		$collection->user2 = [
			'username' => 'paul',
			'group'    => 'admin'
		];

		$collection->user3 = [
			'username' => 'mary',
			'group'    => 'client'
		];

		$groups = $collection->group(fn ($item) => $item['group']);
		$this->assertCount(2, $groups->admin());
		$this->assertCount(1, $groups->client());

		$firstAdmin = $groups->admin()->first();
		$this->assertSame('peter', $firstAdmin['username']);

		// alias
		$groups = $collection->groupBy(fn ($item) => $item['group']);
		$this->assertCount(2, $groups->admin());
		$this->assertCount(1, $groups->client());
	}

	public function testGroupWithInvalidKey()
	{
		$collection = new Collection(['a' => 'A']);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid grouping value for key: a');

		$collection->group(fn ($item) => false);
	}

	public function testGroupArray()
	{
		$collection = new Collection(['a' => 'A']);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('You cannot group by arrays or objects');

		$collection->group(fn ($item) => ['a' => 'b']);
	}

	public function testGroupObject()
	{
		$collection = new Collection(['a' => 'A']);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('You cannot group by arrays or objects');

		$collection->group(fn ($item) => new Obj(['a' => 'b']));
	}

	public function testGroupStringObject()
	{
		$collection = new Collection();

		$collection->user1 = [
			'username' => 'peter',
			'group'    => new StringObject('admin')
		];

		$collection->user2 = [
			'username' => 'paul',
			'group'    => new StringObject('admin')
		];

		$collection->user3 = [
			'username' => 'mary',
			'group'    => new StringObject('client')
		];

		$groups = $collection->group(fn ($item) => $item['group']);
		$this->assertCount(2, $groups->admin());
		$this->assertCount(1, $groups->client());

		$firstAdmin = $groups->admin()->first();
		$this->assertSame('peter', $firstAdmin['username']);
	}

	public function testGroupBy()
	{
		$collection = new Collection();

		$collection->user1 = [
			'username' => 'peter',
			'group'    => 'admin'
		];

		$collection->user2 = [
			'username' => 'paul',
			'group'    => 'admin'
		];

		$collection->user3 = [
			'username' => 'mary',
			'group'    => 'client'
		];

		$groups = $collection->group('group');

		$this->assertCount(2, $groups->admin());
		$this->assertCount(1, $groups->client());

		$firstAdmin = $groups->admin()->first();
		$this->assertSame('peter', $firstAdmin['username']);
	}

	public function testGroupByWithInvalidKey()
	{
		$collection = new Collection(['a' => 'A']);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Can only group by string values or by providing a callback function');

		$collection->group(1);
	}

	public function testIndexOf()
	{
		$this->assertSame(1, $this->collection->indexOf('My second element'));
	}

	public function testIntersection()
	{
		$collection1 = new Collection([
			'a' => $a = new StringObject('a'),
			'b' => $b = new StringObject('b'),
			'c' => $c = new StringObject('c')
		]);

		$collection2 = new Collection([
			'c' => $c,
			'd' => $d = new StringObject('d'),
			'b' => $b
		]);

		$collection3 = new Collection([
			'd' => $d,
			'e' => $e = new StringObject('e')
		]);

		// 1 with 2
		$result = $collection1->intersection($collection2);

		$this->assertCount(2, $result);
		$this->assertSame($b, $result->first());
		$this->assertSame($c, $result->last());

		// 2 with 1
		$result = $collection2->intersection($collection1);

		$this->assertCount(2, $result);
		$this->assertSame($c, $result->first());
		$this->assertSame($b, $result->last());

		// 1 with 3
		$result = $collection1->intersection($collection3);

		$this->assertCount(0, $result);

		// 3 with 2
		$result = $collection3->intersection($collection2);

		$this->assertCount(1, $result);
		$this->assertSame($d, $result->first());
	}

	public function testIntersects()
	{
		$collection1 = new Collection([
			'a' => $a = new StringObject('a'),
			'b' => $b = new StringObject('b'),
			'c' => $c = new StringObject('c')
		]);

		$collection2 = new Collection([
			'c' => $c,
			'd' => $d = new StringObject('d'),
			'b' => $b
		]);

		$collection3 = new Collection([
			'd' => $d,
			'e' => $e = new StringObject('e')
		]);

		// 1 with 2
		$this->assertTrue($collection1->intersects($collection2));

		// 2 with 1
		$this->assertTrue($collection2->intersects($collection1));

		// 1 with 3
		$this->assertFalse($collection1->intersects($collection3));

		// 3 with 2
		$this->assertTrue($collection3->intersects($collection2));
	}

	public function testIsEmpty()
	{
		$collection = new Collection([
			['name'  => 'Bastian'],
			['name' => 'Nico']
		]);

		$this->assertTrue($collection->isNotEmpty());
		$this->assertFalse($collection->isEmpty());
	}

	public function testIsEven()
	{
		$collection = new Collection(['a' => 'a']);
		$this->assertFalse($collection->isEven());

		$collection = new Collection(['a' => 'a', 'b' => 'b']);
		$this->assertTrue($collection->isEven());
	}

	public function testIsNotEmpty()
	{
		$collection = new Collection([]);

		$this->assertTrue($collection->isEmpty());
		$this->assertFalse($collection->isNotEmpty());
	}

	public function testIsOdd()
	{
		$collection = new Collection(['a' => 'a']);
		$this->assertTrue($collection->isOdd());

		$collection = new Collection(['a' => 'a', 'b' => 'b']);
		$this->assertFalse($collection->isOdd());
	}

	public function testIsset()
	{
		$this->assertTrue(isset($this->collection->first));
		$this->assertFalse(isset($this->collection->super));
	}

	public function testKeyOf()
	{
		$this->assertSame('second', $this->collection->keyOf('My second element'));
	}

	public function testKeys()
	{
		$this->assertSame(['first', 'second', 'third'], $this->collection->keys());
	}

	public function testLast()
	{
		$this->assertSame('My third element', $this->collection->last());
	}

	public function testMap()
	{
		$collection = new Collection(['a' => 1, 'b' => 2]);
		$collection->map(fn ($item) => $item * 2);
		$this->assertSame(['a' => 2, 'b' => 4], $collection->data());
	}

	public function testNextAndPrev()
	{
		$this->assertSame('My second element', $this->collection->next());
		$this->assertSame('My third element', $this->collection->next());
		$this->assertSame('My second element', $this->collection->prev());
	}

	public function testNotAndWithout()
	{
		// remove elements
		$this->assertSame('My second element', $this->collection->not('first')->first());
		$this->assertCount(1, $this->collection->not('second')->not('third'));
		$this->assertCount(0, $this->collection->not('first', 'second', 'third'));

		// also check the alternative
		$this->assertSame('My second element', $this->collection->without('first')->first());
		$this->assertCount(1, $this->collection->without('second')->not('third'));
		$this->assertCount(0, $this->collection->without('first', 'second', 'third'));

		$this->assertIsUntouched();
	}

	public function testNth()
	{
		$this->assertSame('My first element', $this->collection->nth(0));
		$this->assertSame('My second element', $this->collection->nth(1));
		$this->assertSame('My third element', $this->collection->nth(2));
		$this->assertNull($this->collection->nth(3));
	}

	public function testOffsetAndLimit()
	{
		$this->assertSame(array_slice($this->sampleData, 1), $this->collection->offset(1)->toArray());
		$this->assertSame(array_slice($this->sampleData, 0, 1), $this->collection->limit(1)->toArray());
		$this->assertSame(array_slice($this->sampleData, 1, 1), $this->collection->offset(1)->limit(1)->toArray());
		$this->assertIsUntouched();
	}

	public function testPrepend()
	{
		// simple
		$collection = new Collection(['b', 'c']);
		$collection = $collection->prepend('a');

		$this->assertSame([0, 1, 2], $collection->keys());
		$this->assertSame(['a', 'b', 'c'], $collection->values());

		// with key
		$collection = new Collection(['b' => 'B', 'c' => 'C']);
		$collection = $collection->prepend('a', 'A');

		$this->assertSame(['a', 'b', 'c'], $collection->keys());
		$this->assertSame(['A', 'B', 'C'], $collection->values());

		// with too many params
		$collection = new Collection(['b' => 'B', 'c' => 'C']);
		$collection = $collection->prepend('a', 'A', 'ignore this');

		$this->assertSame(['a', 'b', 'c'], $collection->keys());
		$this->assertSame(['A', 'B', 'C'], $collection->values());
	}

	public function testQuery()
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei',
			'four'  => 'vier'
		]);

		$this->assertSame([
			'two'   => 'zwei',
			'four'  => 'vier'
		], $collection->query([
			'not'    => ['three'],
			'offset' => 1,
			'limit'  => 2
		])->toArray());
	}

	public function testQueryPaginate()
	{
		$collection = new Collection([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei',
			'four'  => 'vier'
		]);

		$this->assertSame([
			'three' => 'drei',
			'four'  => 'vier'
		], $collection->query([
			'paginate' => [
				'limit' => 2,
				'page'  => 2
			]
		])->toArray());
	}

	public function testQueryFilter()
	{
		$collection = new Collection([
			[
				'name'  => 'Bastian',
				'role'  => 'founder'
			],
			[
				'name' => 'Nico',
				'role' => 'developer'
			]
		]);

		$this->assertSame([
			[
				'name'  => 'Bastian',
				'role'  => 'founder'
			]
		], $collection->query([
			'filter' => [
				[
					'field'    => 'name',
					'operator' => '*=',
					'value'    => 'Bast'
				]
			]
		])->toArray());
	}

	public function testQuerySortBy()
	{
		$collection = new Collection([
			[
				'name'  => 'Bastian',
				'role'  => 'founder'
			],
			[
				'name' => 'Nico',
				'role' => 'developer'
			]
		]);

		$this->assertSame('Nico', $collection->query([
			'sortBy' => 'name desc'
		])->first()['name']);
		$this->assertSame('Bastian', $collection->query([
			'sortBy' => ['name', 'asc']
		])->first()['name']);
	}

	public function testQuerySortByComma()
	{
		$collection = new Collection([
			'one'   => ['key' => 'erei', 'value' => 'arsz'],
			'two'   => ['key' => 'zwei', 'value' => 'fors'],
			'three' => ['key' => 'erei', 'value' => 'beck'],
			'four'  => ['key' => 'vier', 'value' => 'tars']
		]);

		$results = $collection->query(['sortBy' => 'key asc, value desc'])->toArray();

		$this->assertSame([
			'three',
			'one',
			'four',
			'two',
		], array_keys($results));
	}

	public function testRandom()
	{
		$collection = new Collection([
			'one' => 'eins',
			'two' => 'zwei',
			'three' => 'drei',
			'four' => 'vier'
		]);
		$collectionKeys = array_flip($collection->keys());
		$collectionValues = array_flip($collection->values());

		// Assert existence and correctness of keys
		$random1 = $collection->random();
		$this->assertSame($collection->findByKey($random1->keys()[0]), $random1->first());

		// Assert order of keys in non-shuffled random
		$random2 = $collection->random(2);
		$this->assertTrue($collectionKeys[$random2->keys()[0]] < $collectionKeys[$random2->keys()[1]]);

		$random3 = $collection->random(3, true);
		foreach ($random3 as $key => $value) {
			$this->assertContains($key, $collection->keys());
			$this->assertContains($value, $collection->values());
			$this->assertSame($collectionKeys[$key], $collectionValues[$value]);
		}
	}

	public function testRemoveMultiple()
	{
		$collection = new Collection();

		$collection->set('a', 'A');
		$collection->set('b', 'B');
		$collection->set('c', 'C');

		$this->assertCount(3, $collection);

		foreach ($collection as $key => $item) {
			$collection->__unset($key);
		}

		$this->assertCount(0, $collection);
	}

	public function testSetters()
	{
		$this->collection->fourth = 'My fourth element';
		$this->collection->fifth  = 'My fifth element';

		$this->assertSame('My fourth element', $this->collection->fourth);
		$this->assertSame('My fifth element', $this->collection->fifth);

		$this->assertSame('My fourth element', $this->collection->fourth());
		$this->assertSame('My fifth element', $this->collection->fifth());

		$this->assertSame('My fourth element', $this->collection->get('fourth'));
		$this->assertSame('My fifth element', $this->collection->get('fifth'));
	}

	public function testShuffle()
	{
		$this->assertInstanceOf(Collection::class, $this->collection->shuffle());
		$this->assertIsUntouched();
	}

	public function testSlice()
	{
		$this->assertSame(array_slice($this->sampleData, 1), $this->collection->slice(1)->toArray());
		$this->assertCount(2, $this->collection->slice(1));
		$this->assertSame(array_slice($this->sampleData, 0, 1), $this->collection->slice(0, 1)->toArray());
		$this->assertCount(1, $this->collection->slice(0, 1));
		$this->assertIsUntouched();
	}

	public function testToArray()
	{
		// associative
		$collection = new Collection($input = ['a' => 'value A', 'b' => 'value B']);
		$this->assertSame($input, $collection->toArray());

		// non-associative
		$collection = new Collection($input = ['a', 'b', 'c']);
		$this->assertSame($input, $collection->toArray());

		// with mapping
		$collection = new Collection(['a' => 1, 'b' => 2]);
		$this->assertSame(['a' => 2, 'b' => 4], $collection->toArray(fn ($item) => $item * 2));
	}

	public function testToJson()
	{
		// associative
		$collection = new Collection(['a' => 'value A', 'b' => 'value B']);
		$this->assertSame('{"a":"value A","b":"value B"}', $collection->toJson());

		// non-associative
		$collection = new Collection(['a', 'b', 'c']);
		$this->assertSame('["a","b","c"]', $collection->toJson());
	}

	public function testToString()
	{
		// associative
		$collection = new Collection(['a' => 'value A', 'b' => 'value B']);
		$this->assertSame('a<br />b', $collection->toString());

		// non-associative
		$collection = new Collection(['a', 'b', 'c']);
		$this->assertSame('0<br />1<br />2', $collection->toString());
	}

	public function testValues()
	{
		$this->assertSame([
			'My first element',
			'My second element',
			'My third element'
		], $this->collection->values());
	}

	public function testValuesMap()
	{
		$values = $this->collection->values(
			fn ($item) => Str::after($item, 'My ')
		);

		$this->assertSame([
			'first element',
			'second element',
			'third element'
		], $values);
	}

	public function testWhen()
	{
		$collection = new Collection([
			[
				'name'  => 'Bastian',
				'color' => 'blue'
			],
			[
				'name' => 'Nico',
				'color' => 'green'
			],
			[
				'name' => 'Lukas',
				'color' => 'yellow'
			],
			[
				'name'  => 'Sonja',
				'color' => 'red'
			]
		]);

		$phpunit           = $this;
		$expectedCondition = null;

		$callback = function ($condition) use ($phpunit, &$expectedCondition) {
			$phpunit->assertSame($expectedCondition, $condition);
			return $this->sortBy('name', 'asc');
		};

		$fallback = function ($condition) use ($phpunit, &$expectedCondition) {
			$phpunit->assertSame($expectedCondition, $condition);
			return $this->sortBy('name', 'desc');
		};

		$sorted = $collection->when($expectedCondition = true, $callback);
		$this->assertSame('Bastian', $sorted->nth(0)['name']);
		$this->assertSame('Lukas', $sorted->nth(1)['name']);
		$this->assertSame('Nico', $sorted->nth(2)['name']);
		$this->assertSame('Sonja', $sorted->nth(3)['name']);

		$sorted = $collection->when($expectedCondition = 'this is truthy', $callback);
		$this->assertSame('Bastian', $sorted->nth(0)['name']);
		$this->assertSame('Lukas', $sorted->nth(1)['name']);
		$this->assertSame('Nico', $sorted->nth(2)['name']);
		$this->assertSame('Sonja', $sorted->nth(3)['name']);

		$sorted = $collection->when($expectedCondition = true, $callback, $fallback);
		$this->assertSame('Bastian', $sorted->nth(0)['name']);
		$this->assertSame('Lukas', $sorted->nth(1)['name']);
		$this->assertSame('Nico', $sorted->nth(2)['name']);
		$this->assertSame('Sonja', $sorted->nth(3)['name']);

		$sorted = $collection->when($expectedCondition = false, $callback);
		$this->assertSame('Bastian', $sorted->nth(0)['name']);
		$this->assertSame('Nico', $sorted->nth(1)['name']);
		$this->assertSame('Lukas', $sorted->nth(2)['name']);
		$this->assertSame('Sonja', $sorted->nth(3)['name']);

		$sorted = $collection->when($expectedCondition = false, $callback, $fallback);
		$this->assertSame('Sonja', $sorted->nth(0)['name']);
		$this->assertSame('Nico', $sorted->nth(1)['name']);
		$this->assertSame('Lukas', $sorted->nth(2)['name']);
		$this->assertSame('Bastian', $sorted->nth(3)['name']);

		$sorted = $collection->when($expectedCondition = null, $callback, $fallback);
		$this->assertSame('Sonja', $sorted->nth(0)['name']);
		$this->assertSame('Nico', $sorted->nth(1)['name']);
		$this->assertSame('Lukas', $sorted->nth(2)['name']);
		$this->assertSame('Bastian', $sorted->nth(3)['name']);
	}
}
