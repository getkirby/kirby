<?php

namespace Kirby\Query\Runners;

use Kirby\Query\AST\Node;
use Kirby\Query\Parser\Parser;
use Kirby\Query\Query;
use Kirby\Query\Visitors\DefaultVisitor;

/**
 * Runner that caches the AST in memory
 *
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 *
 * @unstable
 */
class DefaultRunner extends Runner
{
	/**
	 * Creates a runner for the Query
	 */
	public static function for(Query $query): static
	{
		return new static(
			global:      $query::$entries,
			interceptor: $query->intercept(...),
			cache:       $query::$cache
		);
	}

	/**
	 * Returns the AST of the query, parsed once per query string
	 * @since 6.0.0
	 */
	protected function parse(string $query): Node
	{
		return $this->cache[$query] ??= (new Parser($query))->parse();
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
		// Try resolving query directly from data context or global functions
		$entry = Scope::get($query, $context, $this->global, false);

		if ($entry !== false) {
			return $entry;
		}

		$visitor = new DefaultVisitor(
			global:      $this->global,
			context:     $context,
			interceptor: $this->interceptor
		);

		return $this->parse($query)->resolve($visitor);
	}
}
