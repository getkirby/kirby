<?php

namespace Kirby\Query\Runners;

use ArrayAccess;
use Closure;
use Kirby\Query\Parser;
use Kirby\Query\Runner;
use Kirby\Query\Runners\Visitors\Interpreter;
use Kirby\Query\Tokenizer;

class Interpreted extends Runner
{
	public function __construct(
		public array $allowedFunctions = [],
		protected Closure|null $interceptor = null,
		private ArrayAccess|array &$resolverCache = [],
	) {
	}

	protected function getResolver(string $query): Closure
	{
		// load closure from cache
		if (isset($this->resolverCache[$query])) {
			return $this->resolverCache[$query];
		}

		// on cache miss, parse query and generate closure
		$tokenizer = new Tokenizer($query);
		$parser    = new Parser($tokenizer);
		$node      = $parser->parse();
		$self      = $this;

		return $this->resolverCache[$query] = function (array $binding) use ($node, $self) {
			$interpreter = new Interpreter($self->allowedFunctions, $binding);

			if ($self->interceptor !== null) {
				$interpreter->setInterceptor($self->interceptor);
			}

			return $node->accept($interpreter);
		};
	}

	public function run(string $query, array $context = []): mixed
	{
		$resolver = $this->getResolver($query);
		return $resolver($context);
	}
}
