<?php

namespace Kirby\Query;

use Kirby\Cms\App;
use Kirby\Exception\BadMethodCallException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use stdClass;

class MyObj
{
	public string $homer = 'simpson';

	public function foo(int $count)
	{
		return $count . 'bar';
	}
}

class MyCallObj
{
	public function __call($name, $args)
	{
		return $args[0] . 'bar';
	}
}

class MyGetObj
{
	public function __get($name)
	{
		return 'simpson';
	}
}

/**
 * @coversDefaultClass \Kirby\Query\Segment
 */
class SegmentTest extends TestCase
{
	public static function scalarProvider(): array
	{
		return [
			['test', 'string'],
			[1, 'integer'],
			[1.1, 'float'],
			[true, 'boolean'],
			[false, 'boolean'],
			[null, 'null']
		];
	}

	/**
	 * @covers ::error
	 * @dataProvider scalarProvider
	 */
	public function testErrorWithScalars($scalar, $label)
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to method "foo" on ' . $label);

		Segment::error($scalar, 'foo', 'method');
	}

	/**
	 * @covers ::error
	 */
	public function testErrorWithObject()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to non-existing method "foo" on object');

		Segment::error(new stdClass(), 'foo', 'method');
	}

	/**
	 * @covers ::factory
	 * @covers ::__construct
	 */
	public function testFactory()
	{
		$segment = Segment::factory('foo');
		$this->assertSame('foo', $segment->method);
		$this->assertNull($segment->arguments);

		$segment = Segment::factory('foo(1, 2)');
		$this->assertSame('foo', $segment->method);
		$this->assertCount(2, $segment->arguments);

		$segment = Segment::factory('foo(1, bar(2))');
		$this->assertSame('foo', $segment->method);
		$this->assertCount(2, $segment->arguments);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveFirst()
	{
		// without parameters
		$segment = Segment::factory('foo');
		$this->assertSame('bar', $segment->resolve(null, ['foo' => 'bar']));

		// with parameters
		$segment = Segment::factory('foo(2, "bar")');
		$this->assertSame('2bar', $segment->resolve(null, ['foo' => fn (int $a, string $b) => $a . $b]));
	}

	/**
	 * @covers ::resolve
	 * @covers ::resolveObject
	 */
	public function testResolveFirstWithDataObject()
	{
		$obj      = new stdClass();
		$obj->foo = 'bar';
		$segment  = Segment::factory('foo');
		$this->assertSame('bar', $segment->resolve(null, $obj));
	}


	/**
	 * @covers ::resolve
	 * @covers ::resolveArray
	 */
	public function testResolveArray()
	{
		$segment = Segment::factory('foo', 1);
		$data    = ['foo' => $expected = [1, 2]];
		$this->assertSame($expected, $segment->resolve($data));
	}

	/**
	 * @covers ::resolve
	 * @covers ::resolveArray
	 */
	public function testResolveArrayClosure()
	{
		$segment = Segment::factory('foo', 0);
		$data    = ['foo' => fn () => 'bar'];
		$this->assertSame('bar', $segment->resolve(null, $data));
	}

	/**
	 * @covers ::resolve
	 * @covers ::resolveArray
	 */
	public function testResolveArrayInvalidKey()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to non-existing property "foo" on array');

		$segment = Segment::factory('foo');
		$segment->resolve(['bar' => 2]);
	}

	/**
	 * @covers ::resolve
	 * @covers ::resolveArray
	 */
	public function testResolveArrayArgOnNonClosure()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Cannot access array element "foo" with arguments');

		$segment = Segment::factory('foo(2)', 1);
		$segment->resolve(['foo' => 'bar']);
	}

	/**
	 * @covers ::resolve
	 * @covers ::resolveArray
	 */
	public function testResolveArrayFromGlobalEntry()
	{
		$segment = Segment::factory('kirby');
		$this->assertSame(App::instance(), $segment->resolve(null, []));
	}

	/**
	 * @covers ::resolve
	 * @covers ::resolveObject
	 */
	public function testResolveObject()
	{
		$obj     = new MyObj();
		$segment = Segment::factory('foo(2)', 1);
		$this->assertSame('2bar', $segment->resolve($obj));

		$obj     = new MyObj();
		$segment = Segment::factory('homer', 1);
		$this->assertSame('simpson', $segment->resolve($obj));

		$obj     = new MyCallObj();
		$segment = Segment::factory('foo(2)', 1);
		$this->assertSame('2bar', $segment->resolve($obj));

		$obj     = new MyGetObj();
		$segment = Segment::factory('homer', 1);
		$this->assertSame('simpson', $segment->resolve($obj));
	}

	/**
	 * @covers ::resolve
	 * @covers ::resolveObject
	 */
	public function testResolveObjectInvalid()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to method/property "foo" on string');

		$segment = Segment::factory('foo', 1);
		$segment->resolve('bar');
	}

	/**
	 * @covers ::resolve
	 * @covers ::resolveObject
	 */
	public function testResolveObjectInvalidMethod()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to non-existing method/property "notfound" on object');

		$obj     = new MyObj();
		$segment = Segment::factory('notfound', 1);
		$segment->resolve($obj);
	}

	/**
	 * @covers ::resolve
	 * @covers ::resolveObject
	 */
	public function testResolveObjectMethodWithoutArgs()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to non-existing method "notfound" on object');

		$obj     = new MyObj();
		$segment = Segment::factory('notfound(2)', 1);
		$segment->resolve($obj);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithArrayNullValueError()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Access to method/property "method" on null');

		$segment = Segment::factory('method', 1);
		$segment->resolve(null);
	}
}
