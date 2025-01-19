<?php

namespace Kirby\Query\Runners;

use ArrayAccess;
use Closure;
use Kirby\Cms\App;
use Kirby\Filesystem\F;
use Kirby\Query\AST\Node;
use Kirby\Query\Parsers\Parser;
use Kirby\Query\Query;
use Kirby\Query\Visitors\Transpiler;

/**
 * Runner that caches the AST as a PHP file
 *
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Transpiled extends Runner
{
	public function __construct(
		protected string $root,
		public array $functions = [],
		protected Closure|null $interceptor = null,
		protected ArrayAccess|array &$cache = []
	) {
	}

	/**
	 * Create string for comments to include query
	 */
	public function comments(string $query): string
	{
		$comments = array_map(
			fn ($line) => "// $line",
			explode("\n", $query)
		);

		return join("\n", $comments);
	}

	/**
	 * Returns the file path for the cache file
	 */
	public function file(string $query): string
	{
		return $this->root . '/' . crc32($query) . '.php';
	}

	/**
	 * Creates a runner for the Query
	 */
	public static function for(Query $query): static
	{
		return new static(
			root:        App::instance()->root('cache') . '/.queries',
			functions:   $query::$entries,
			interceptor: $query->intercept(...),
			cache:       $query::$cache
		);
	}

	/**
	 * Create string representation for mappings
	 */
	public function mappings(Transpiler $visitor): string
	{
		$mappings = array_map(
			fn ($key, $value) => "$key = $value;",
			array_keys($visitor->mappings),
			$visitor->mappings
		);

		return join("\n", $mappings) . "\n";
	}

	/**
	 * Returns the query transpiled as PHP code string
	 */
	public function representation(
		Transpiler $visitor,
		string $query,
		Node $ast
	): string {
		$body     = $ast->resolve($visitor);
		$uses     = $this->uses($visitor);
		$comments = $this->comments($query);
		$mappings = $this->mappings($visitor);
		$closure  = "return function(array \$context, array \$functions, Closure \$intercept) {\n$mappings\nreturn $body;\n};";

		return "<?php\n$uses\n$comments\n$closure";
	}

	/**
	 * Retrieves the executor closure for a given query.
	 *
	 * If the closure is not already cached in the filesystem,
	 * it will be generated and stored in `Transpiled::$root`.
	 */
	protected function resolver(string $query): Closure
	{
		// load closure from memory
		if (isset($this->cache[$query]) === true) {
			return $this->cache[$query];
		}

		// load closure from file
		$file = $this->file($query);

		if (file_exists($file) === true) {
			return $this->cache[$query] = include $file;
		}

		// parse query and generate closure
		$parser = new Parser($query);
		$ast    = $parser->parse();

		// resolve AST to code representations
		$visitor = new Transpiler($this->functions);
		$code     = $this->representation($visitor, $query, $ast);

		// cache in file
		F::write($file, $code);

		// load from file to create memory entry
		return $this->cache[$query] = include $file;
	}

	/**
	 * Executes a query within a given data context
	 *
	 * @param array $context Optional variables to be passed to the query
	 *
	 * @throws \Exception when query is invalid or executor not callable
	 */
	public function run(string $query, array $context = []): mixed
	{
		return $this->resolver($query)(
			$context,
			$this->functions,
			$this->interceptor ?? fn ($obj) => $obj
		);
	}

	/**
	 * Create PHP use references as string representations
	 */
	public function uses(Transpiler $visitor): string
	{
		$uses = array_map(
			fn ($class) => "use $class;",
			array_keys($visitor->uses)
		);

		return join("\n", $uses) . "\n";
	}
}
