<?php

namespace Kirby\Query\Runners;

use ArrayAccess;

/**
 * @coversDefaultClass \Kirby\Query\Runners\Interpreted
 */
class InterpretedTest extends TestCase
{
	/**
	 * @dataProvider interceptProvider
	 * @coversNothing
	 */
	public function testIntercept(
		string $query,
		array $context,
		array $intercept,
		array $functions = []
	): void {
		$intercepted = [];
		$interceptor = function ($value) use (&$intercepted) {
			$intercepted[] = $value;
			return $value;
		};

		$runner = new Interpreted(
			functions: $functions,
			interceptor: $interceptor
		);

		$runner->run($query, $context);

		$this->assertSame($intercept, $intercepted);
	}

	/**
	 * @dataProvider resultProvider
	 * @covers ::run
	 */
	public function testResult(
		string $query,
		array $context,
		mixed $expected,
		array $functions = []
	): void {
		$runner = new Interpreted(functions: $functions);
		$result = $runner->run($query, $context);

		$this->assertSame($expected, $result);
	}

	/**
	 * Runners should keep a cache of parsed queries
	 * to avoid parsing the same query multiple times
	 *
	 * @covers ::resolver
	 */
	public function testParsesOnlyOnce()
	{
		$cache = [];

		$cacheSpy = $this->createStub(ArrayAccess::class);

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
			->expects($this->exactly(2))
			->method('offsetSet')
			->willReturnCallback(function ($key, $val) use (&$cache) {
				$cache[$key] = $val;
			});

		$runner1 = new Interpreted(cache: $cacheSpy);
		$runner2 = new Interpreted(cache: $cacheSpy);

		// it should still give different results for different contexts
		$result = $runner1->run('foo.bar', ['foo' => ['bar' => 42]]);
		$this->assertSame(42, $result);

		$result = $runner2->run('foo.bar', ['foo' => ['bar' => 84]]);
		$this->assertSame(84, $result);

		$runner3 = new Interpreted(cache: $cacheSpy);
		$result = $runner3->run('foo.bar', ['foo' => ['bar' => 97]]);
		$this->assertSame(97, $result);
	}
}
