<?php

namespace Kirby\Query\Runners;

use Kirby\Query\Parsers\Parser;
use Kirby\Query\Runners\Transpiled;
use Kirby\Query\Visitors\Transpiler;
use Kirby\Toolkit\Str;

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
	 * @covers ::comments
	 */
	public function testComments(): void
	{
		$runner  = new Transpiled(root: static::TMP);
		$comments = $runner->comments('user.add(5.0)');
		$this->assertSame('// user.add(5.0)', $comments);
	}

	/**
	 * @covers ::file
	 */
	public function testFile(): void
	{
		$runner  = new Transpiled(root: static::TMP);
		$file    = $runner->file('user.add(5.0)');
		$this->assertSame(static::TMP . '/197771331.php', $file);
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
	 * @covers ::mappings
	 */
	public function testMappings(): void
	{
		$visitor  = new Transpiler();
		$visitor->mappings['$_foo'] = 'bar';
		$runner   = new Transpiled(root: static::TMP);
		$mappings = $runner->mappings($visitor);
		$this->assertSame("\$_foo = bar;\n", $mappings);
	}

	/**
	 * @covers ::representation
	 */
	public function testRepresentation(): void
	{
		$query          = 'user.add(5.0)';
		$parser         = new Parser($query);
		$visitor        = new Transpiler();
		$visitor->uses[Str::class] = true;
		$visitor->mappings['$_foo'] = 'bar';
		$runner         = new Transpiled(root: static::TMP);
		$representation = $runner->representation($visitor, $query, $parser->parse());
		$this->assertSame(<<<EOT
<?php
use Kirby\Toolkit\Str;
use Kirby\Query\Runners\Runtime;

// $query
return function(array \$context, array \$functions, Closure \$intercept) {
\$_foo = bar;
\$_2375276105 = match(true) {
	isset(\$context['user']) && \$context['user'] instanceof Closure => \$context['user'](),
	isset(\$context['user']) => \$context['user'],
	isset(\$functions['user']) => \$functions['user'](),
	default => null
};

return Runtime::access((\$intercept(\$_2375276105)), 'add', false, 5.0);
};
EOT, $representation);
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

	/**
	 * @covers ::uses
	 */
	public function testUses(): void
	{
		$visitor = new Transpiler();
		$visitor->uses[Str::class] = true;
		$runner  = new Transpiled(root: static::TMP);
		$uses    = $runner->uses($visitor);
		$this->assertSame("use Kirby\Toolkit\Str;\n", $uses);
	}
}
