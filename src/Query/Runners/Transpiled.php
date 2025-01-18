<?php

namespace Kirby\Query\Runners;

use ArrayAccess;
use Closure;
use Exception;
use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Query\Parsers\Parser;
use Kirby\Query\Visitors\CodeGen;

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
	public string $root;

	public function __construct(
		public array $functions = [],
		protected Closure|null $interceptor = null,
		protected ArrayAccess|array &$cache = [],
		string|null $root = null
	) {
		$this->root = $root ?? App::instance()->root('cache') . '/.queries';
	}

	public function file(string $query): string
	{
		return $this->root . '/' . crc32($query) . '.php';
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
		$parser  = new Parser($query);
		$ast     = $parser->parse();

		// resolve AST to string representations
		$visitor = new CodeGen($this->functions);
		$body    = $ast->resolve($visitor);

		// create string representation for mappings
		$mappings = array_map(
			fn ($key, $value) => "$key = $value;",
			array_keys($visitor->mappings),
			$visitor->mappings
		);
		$mappings = join("\n", $mappings) . "\n";

		// add query as comments
		$comment = array_map(fn ($line) => "// $line", explode("\n", $query));
		$comment = join("\n", $comment);

		// add PHP use references as string representations
		$uses = array_map(
			fn ($class) => "use $class;",
			array_keys($visitor->uses)
		);
		$uses = join("\n", $uses) . "\n";

		// closure string representation
		$closure = "return function(array \$context, array \$functions, Closure \$intercept) {\n$mappings\nreturn $body;\n};";

		// wrap in PHP file structure
		$function = "<?php\n$uses\n$comment\n$closure";

		// cache in file
		F::write($file, $function);

		// load from file to create memory entry
		return $this->cache[$query] = include $file;
	}

	/**
	 * Executes a query within a given data context
	 *
	 * @param array $context Optional variables to be passed to the query
	 *
	 * @throws Exception when query is invalid or executor not callable
	 */
	public function run(string $query, array $context = []): mixed
	{
		return $this->resolver($query)(
			$context,
			$this->functions,
			$this->interceptor ?? fn ($obj) => $obj
		);
	}
}
