<?php

namespace Kirby\Http\Request;

use Kirby\TestCase;

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
