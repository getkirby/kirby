<?php

namespace Kirby\Toolkit\Query\Runners;

use Closure;
use Kirby\Toolkit\Query\Parser;
use Kirby\Toolkit\Query\Runner;
use Kirby\Toolkit\Query\Tokenizer;

class Interpreted extends Runner {
	private static array $cache = [];

	protected function getResolver(string $query): Closure {
		// load closure from process cache
		if(isset(self::$cache[$query])) {
			return self::$cache[$query];
		}

		// on cache miss, parse query and generate closure
		$t = new Tokenizer($query);
		$parser = new Parser($t);
		$node = $parser->parse();

		return self::$cache[$query] = fn(array $binding) => $node->accept(new Visitors\Interpreter($this->allowedFunctions, $binding));
	}

	public function run(string $query, array $bindings = []): mixed {
		$resolver = $this->getResolver($query);
		return $resolver($bindings);
	}
}
