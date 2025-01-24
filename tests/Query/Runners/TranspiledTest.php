<?php

namespace Kirby\Query\Runners;

use ArrayAccess;
use Kirby\Filesystem\Dir;
use Kirby\Query\Parser\Parser;
use Kirby\Query\Query;
use Kirby\Query\Visitors\Transpiler;
use Kirby\Toolkit\Str;

/**
 * @coversDefaultClass \Kirby\Query\Runners\Transpiled
 * @covers ::__construct
 */
class TranspiledTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Query.TranspiledTest';

	public function setUp(): void
	{
		$this->setUpTmp();
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
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
	 * @covers ::for
	 */
	public function testFor(): void
	{
		$query  = new Query('');
		$runner = Transpiled::for($query);

		$this->assertInstanceOf(Transpiled::class, $runner);
	}

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
		$this->assertSame(<<<PHP
			<?php
			use Kirby\Toolkit\Str;
			use Kirby\Query\Runners\Runtime;

			// $query
			return function(array \$context, array \$functions, Closure \$intercept) {
			\$_foo = bar;
			\$_2375276105 = Runtime::get('user', \$context, \$functions);

			return Runtime::access((\$intercept(\$_2375276105)), 'add', false, 5.0);
			};
			PHP, $representation);
	}

	/**
	 * @covers ::resolver
	 */
	public function testResolverMemoryCache()
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

		$runner1 = new Transpiled(root: static::TMP, cache: $cacheSpy);
		$runner2 = new Transpiled(root: static::TMP, cache: $cacheSpy);

		// it should still give different results for different contexts
		$result = $runner1->run('foo.bar', ['foo' => ['bar' => 42]]);
		$this->assertSame(42, $result);

		$result = $runner2->run('foo.bar', ['foo' => ['bar' => 84]]);
		$this->assertSame(84, $result);

		$runner3 = new Transpiled(root: static::TMP, cache: $cacheSpy);
		$result = $runner3->run('foo.bar', ['foo' => ['bar' => 97]]);
		$this->assertSame(97, $result);
	}

	/**
	 * @covers ::resolver
	 */
	public function testResolverFileCache()
	{
		$runner1 = new Transpiled(root: static::TMP);
		$runner2 = new Transpiled(root: static::TMP);
		$file    = $runner1->file($query = 'user');

		$this->assertFileDoesNotExist($file);

		$runner1->run($query);
		$this->assertFileExists($file);

		$runner2->run($query);
	}

	/**
	 * @dataProvider resultProvider
	 * @covers ::run
	 */
	public function testRun(
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
	 * @covers ::run
	 */
	public function testRunDirectContextEntry(): void
	{
		$runner = new Transpiled(root: static::TMP);
		$result = $runner->run('null', ['null' => 'foo']);
		$this->assertSame('foo', $result);

		$runner = new Transpiled(root: static::TMP);
		$result = $runner->run('null', ['null' => fn () => 'foo']);
		$this->assertSame('foo', $result);

		$runner = new Transpiled(root: static::TMP);
		$result = $runner->run('null', ['null' => null]);
		$this->assertNull($result);

		$runner = new Transpiled(
			root: static::TMP,
			functions: ['null' => fn () => 'foo']
		);
		$result = $runner->run('null');
		$this->assertSame('foo', $result);

		$runner = new Transpiled(
			root: static::TMP,
			functions: ['null' => fn () => null]
		);
		$result = $runner->run('null');
		$this->assertNull($result);
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
