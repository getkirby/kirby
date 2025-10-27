<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Content\Field;
use Kirby\Toolkit\Obj;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

class MockObject
{
	protected string|Field $id;
	protected string|null $group;

	public function __construct(array $props = [])
	{
		$this->id    = $props['id'];
		$this->group = $props['group'] ?? null;
	}

	public function id(): string|Field
	{
		return $this->id;
	}

	public function group(): string|null
	{
		return $this->group;
	}

	public function toArray(): array
	{
		return ['id' => $this->id];
	}

	public function uuid(): string|Field
	{
		return $this->id;
	}
}

class MockObjectWith__CallException
{
	public function __call($name, $arguments)
	{
		throw new Exception('Test exception');
	}
}

#[CoversClass(Collection::class)]
class CollectionTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.Collection';

	public function testCollectionMethods(): void
	{
		$kirby = $this->kirby([
			'collectionMethods' => [
				'test' => fn () => 'collection test'
			],
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->assertSame('collection test', (new Collection())->test());
		$this->assertSame('collection test', $kirby->site()->children()->test());

		Pages::$methods['test'] = fn () => 'pages test';

		$this->assertSame('collection test', (new Collection())->test());
		$this->assertSame('pages test', $kirby->site()->children()->test());

		Collection::$methods = [];
		Pages::$methods = [];
	}

	public function testWithValidObjects(): void
	{
		$collection = new Collection([
			$a = new MockObject(['id' => 'a', 'name' => 'a']),
			$b = new MockObject(['id' => 'b', 'name' => 'b']),
			$c = new MockObject(['id' => 'c', 'name' => 'c'])
		]);

		$this->assertSame($a, $collection->first());
		$this->assertSame($c, $collection->last());
	}

	public function testWithArray(): void
	{
		$collection = new Collection([
			$a = ['id' => 'a', 'name' => 'a'],
			$b = ['id' => 'b', 'name' => 'b'],
			$c = ['id' => 'c', 'name' => 'c']
		]);

		$this->assertSame($a, $collection->first());
		$this->assertSame($c, $collection->last());
	}

	public function testGetAttribute(): void
	{
		$object     = new MockObject(['id' => 'a']);
		$collection = new Collection();
		$value      = $collection->getAttribute($object, 'id');

		$this->assertSame('a', $value);
	}

	public function testGetAttributeWithField(): void
	{
		$object = new MockObject([
			'id' => $field = new Field(null, 'id', 'a')
		]);

		$collection = new Collection();
		$value      = $collection->getAttribute($object, 'id');

		$this->assertSame($field, $value);
	}

	public function testAppend(): void
	{
		$a = new MockObject(['id' => 'a', 'name' => 'A']);
		$b = new MockObject(['id' => 'b', 'name' => 'B']);
		$c = new Obj(['id' => 'c', 'name' => 'C']);
		$d = new stdClass();
		$d->id = 'd';
		$d->name = 'D';

		// with custom keys
		$collection = new Collection();
		$collection = $collection->append('key-a', $a);
		$collection = $collection->append('key-b', $b);
		$collection = $collection->append('key-c', $c);
		$collection = $collection->append('key-d', $d);
		$collection = $collection->append('key-e', 'a simple string');

		$this->assertSame(['key-a', 'key-b', 'key-c', 'key-d', 'key-e'], $collection->keys());
		$this->assertSame([$a, $b, $c, $d, 'a simple string'], $collection->values());

		// with automatic keys
		$collection = new Collection();
		$collection = $collection->append($a);
		$collection = $collection->append($b);
		$collection = $collection->append($c);
		$collection = $collection->append($d);
		$collection = $collection->append('a simple string');

		$this->assertSame(['a', 'b', 'c', 0, 1], $collection->keys());
		$this->assertSame([$a, $b, $c, $d, 'a simple string'], $collection->values());
	}

	public function testAppendWith__CallException(): void
	{
		$collection = new Collection();
		$obj        = new MockObjectWith__CallException();
		$collection = $collection->append($obj);
		$this->assertSame([0], $collection->keys());
	}

	public function testFindByUuid(): void
	{
		$collection = new Collection([
			$page = new Page([
				'slug' => 'test',
				'content' => [
					'uuid' => 'test'
				]
			])
		]);

		$result = $collection->findBy('uuid', 'page://test');
		$this->assertIsPage($page, $result);

		$result = $collection->findBy('uuid', $page->uuid());
		$this->assertIsPage($page, $result);

		$result = $collection->findBy('uuid', 'page://foo');
		$this->assertNull($result);
	}

	public function testGroup(): void
	{
		$collection = new Collection([
			new MockObject(['id' => 'a', 'group' => 'a']),
			new MockObject(['id' => 'b', 'group' => 'a']),
			new MockObject(['id' => 'c', 'group' => 'b']),
		]);

		$groups = $collection->group('group');

		$this->assertInstanceOf(Collection::class, $groups);
		$this->assertCount(2, $groups);

		$groupA = $groups->first();
		$groupB = $groups->last();

		$this->assertCount(2, $groupA);
		$this->assertCount(1, $groupB);
	}

	public function testGroupWithInvalidKey(): void
	{
		$collection = new Collection([
			new MockObject(['id' => 'a', 'group' => 'a']),
			new MockObject(['id' => 'b', 'group' => 'a']),
			new MockObject(['id' => 'c', 'group' => 'b']),
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Can only group by string values or by providing a callback function');

		$collection->group(1);
	}

	public function testGroupCaseSensitive(): void
	{
		$collection = new Collection([
			new Page(['slug' => 'a', 'content' => ['group' => 'a']]),
			new Page(['slug' => 'b', 'content' => ['group' => 'a']]),
			new Page(['slug' => 'c', 'content' => ['group' => 'A']]),
		]);

		$groups = $collection->group('group', true);
		$this->assertCount(1, $groups);

		$groups = $collection->group('group', false);
		$this->assertCount(2, $groups);

		$groupA = $groups->first();
		$groupB = $groups->last();

		$this->assertCount(2, $groupA);
		$this->assertCount(1, $groupB);
	}

	public function testGroupWithClosure(): void
	{
		$collection = new Collection([
			new MockObject(['id' => 'a', 'group' => 'a']),
			new MockObject(['id' => 'b', 'group' => 'a']),
			new MockObject(['id' => 'c', 'group' => 'b']),
		]);

		$groups = $collection->group(fn ($object) => $object->group());

		$this->assertInstanceOf(Collection::class, $groups);
		$this->assertCount(2, $groups);

		$groupA = $groups->first();
		$groupB = $groups->last();

		$this->assertCount(2, $groupA);
		$this->assertCount(1, $groupB);
	}

	public function testIndexOfWithObject(): void
	{
		$collection = new Collection([
			$a = new MockObject(['id' => 'a']),
			$b = new MockObject(['id' => 'b']),
			$c = new MockObject(['id' => 'c'])
		]);

		$d = new MockObject(['id' => 'd']);

		$this->assertSame(0, $collection->indexOf($a));
		$this->assertSame(1, $collection->indexOf($b));
		$this->assertSame(2, $collection->indexOf($c));
		$this->assertFalse($collection->indexOf($d));
	}

	public function testIndexOfWithString(): void
	{
		$collection = new Collection([
			new MockObject(['id' => 'a']),
			new MockObject(['id' => 'b']),
			new MockObject(['id' => 'c'])
		]);

		$this->assertSame(0, $collection->indexOf('a'));
		$this->assertSame(1, $collection->indexOf('b'));
		$this->assertSame(2, $collection->indexOf('c'));
		$this->assertFalse($collection->indexOf('d'));
	}

	public function testNotWithObjects(): void
	{
		$collection = new Collection([
			$a = new MockObject(['id' => 'a']),
			$b = new MockObject(['id' => 'b']),
			$c = new MockObject(['id' => 'c'])
		]);

		$result = $collection->not($a);

		$this->assertCount(2, $result);
		$this->assertSame($b, $result->first());
		$this->assertSame($c, $result->last());

		$result = $collection->not($a, $b);

		$this->assertCount(1, $result);
		$this->assertSame($c, $result->first());
		$this->assertSame($c, $result->last());
	}

	public function testNotWithCollection(): void
	{
		$collection = new Collection([
			new MockObject(['id' => 'a']),
			new MockObject(['id' => 'b']),
			new MockObject(['id' => 'c'])
		]);

		$not = $collection->find('a', 'c');

		$result = $collection->not($not);
		$this->assertCount(1, $result);
		$this->assertSame('b', $result->first()->id());
	}

	public function testNotWithSimpleArray(): void
	{
		$collection = new Collection([
			new MockObject(['id' => 'a']),
			new MockObject(['id' => 'b']),
			new MockObject(['id' => 'c'])
		]);

		$not = ['a', 'c', 'non-exists'];

		$result = $collection->not($not);
		$this->assertCount(1, $result);
		$this->assertSame('b', $result->first()->id());
	}

	public function testNotWithCollectionsArray(): void
	{
		$collection = new Collection([
			new MockObject(['id' => 'a']),
			new MockObject(['id' => 'b']),
			new MockObject(['id' => 'c'])
		]);

		$not = [
			new Collection([
				new MockObject(['id' => 'a']),
				new MockObject(['id' => 'non-exists'])
			]),
			new Collection([
				new MockObject(['id' => 'b'])
			])
		];

		$result = $collection->not($not);
		$this->assertCount(1, $result);
		$this->assertSame('c', $result->first()->id());
	}

	public function testNotWithObjectsArray(): void
	{
		$collection = new Collection([
			new MockObject(['id' => 'a']),
			new MockObject(['id' => 'b']),
			new MockObject(['id' => 'c'])
		]);

		$not = [
			new MockObject(['id' => 'b']),
			new MockObject(['id' => 'c']),
			new MockObject(['id' => 'non-exists']),
		];

		$result = $collection->not($not);
		$this->assertCount(1, $result);
		$this->assertSame('a', $result->first()->id());
	}

	public function testNotWithString(): void
	{
		$collection = new Collection([
			$a = new MockObject(['id' => 'a']),
			$b = new MockObject(['id' => 'b']),
			$c = new MockObject(['id' => 'c'])
		]);

		$result = $collection->not('a');

		$this->assertCount(2, $result);
		$this->assertSame($b, $result->first());
		$this->assertSame($c, $result->last());

		$result = $collection->not('a', 'b');

		$this->assertCount(1, $result);
		$this->assertSame($c, $result->first());
		$this->assertSame($c, $result->last());
	}

	public function testPaginate(): void
	{
		$collection = new Collection([
			$a = new MockObject(['id' => 'a']),
			$b = new MockObject(['id' => 'b']),
			$c = new MockObject(['id' => 'c'])
		]);

		// page: 1
		$result = $collection->paginate(1);

		$this->assertCount(1, $result);
		$this->assertSame($a, $result->first());
		$this->assertSame($a, $result->last());

		// page: 2
		$result = $collection->paginate(1, 2);

		$this->assertCount(1, $result);
		$this->assertSame($b, $result->first());
		$this->assertSame($b, $result->last());

		// page: 3
		$result = $collection->paginate(1, 3);

		$this->assertCount(1, $result);
		$this->assertSame($c, $result->first());
		$this->assertSame($c, $result->last());
	}

	public function testPrepend(): void
	{
		$a = new MockObject(['id' => 'a', 'name' => 'A']);
		$b = new MockObject(['id' => 'b', 'name' => 'B']);
		$c = new Obj(['id' => 'c', 'name' => 'C']);
		$d = new stdClass();
		$d->id = 'd';
		$d->name = 'D';

		// with custom keys
		$collection = new Collection();
		$collection = $collection->prepend('key-a', $a);
		$collection = $collection->prepend('key-b', $b);
		$collection = $collection->prepend('key-c', $c);
		$collection = $collection->prepend('key-d', $d);
		$collection = $collection->prepend('key-e', 'a simple string');

		$this->assertSame(['key-e', 'key-d', 'key-c', 'key-b', 'key-a'], $collection->keys());
		$this->assertSame(['a simple string', $d, $c, $b, $a], $collection->values());

		// with automatic keys
		$collection = new Collection();
		$collection = $collection->prepend($a);
		$collection = $collection->prepend($b);
		$collection = $collection->prepend($c);
		$collection = $collection->prepend($d);
		$collection = $collection->prepend('a simple string');

		$this->assertSame([0, 1, 'c', 'b', 'a'], $collection->keys());
		$this->assertSame(['a simple string', $d, $c, $b, $a], $collection->values());
	}

	public function testQuerySearch(): void
	{
		$collection = new Collection([
			new Page(['slug' => 'project-a']),
			new Page(['slug' => 'project-b']),
			new Page(['slug' => 'project-c'])
		]);

		// simple
		$result = $collection->query([
			'search' => 'project-b'
		]);

		$this->assertCount(1, $result);
		$this->assertSame('project-b', $result->first()->id());

		// with options array
		$result = $collection->query([
			'search' => [
				'query' => 'project-b'
			]
		]);

		$this->assertCount(1, $result);
		$this->assertSame('project-b', $result->first()->id());
	}

	public function testQueryPagination(): void
	{
		$collection = new Collection([
			new Page(['slug' => 'project-a']),
			new Page(['slug' => 'project-b']),
			new Page(['slug' => 'project-c'])
		]);

		$result = $collection->query([
			'paginate' => 1
		]);

		$this->assertCount(1, $result);
		$this->assertSame('project-a', $result->first()->id());
		$this->assertSame(3, $result->pagination()->pages());
	}

	public function testToArray(): void
	{
		$collection = new Collection([
			new MockObject(['id' => 'a']),
			new MockObject(['id' => 'b']),
			new MockObject(['id' => 'c'])
		]);

		$array = $collection->toArray();

		$this->assertSame($array, [
			'a' => [
				'id' => 'a'
			],
			'b' => [
				'id' => 'b'
			],
			'c' => [
				'id' => 'c'
			]
		]);
	}

	public function testToArrayWithCallback(): void
	{
		$collection = new Collection([
			new MockObject(['id' => 'a']),
			new MockObject(['id' => 'b']),
			new MockObject(['id' => 'c'])
		]);

		$array = $collection->toArray(fn ($object) => $object->id());

		$this->assertSame($array, [
			'a' => 'a',
			'b' => 'b',
			'c' => 'c'
		]);
	}

	public function testUpdate(): void
	{
		$collection = new Collection([
			new MockObject(['id' => 'a', 'group' => 'a']),
			new MockObject(['id' => 'b', 'group' => 'a'])
		]);

		$this->assertSame('a', $collection->first()->group());
		$this->assertSame('a', $collection->last()->group());

		$new = new MockObject(['id' => 'b', 'group' => 'b']);
		$collection->update($new);

		$this->assertSame('a', $collection->first()->group());
		$this->assertSame('b', $collection->last()->group());
	}
}
