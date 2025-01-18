<?php

namespace Kirby\Query\Runners;

use Kirby\Query\Runners\Transpiled;

/**
 * @coversDefaultClass \Kirby\Query\Runners\Transpiled
 */
class TranspiledTest extends TestCase
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

		$this->assertSame($intercept, $actuallyItercepted, 'Generated PHP Code:' . PHP_EOL . file_get_contents(Transpiled::getCacheFile($query)));
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
		Transpiled::$cacheFolder = static::TMP;

		$runner = new Transpiled(
			allowedFunctions: $globalFunctions,
		);

		$actualResult = $runner->run($query, $context);
		$code = file_get_contents(Transpiled::getCacheFile($query));

		$this->assertSame($result, $actualResult, 'Generated PHP Code:' . PHP_EOL . $code);
	}
}
