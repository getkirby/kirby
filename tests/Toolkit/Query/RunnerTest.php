<?php

namespace Kirby\Toolkit\Query;

use ArrayAccess;
use Kirby\TestCase;
use Kirby\Toolkit\Query\Runners\Interpreted;
use Kirby\Toolkit\Query\Runners\Transpiled;

class RunnerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Toolkit.Query.RunnerTest';

	/**
	 * @dataProvider interceptProvider
	 */
	public function testInterpretedIntercept(string $query, array $context, array $intercept, array $globalFunctions = [])
	{
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

		$this->assertEquals($intercept, $actuallyIntercepted);
	}

	/**
	 * @dataProvider interceptProvider
	 */
	public function testTranspiledIntercept(string $query, array $context, array $intercept, array $globalFunctions = [])
	{
		$actuallyItercepted = [];

		$interceptorSpy = function ($value) use (&$actuallyItercepted) {
			$actuallyItercepted[] = $value;
			return $value;
		};

		Transpiled::$cacheFolder = static::TMP;

		$runner = new Transpiled(
			allowedFunctions: $globalFunctions,
			interceptor: $interceptorSpy
		);
		$runner->run($query, $context);

		$this->assertEquals($intercept, $actuallyItercepted, 'Generated PHP Code:' . PHP_EOL . file_get_contents(Transpiled::getCacheFile($query)));
	}

	/**
	 * @dataProvider resultProvider
	 */
	public function testInterpretedResult(string $query, array $context, mixed $result, array $globalFunctions = [])
	{
		$runner = new Interpreted(
			allowedFunctions: $globalFunctions,
		);

		$actualResult = $runner->run($query, $context);

		$this->assertEquals($result, $actualResult);
	}

	/**
	 * @dataProvider resultProvider
	 */
	public function testTranspiledResult(string $query, array $context, mixed $result, array $globalFunctions = [])
	{
		Transpiled::$cacheFolder = static::TMP;

		$runner = new Transpiled(
			allowedFunctions: $globalFunctions,
		);

		$actualResult = $runner->run($query, $context);
		$code = file_get_contents(Transpiled::getCacheFile($query));

		$this->assertEquals($result, $actualResult, 'Generated PHP Code:' . PHP_EOL . $code);
	}

	/**
	 * Runners should keep a cache of parsed queries to avoid parsing the same query multiple times
	 */
	public function testInterpretParsesOnlyOnce()
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

		$runner1 = new Interpreted(resolverCache: $cacheSpy);
		$runner2 = new Interpreted(resolverCache: $cacheSpy);

		// it should still give different results for different contexts
		$this->assertEquals(42, $runner1->run('foo.bar', ['foo' => ['bar' => 42]]));
		$this->assertEquals(84, $runner2->run('foo.bar', ['foo' => ['bar' => 84]]));
	}

	public static function resultProvider()
	{
		return [
			'field' => [
				'user.name', // query
				['user' => ['name' => 'Homer']], // context
				'Homer', // result
			],

			'nested field' => [
				'user.name.first', // query
				['user' => ['name' => ['first' => 'Homer']]], // context
				'Homer' // result
			],

			'method result' => [
				'user.get("arg").thing', // query
				['user' => ['get' => fn ($a) => ['thing' => $a]]], // context
				'arg' // result
			],

			'closure access to parent context' => [
				'thing.call(() => result).field', // query
				['result' => ['field' => 42], 'thing' => ['call' => fn ($callback) => $callback()]], // context
				42 // result
			],

			'function result for explicit global function' => [
				'foo(42).bar', // query
				[], // context
				84, // result
				['foo' => fn ($a) => ['bar' => $a * 2]] // globalFunctions
			],

			'global function result when function looks like variable - i' => [
				'foo.bar', // query
				[], // context
				42, // result
				['foo' => fn () => ['bar' => 42]] // globalFunctions
			],
		];
	}


	public static function interceptProvider()
	{
		return [
			'field' => [
				'user.name', // query
				['user' => $user = ['name' => 'Homer']], // context
				[$user], // intercept
			],

			'nested field' => [
				'user.name.first', // query
				['user' => $user = ['name' => $name = ['first' => 'Homer']]], // context
				[$user, $name] // intercept
			],

			'method result' => (function () {
				$closureResult = ['age' => 42];
				$user = ['get' => fn () => $closureResult];

				return [
					'user.get("arg").age', // query
					['user' => $user], // context
					[$user, $closureResult] // intercept
				];
			})(),

			'closure result' => (function () {
				$result = ['field' => 'value'];
				$thing = ['call' => fn ($callback) => $callback()];

				return [
					'thing.call(() => result).field', // query
					['thing' => $thing, 'result' => $result], // context
					[$thing, $result] // intercept
				];
			})(),

			'function result for explicit global function' => (function () {
				$functionResult = ['bar' => 'baz'];
				$globalFunctions = ['foo' => fn () => $functionResult];

				return [
					'foo("arg").bar', // query
					[], // context
					[$functionResult], // intercept
					$globalFunctions // globalFunctions
				];
			})(),

			'global function result when function looks like variable - a' => (function () {
				$functionResult = ['bar' => 'baz'];
				$globalFunctions = ['foo' => fn () => $functionResult];

				return [
					'foo.bar', // query
					[], // context
					[$functionResult], // intercept
					$globalFunctions // globalFunctions
				];
			})()
		];
	}
}
