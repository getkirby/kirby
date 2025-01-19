<?php

namespace Kirby\Query;

use Closure;
use Exception;
use Kirby\Cms\App;
use Kirby\Query\Runners\Interpreted;
use Kirby\Query\Runners\Transpiled;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\Query
 */
class QueryTest extends TestCase
{
	protected function tearDown(): void
	{
		App::destroy();
	}

	/**
	 * @covers ::__construct
	 * @covers ::factory
	 */
	public function testFactory(): void
	{
		$query = Query::factory(' user.me ');
		$this->assertSame('user.me', $query->query);
	}

	/**
	 * @covers ::intercept
	 */
	public function testIntercept(): void
	{
		$query = new Query('kirby');
		$this->assertSame('foo', $query->intercept('foo'));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve(): void
	{
		$query = new Query("user.self.likes(['(', ')']).self.drink");
		$data  = ['user' => new TestUser()];
		$this->assertSame(['gin', 'tonic', 'cucumber'], $query->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithEmptyQuery(): void
	{
		$query = new Query('');
		$data = ['foo' => 'bar'];
		$this->assertSame($data, $query->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithComparisonExpresion(): void
	{
		$query = new Query('user.nothing ?? (user.nothing ?? user.isYello(false)) ? user.says("error") : (user.nothing ?? user.says("success"))');
		$data  = ['user' => new TestUser()];
		$this->assertSame('success', $query->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithExactArrayMatch(): void
	{
		$query = new Query('user');
		$this->assertSame('homer', $query->resolve(['user' => 'homer']));

		$query = new Query('this["user.username"]');
		$this->assertSame('homer', $query->resolve(['user.username' => 'homer']));

		$query = new Query('this["user callback"]');
		$this->assertSame('homer', $query->resolve(['user callback' => fn () => 'homer']));
	}

	/**
	 * @covers ::resolve
	 */
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

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithClosureWithArgument(): void
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
	public function testResolveWithInterceptor(): void
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

	/**
	 * @covers ::runner
	 */
	public function testRunner(): void
	{
		$query = new Query('');
		$this->assertInstanceOf(Interpreted::class, $query->runner());
	}

	/**
	 * @covers ::runner
	 */
	public function testRunnerWithConfig(): void
	{
		$app = new App([
			'options' => [
				'query' => [
					'runner' => 'transpiled'
				]
			]
		]);

		$query = new Query('');
		$this->assertInstanceOf(Transpiled::class, $query->runner());
	}

	/**
	 * @covers ::runner
	 */
	public function testRunnerWithInvalidConfig(): void
	{
		$app = new App([
			'options' => [
				'query' => [
					'runner' => 'foo'
				]
			]
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid query runner: foo');

		$query = new Query('');
		$query->runner();
	}
}
