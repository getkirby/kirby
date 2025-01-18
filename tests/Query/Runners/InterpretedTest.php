<?php

namespace Kirby\Query\Runners;

use ArrayAccess;
use Kirby\Query\Runners\Interpreted;

/**
 * @coversDefaultClass \Kirby\Query\Runners\Interpreted
 */
class InterpretedTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Toolkit.Query.RunnerTest';

	/**
	 * @dataProvider interceptProvider
	 */
	public function testIntercept(
		string $query,
		array $context,
		array $intercept,
		array $globalFunctions = []
	): void {
		$actuallyIntercepted = [];

		$interceptorSpy = function ($value) use (&$actuallyIntercepted) {
			$actuallyIntercepted[] = $value;
			return $value;
		};

		$runner = new Interpreted(
			allowedFunctions: $globalFunctions,
			interceptor: $interceptorSpy
		);

		$runner->run($query, $context);

		$this->assertSame($intercept, $actuallyIntercepted);
	}

	/**
	 * @dataProvider resultProvider
	 */
	public function testResult(
		string $query,
		array $context,
		mixed $result,
		array $globalFunctions = []
	): void {
		$runner = new Interpreted(
			allowedFunctions: $globalFunctions,
		);

		$actualResult = $runner->run($query, $context);

		$this->assertSame($result, $actualResult);
	}

	/**
	 * Runners should keep a cache of parsed queries
	 * to avoid parsing the same query multiple times
	 */
	public function testParsesOnlyOnce()
	{
		$cache = [];

		$cacheSpy = $this->createStub(ArrayAccess::class);

		$cacheSpy
			->expects($this->exactly(2))
			->method('offsetExists')
			->willReturnCallback(function ($key) use (&$cache) {
				return isset($cache[$key]);
			});

		$cacheSpy
			->expects($this->exactly(1))
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

		$runner1 = new Interpreted(cache: $cacheSpy);
		$runner2 = new Interpreted(cache: $cacheSpy);

		// it should still give different results for different contexts
		$this->assertSame(42, $runner1->run('foo.bar', ['foo' => ['bar' => 42]]));
		$this->assertSame(84, $runner2->run('foo.bar', ['foo' => ['bar' => 84]]));
	}
}
