<?php

namespace Kirby\Query\Runners;

use Closure;
use Kirby\Query\Parser\Parser;
use Kirby\Query\Query;
use Kirby\Query\Visitors\DefaultVisitor;

/**
 * Runner that caches the AST in memory
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
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

	protected function resolver(string $query): Closure
	{
		// Load closure from cache
		if (isset($this->cache[$query]) === true) {
			return $this->cache[$query];
		}

		// Parse query as AST
		$parser = new Parser($query);
		$ast    = $parser->parse();

		// Cache closure to resolve same query
		return $this->cache[$query] = fn (array $context) => $ast->resolve(
			new DefaultVisitor($this->global, $context, $this->interceptor)
		);
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

		return $this->resolver($query)($context);
	}
}
