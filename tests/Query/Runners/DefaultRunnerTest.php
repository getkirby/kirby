<?php

namespace Kirby\Query\Runners;

use ArrayAccess;
use Kirby\Query\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(DefaultRunner::class)]
class DefaultRunnerTest extends TestCase
{
	public function testFor(): void
	{
		$query  = new Query('');
		$runner = DefaultRunner::for($query);

		$this->assertInstanceOf(DefaultRunner::class, $runner);
	}

	#[DataProvider('interceptProvider')]
	public function testIntercept(
		string $query,
		array $context,
		array $intercept,
		array $global = []
	): void {
		$intercepted = [];
		$interceptor = function ($value) use (&$intercepted) {
			$intercepted[] = $value;
			return $value;
		};

		$runner = new DefaultRunner($global, $interceptor);
		$runner->run($query, $context);

		$this->assertSame($intercept, $intercepted);
	}

	/**
	 * Runners should keep a cache of parsed queries
	 * to avoid parsing the same query multiple times
	 */
	public function testResolverMemoryCache(): void
	{
		$cache = [];

		$cacheSpy = $this->createMock(ArrayAccess::class);

		$cacheSpy
			->expects($this->exactly(3))
			->method('offsetExists')
			->willReturnCallback(function ($key) use (&$cache) {
				return isset($cache[$key]);
			});

		$cacheSpy
			->expects($this->exactly(2))
			->method('offsetGet')
			->willReturnCallback(function ($key) use (&$cache) {
				return $cache[$key] ?? null;
			});

		$cacheSpy
			->expects($this->exactly(1))
			->method('offsetSet')
			->willReturnCallback(function ($key, $val) use (&$cache) {
				$cache[$key] = $val;
			});

		$runner1 = new DefaultRunner(cache: $cacheSpy);
		$runner2 = new DefaultRunner(cache: $cacheSpy);

		// it should still give different results for different contexts
		$result = $runner1->run('foo.bar', ['foo' => ['bar' => 42]]);
		$this->assertSame(42, $result);

		$result = $runner2->run('foo.bar', ['foo' => ['bar' => 84]]);
		$this->assertSame(84, $result);

		$runner3 = new DefaultRunner(cache: $cacheSpy);
		$result = $runner3->run('foo.bar', ['foo' => ['bar' => 97]]);
		$this->assertSame(97, $result);
	}

	#[DataProvider('resultProvider')]
	public function testRun(
		string $query,
		array $context,
		mixed $expected,
		array $global = []
	): void {
		$runner = new DefaultRunner(global: $global);
		$result = $runner->run($query, $context);

		$this->assertSame($expected, $result);
	}

	public function testRunDirectContextEntry(): void
	{
		$runner = new DefaultRunner();
		$result = $runner->run('null', ['null' => 'foo']);
		$this->assertSame('foo', $result);

		$runner = new DefaultRunner();
		$result = $runner->run('null', ['null' => fn () => 'foo']);
		$this->assertSame('foo', $result);

		$runner = new DefaultRunner();
		$result = $runner->run('null', ['null' => null]);
		$this->assertNull($result);

		$runner = new DefaultRunner(global: ['null' => fn () => 'foo']);
		$result = $runner->run('null');
		$this->assertSame('foo', $result);

		$runner = new DefaultRunner(global: ['null' => fn () => null]);
		$result = $runner->run('null');
		$this->assertNull($result);
	}
}
