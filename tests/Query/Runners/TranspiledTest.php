<?php

namespace Kirby\Query\Runners;

use Kirby\Query\Runners\Transpiled;

/**
 * @coversDefaultClass \Kirby\Query\Runners\Transpiled
 */
class TranspiledTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Query.TranspiledTest';

	public function setUp(): void
	{
		$this->setUpTmp();
	}

	/**
	 * @dataProvider interceptProvider
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

		$runner = new Transpiled(
			functions: $functions,
			interceptor: $interceptor,
			root: static::TMP
		);
		$runner->run($query, $context);

		$this->assertSame(
			$intercept,
			$intercepted,
			'Generated PHP Code:' . PHP_EOL . file_get_contents($runner->file($query))
		);
	}

	/**
	 * @dataProvider resultProvider
	 */
	public function testResult(
		string $query,
		array $context,
		mixed $expected,
		array $functions = []
	): void {
		$runner = new Transpiled(functions: $functions, root: static::TMP);
		$result = $runner->run($query, $context);
		$code   = file_get_contents($runner->file($query));

		$this->assertSame(
			$expected,
			$result,
			'Generated PHP Code:' . PHP_EOL . $code
		);
	}
}
