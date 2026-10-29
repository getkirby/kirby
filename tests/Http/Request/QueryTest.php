<?php

namespace Kirby\Http\Request;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Query::class)]
class QueryTest extends TestCase
{
	public function testData(): void
	{
		// default
		$query = new Query();
		$this->assertSame([], $query->data());

		// custom array
		$data  = ['foo' => 'bar'];
		$query = new Query($data);
		$this->assertSame($data, $query->data());

		// custom string
		$string = 'foo=bar&kirby[]=bastian&kirby[]=allgeier';
		$data  = ['foo' => 'bar', 'kirby' => ['bastian', 'allgeier']];
		$query = new Query($string);
		$this->assertSame($data, $query->data());
	}

	public function testIsEmpty(): void
	{
		// without data
		$query = new Query();
		$this->assertTrue($query->isEmpty());
		$this->assertFalse($query->isNotEmpty());

		// with data
		$query = new Query(['foo' => 'bar']);
		$this->assertFalse($query->isEmpty());
		$this->assertTrue($query->isNotEmpty());
	}

	public function testGet(): void
	{
		// default
		$query = new Query();
		$this->assertNull($query->get('foo'));

		// single get
		$query = new Query(['foo' => 'bar']);
		$this->assertSame('bar', $query->get('foo'));

		// multiple gets
		$query = new Query(['a' => 'a', 'b' => 'b']);
		$this->assertSame(['a' => 'a', 'b' => 'b', 'c' => null], $query->get(['a', 'b', 'c']));
	}

	public static function intProvider(): array
	{
		return [
			[2, 2],
			['2', 2],
			['-1', -1],
			['+3', 3],
			['0', 0],
			[' 2 ', 2],
			['05', null],
			['1.5', null],
			['1e1', null],
			['abc', null],
			['2abc', null],
			['', null],
			['99999999999999999999', null],
			[1.0, null],
			[true, null],
			[null, null],
			[['2'], null],
		];
	}

	#[DataProvider('intProvider')]
	public function testInt(mixed $value, int|null $expected): void
	{
		$query = new Query(['a' => $value]);
		$this->assertSame($expected, $query->int('a'));
		$this->assertSame($expected ?? 5, $query->int('a', 5));
	}

	public function testIntMissing(): void
	{
		$query = new Query();
		$this->assertNull($query->int('a'));
		$this->assertSame(5, $query->int('a', 5));
	}

	public function testString(): void
	{
		$query = new Query([
			'string' => 'foo',
			'empty'  => '',
			'number' => '2',
			'int'    => 2,
			'bool'   => true,
			'null'   => null,
			'array'  => ['foo'],
		]);

		$this->assertSame('foo', $query->string('string'));
		$this->assertSame('foo', $query->string('string', 'bar'));
		$this->assertSame('', $query->string('empty', 'bar'));
		$this->assertSame('2', $query->string('number'));
		$this->assertNull($query->string('int'));
		$this->assertSame('bar', $query->string('int', 'bar'));
		$this->assertNull($query->string('bool'));
		$this->assertSame('bar', $query->string('null', 'bar'));
		$this->assertNull($query->string('array'));
		$this->assertSame('bar', $query->string('array', 'bar'));
		$this->assertNull($query->string('missing'));
		$this->assertSame('bar', $query->string('missing', 'bar'));
	}

	public function testToString(): void
	{
		// default
		$query = new Query();
		$this->assertSame('', $query->toString());
		$this->assertSame('', $query->__toString());
		$this->assertEquals('', $query); // cannot use strict assertion (string conversion)

		// custom
		$query = new Query(['foo' => 'bar']);
		$this->assertSame('foo=bar', $query->toString());
		$this->assertSame('foo=bar', $query->__toString());
		$this->assertEquals('foo=bar', $query); // cannot use strict assertion (string conversion)
	}

	public function testToArrayAndDebuginfo(): void
	{
		$data  = ['a' => 'a'];
		$query = new Query($data);
		$this->assertSame($data, $query->toArray());
		$this->assertSame($data, $query->__debugInfo());
	}

	public function testToJson(): void
	{
		$data  = ['a' => 'a'];
		$query = new Query($data);
		$this->assertSame(json_encode($data), $query->toJson());
	}
}
