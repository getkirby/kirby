<?php

namespace Kirby\Toolkit\Query\Runners;

use Closure;
use Exception;
use Kirby\Toolkit\Query\Parser;
use Kirby\Toolkit\Query\Runner;
use Kirby\Toolkit\Query\Tokenizer;

class Transpiled extends Runner {
	private static array $cache = [];
	public static string $cacheFolder = '/tmp/query_cache';

	/**
	 * Runner constructor.
	 *
	 * @param array $allowedFunctions An array of allowed global function closures.
	 */
	public function __construct(
		public array $allowedFunctions = [],
		public Closure|null $interceptor = null,
	) {}


	/**
	 * Retrieves the executor closure for a given query.
	 * If the closure is not already cached, it will be generated and stored in `Runner::$cacheFolder`.
	 *
	 * @param string $query The query string to be executed.
	 * @return Closure The executor closure for the given query.
	 */
	protected function getResolver(string $query): Closure {
		// load closure from process memory
		if(isset(self::$cache[$query])) {
			return self::$cache[$query];
		}

		// load closure from file-cache / opcache
		$hash = crc32($query);
		$filename = self::$cacheFolder . '/' . $hash . '.php';
		if(file_exists($filename)) {
			return self::$cache[$query] = include $filename;
		}

		// on cache miss, parse query and generate closure
		$t = new Tokenizer($query);
		$parser = new Parser($t);
		$node = $parser->parse();
		$codeGen = new Visitors\CodeGen($this->allowedFunctions);

		$functionBody = $node->accept($codeGen);

		$mappings = join("\n", array_map(fn($k, $v) => "$$k = $v;", array_keys($codeGen->mappings), $codeGen->mappings)) . "\n";
		$comment = join("\n", array_map(fn($l) => "// $l", explode("\n", $query)));

		$uses = join("\n", array_map(fn($k) => "use $k;", array_keys($codeGen->uses))) . "\n";
		$function = "<?php\n$uses\n$comment\nreturn function(array \$context, array \$functions, Closure \$intercept) {\n$mappings\nreturn $functionBody;\n};";

		// store closure in file-cache
		if(!is_dir(self::$cacheFolder)) {
			mkdir(self::$cacheFolder, 0777, true);
		}

		file_put_contents($filename, $function);

		// load from file-cache to create opcache entry
		return self::$cache[$query] = include $filename;
	}


	/**
	 * Executes a query within a given data context.
	 *
	 * @param string $query The query string to be executed.
	 * @param array $context An optional array of context variables to be passed to the query executor.
	 * @return mixed The result of the executed query.
	 * @throws Exception If the query is not valid or the executor is not callable.
	 */
	public function run(string $query, array $context = []): mixed {
		$function = $this->getResolver($query);
		if(!is_callable($function)) {
			throw new Exception("Query is not valid");
		}
		return $function($context, $this->allowedFunctions, $this->interceptor ?? fn($v) => $v);
	}
}
