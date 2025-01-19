<?php

namespace Kirby\Query;

use Closure;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\Query
 */
class QueryTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::factory
	 */
	public function testFactory()
	{
		$query = Query::factory(' user.me ');
		$this->assertSame('user.me', $query->query);
	}

	/**
	 * @covers ::intercept
	 */
	public function testIntercept()
	{
		$query = new Query('kirby');
		$this->assertSame('foo', $query->intercept('foo'));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve()
	{
		$query = new Query("user.self.likes(['(', ')']).self.drink");
		$data  = ['user' => new TestUser()];
		$this->assertSame(['gin', 'tonic', 'cucumber'], $query->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithEmptyQuery()
	{
		$query = new Query('');
		$data = ['foo' => 'bar'];
		$this->assertSame($data, $query->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithComparisonExpresion()
	{
		$query = new Query('user.nothing ?? (user.nothing ?? user.isYello(false)) ? user.says("error") : (user.nothing ?? user.says("success"))');
		$data  = ['user' => new TestUser()];
		$this->assertSame('success', $query->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithExactArrayMatch()
	{
		$query = new Query('user');
		$this->assertSame('homer', $query->resolve(['user' => 'homer']));

		$query = new Query('user\.username');
		$this->assertSame('homer', $query->resolve(['user.username' => 'homer']));

		$query = new Query('user\.callback');
		$this->assertSame('homer', $query->resolve(['user.callback' => fn () => 'homer']));

		// in the query, the first slash escapes the second, the third escapes the dot
		$query = <<<'TXT'
		user\\\.username
		TXT;

		// this is actually the array key
		$key = <<<'TXT'
		user\.username
		TXT;

		$query = new Query($query);
		$this->assertSame('homer', $query->resolve([$key => 'homer']));
	}

	/**
	 * @covers ::resolve
	 */
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

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithClosureWithArgument()
	{
		$query = new Query('(foo) => foo.homer');
		$data  = [];

		$bar = $query->resolve($data);
		$this->assertInstanceOf(Closure::class, $bar);

		$bar = $bar(['homer' => 'simpson']);
		$this->assertSame('simpson', $bar);
	}

	/**
	 * @covers ::intercept
	 */
	public function testResolveWithInterceptor()
	{
		$query = new class ('foo.getObj.name') extends Query {
			public function intercept($result): mixed
			{
				if (is_object($result) === true) {
					$result = clone $result;
					$result->name .= ' simpson';
				}

				return $result;
			}
		};

		$data  = [
			'foo' => [
				'getObj' => fn () => (object)['name' => 'homer']
			]
		];

		$bar = $query->resolve($data);
		$this->assertSame('homer simpson', $bar);
	}
}
