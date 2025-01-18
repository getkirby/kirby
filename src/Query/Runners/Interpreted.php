<?php

namespace Kirby\Query\Runners;

use Closure;
use Kirby\Query\Parsers\Parser;
use Kirby\Query\Parsers\Tokenizer;
use Kirby\Query\Visitors\Interpreter;

/**
 * Runner that caches the AST in memory
 *
 * @package   Kirby Query
 * @author    Roman Steiner <>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Interpreted extends Runner
{
	protected function resolver(string $query): Closure
	{
		// load closure from cache
		if (isset($this->cache[$query]) === true) {
			return $this->cache[$query];
		}

		// parse query and generate closure
		$parser = new Parser($query);
		$node   = $parser->parse();
		$self   = $this;

		return $this->cache[$query] = function (array $binding) use ($node, $self) {
			$interpreter = new Interpreter($self->allowedFunctions, $binding);

			if ($self->interceptor !== null) {
				$interpreter->setInterceptor($self->interceptor);
			}

			return $node->accept($interpreter);
		};
	}

	public function run(string $query, array $context = []): mixed
	{
		$resolver = $this->resolver($query);
		return $resolver($context);
	}
}
