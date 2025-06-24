<?php

namespace Kirby\Query;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Query::class)]
class QueryLegacyTest extends TestCase
{
	public function testFactory(): void
	{
		$query = Query::factory(' user.me ');
		$this->assertSame('user.me', $query->query);
	}

	public function testIntercept(): void
	{
		$query = new Query('kirby');
		$this->assertSame('foo', $query->intercept('foo'));
	}

	public function testResolve(): void
	{
		$query = new Query("user.self.likes(['(', ')']).self.drink");
		$data  = ['user' => new TestUser()];
		$this->assertSame(['gin', 'tonic', 'cucumber'], $query->resolve($data));
	}

	public function testResolveWithEmptyQuery(): void
	{
		$query = new Query('');
		$data = ['foo' => 'bar'];
		$this->assertSame($data, $query->resolve($data));
	}

	public function testResolveWithComparisonExpresion(): void
	{
		$query = new Query('user.nothing ?? (user.nothing ?? user.isYello(false)) ? user.says("error") : (user.nothing ?? user.says("success"))');
		$data  = ['user' => new TestUser()];
		$this->assertSame('success', $query->resolve($data));
	}

	public function testResolveWithExactArrayMatch(): void
	{
		$query = new Query('user');
		$this->assertSame('homer', $query->resolve(['user' => 'homer']));

		$query = new Query('user.username');
		$this->assertSame('homer', $query->resolve(['user.username' => 'homer']));

		$query = new Query('user.callback');
		$this->assertSame('homer', $query->resolve(['user.callback' => fn () => 'homer']));
	}

	public function testResolveWithClosureArgument(): void
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
