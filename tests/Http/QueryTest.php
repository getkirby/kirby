<?php

namespace Kirby\Http;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Query::class)]
class QueryTest extends TestCase
{
	public function testConstructWithArray(): void
	{
		$query = new Query([
			'a' => 'value-a',
			'b' => 'value-b'
		]);

		$this->assertSame('value-a', $query->a);
		$this->assertSame('value-b', $query->b);
	}

	public function testConstructWithString(): void
	{
		$query = new Query('?a=value-a&b=value-b');

		$this->assertSame('value-a', $query->a);
		$this->assertSame('value-b', $query->b);
	}

	public function testConstructWithEmptyValue(): void
	{
		$query = new Query('?a=&b=');

		$this->assertSame('', $query->a);
		$this->assertSame('', $query->b);
	}

	public function testIsEmpty(): void
	{
		$query = new Query('');
		$this->assertTrue($query->isEmpty());

		$query = new Query('?a=value-a');
		$this->assertFalse($query->isEmpty());
	}

	public function testIsNotEmpty(): void
	{
		$query = new Query('?a=value-a');
		$this->assertTrue($query->isNotEmpty());

		$query = new Query('');
		$this->assertFalse($query->isNotEmpty());
	}

	public function testToString(): void
	{
		$query = new Query([
			'a' => 'value-a',
			'b' => 'value-b'
		]);

		$this->assertSame('a=value-a&b=value-b', $query->toString());
		$this->assertSame('?a=value-a&b=value-b', $query->toString(true));
		$this->assertSame('a=value-a&b=value-b', (string)$query);
	}

	public function testToStringEmpty(): void
	{
		$query = new Query('');
		$this->assertSame('', $query->toString());
		$this->assertSame('', (string)$query);

		$query = new Query(null);
		$this->assertSame('', $query->toString());
		$this->assertSame('', (string)$query);
	}

	public function testUnsetQuery(): void
	{
		$query = new Query(['foo' => 'bar']);
		$query->foo = null;

		$this->assertSame('', $query->toString());
	}
}
