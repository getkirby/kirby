<?php

namespace Kirby\Query\Runners;

use Closure;
use Exception;
use Kirby\Query\Parsers\Parser;
use Kirby\Query\Parsers\Tokenizer;
use Kirby\Query\Visitors\CodeGen;

/**
 * Runner that caches the AST as a PHP file
 *
 * @package   Kirby Query
 * @author    Roman Steiner <>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Transpiled extends Runner
{
	public static string $cacheFolder = '/tmp/query_cache';

	public static function getCacheFile(string $query): string
	{
		$hash = crc32($query);
		return self::$cacheFolder . '/' . $hash . '.php';
	}

	/**
	 * Retrieves the executor closure for a given query.
	 * If the closure is not already cached, it will be generated and stored in `Runner::$cacheFolder`.
	 *
	 * @param string $query The query string to be executed.
	 * @return Closure The executor closure for the given query.
	 */
	protected function resolver(string $query): Closure
	{
		// load closure from process memory
		if (isset($this->cache[$query]) === true) {
			return $this->cache[$query];
		}

		// load closure from file-cache / opcache
		$filename = self::getCacheFile($query);

		if (file_exists($filename) === true) {
			return $this->cache[$query] = include $filename;
		}

		// parse query and generate closure
		$parser  = new Parser($query);
		$node    = $parser->parse();
		$codeGen = new CodeGen($this->allowedFunctions);

		$functionBody = $node->resolve($codeGen);

		$mappings = array_map(
			fn ($k, $v) => "$k = $v;",
			array_keys($codeGen->mappings),
			$codeGen->mappings
		);
		$mappings = join("\n", $mappings) . "\n";

		$comment = array_map(fn ($l) => "// $l", explode("\n", $query));
		$comment = join("\n", $comment);

		$uses = array_map(fn ($k) => "use $k;", array_keys($codeGen->uses));
		$uses = join("\n", $uses) . "\n";

		$function = "<?php\n$uses\n$comment\nreturn function(array \$context, array \$functions, Closure \$intercept) {\n$mappings\nreturn $functionBody;\n};";

		// store closure in file-cache
		if (is_dir(self::$cacheFolder) === false) {
			mkdir(self::$cacheFolder, 0777, true);
		}

		file_put_contents($filename, $function);

		// load from file-cache to create opcache entry
		return $this->cache[$query] = include $filename;
	}


	/**
	 * Executes a query within a given data context.
	 *
	 * @param string $query The query string to be executed.
	 * @param array $context An optional array of context variables to be passed to the query executor.
	 * @return mixed The result of the executed query.
	 * @throws Exception If the query is not valid or the executor is not callable.
	 */
	public function run(string $query, array $context = []): mixed
	{
		$function = $this->resolver($query);

		if (is_callable($function) === false) {
			throw new Exception('Query is not valid');
		}

		return $function(
			$context,
			$this->allowedFunctions,
			$this->interceptor ?? fn ($v) => $v
		);
	}
}
