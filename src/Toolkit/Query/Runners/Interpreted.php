<?php

namespace Kirby\Toolkit\Query\Runners;

use Closure;
use Kirby\Toolkit\Query\Parser;
use Kirby\Toolkit\Query\Runner;
use Kirby\Toolkit\Query\Tokenizer;

class Interpreted extends Runner {
	private static array $cache = [];

	public function __construct(
		public array $allowedFunctions = [],
		protected Closure|null $interceptor = null,
	) {}

	protected function getResolver(string $query): Closure {
		// load closure from process cache
		if(isset(self::$cache[$query])) {
			return self::$cache[$query];
		}

		// on cache miss, parse query and generate closure
		$t = new Tokenizer($query);
		$parser = new Parser($t);
		$node = $parser->parse();

		$self = $this;

		return self::$cache[$query] = function(array $binding) use ($node, $self) {
			$interpreter = new Visitors\Interpreter($self->allowedFunctions, $binding);
			if($self->interceptor !== null) {
				$interpreter->setInterceptor($self->interceptor);
			}
			return $node->accept($interpreter);
		};
	}

	public function run(string $query, array $context = []): mixed {
		$resolver = $this->getResolver($query);
		return $resolver($context);
	}
}
