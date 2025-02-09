<?php

namespace Kirby\Query;

use Closure;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Query::class)]
class QueryTest extends TestCase
{
	public function testFactory()
	{
		$query = Query::factory(' user.me ');
		$this->assertSame('user.me', $query->query);
	}

	public function testIntercept()
	{
		$query = new Query('kirby');
		$this->assertSame('foo', $query->intercept('foo'));
	}

	public function testResolve()
	{
		$query = new Query("user.self.likes(['(', ')']).self.drink");
		$data  = ['user' => new TestUser()];
		$this->assertSame(['gin', 'tonic', 'cucumber'], $query->resolve($data));
	}

	public function testResolveWithEmptyQuery()
	{
		$query = new Query('');
		$data = ['foo' => 'bar'];
		$this->assertSame($data, $query->resolve($data));
	}

	public function testResolveWithComparisonExpresion()
	{
		$query = new Query('user.nothing ?? (user.nothing ?? user.isYello(false)) ? user.says("error") : (user.nothing ?? user.says("success"))');
		$data  = ['user' => new TestUser()];
		$this->assertSame('success', $query->resolve($data));
	}

	public function testResolveWithExactArrayMatch()
	{
		$query = new Query('user');
		$this->assertSame('homer', $query->resolve(['user' => 'homer']));

		$query = new Query('user.username');
		$this->assertSame('homer', $query->resolve(['user.username' => 'homer']));

		$query = new Query('user.callback');
		$this->assertSame('homer', $query->resolve(['user.callback' => fn () => 'homer']));
	}

	public function testResolveWithClosureArgument()
	{
		$query = new Query('foo.bar(() => foo.homer)');
		$data  = [
			'foo' => [
				'bar'   => fn ($callback) => $callback,
				'homer' => 'simpson'
			]
		];

		$bar = $query->resolve($data);
		$this->assertInstanceOf(Closure::class, $bar);
		$bar = $bar();
		$this->assertSame('simpson', $bar);
	}
}
