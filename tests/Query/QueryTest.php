<?php

namespace Kirby\Query;

use Closure;
use Exception;
use Kirby\Cms\App;
use Kirby\Query\Runners\DefaultRunner;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Query::class)]
class QueryTest extends TestCase
{
	public function tearDown(): void
	{
		App::destroy();
		Query::$runner = null;
	}

	public function test__Construct(): void
	{
		$query = new Query('');
		$this->assertInstanceOf(DefaultRunner::class, $query::$runner);
	}

	public function test__ConstructWitoutConfig(): void
	{
		new App([
			'options' => [
				'query' => [
					'runner' => null
				]
			]
		]);

		$query = new Query('');
		$this->assertInstanceOf(DefaultRunner::class, $query::$runner);
	}

	public function test__ConstructWitLegacyConfig(): void
	{
		new App([
			'options' => [
				'query' => [
					'runner' => 'legacy'
				]
			]
		]);

		$query = new Query('');
		$this->assertSame('legacy', $query::$runner);
	}

	public function test__ConstructWithInvalidConfig(): void
	{
		new App([
			'options' => [
				'query' => [
					'runner' => 'foo'
				]
			]
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Query runner "foo" must extend Kirby\Query\Runners\Runner');

		new Query('');
	}

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

	public static function resolveProvider(): array
	{
		return [
			[
				'user.self.likes([\'(\', \')\']).self.drink',
				['user' => new TestUser()],
				['gin', 'tonic', 'cucumber']
			],
			// empty query
			[
				'',
				['foo' => 'bar'],
				['foo' => 'bar']
			],
			// coalescing
			[
				'user.nothing ?? (user.nothing ?? user.isYello(false)) ? user.says("error") : (user.nothing ?? user.says("success"))',
				['user' => new TestUser()],
				'success'
			],
			// exact array match
			[
				'user',
				['user' => 'homer'],
				'homer'
			],
			[
				'user.username',
				['user.username' => 'homer'],
				'homer'
			],
			[
				'user callback',
				['user callback' => fn () => 'homer'],
				'homer'
			],
			// global this keyword
			[
				'this["user.username"]',
				['user.username' => 'homer'],
				'homer'
			],
			[
				'this["user callback"]',
				['user callback' => fn () => 'homer'],
				'homer'
			],
			// comparison
			[
				'age > 18',
				['age' => 20],
				true
			],
			[
				'age >= minAge',
				['age' => 15, 'minAge' => 18],
				false
			],
			// arithmetic
			[
				'age + 1',
				['age' => 20],
				21
			],
			[
				'2 + 15 * 2 % 10',
				[],
				2
			]
		];
	}

	#[DataProvider('resolveProvider')]
	public function testResolve(
		string $query,
		array $data,
		mixed $expected
	): void {
		$query = new Query($query);
		$this->assertSame($expected, $query->resolve($data));
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

	public function testResolveWithClosureWithArgument(): void
	{
		$query = new Query('(foo) => foo.homer');
		$data  = [];

		$bar = $query->resolve($data);
		$this->assertInstanceOf(Closure::class, $bar);

		$bar = $bar(['homer' => 'simpson']);
		$this->assertSame('simpson', $bar);
	}

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

	public function testDefaultEntries(): void
	{
		$expectedEntries = [
			'kirby', 'collection', 'file', 'page', 'qr',
			'site', 't', 'user', 'users'
		];

		foreach ($expectedEntries as $entryName) {
			$this->assertArrayHasKey($entryName, Query::$entries);
			$this->assertInstanceOf(Closure::class, Query::$entries[$entryName]);
		}
	}
}
