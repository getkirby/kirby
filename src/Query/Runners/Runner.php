<?php

namespace Kirby\Query\Runners;

use ArrayAccess;
use Closure;
use Kirby\Query\Query;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 * @unstable
 */
abstract class Runner
{
	/**
	 * @param array $global Allowed global function closures
	 */
	public function __construct(
		public array $global = [],
		protected Closure|null $interceptor = null,
		protected ArrayAccess|array &$cache = [],
	) {
	}

	/**
	 * Creates a runner instance for the Query
	 */
	abstract public static function for(Query $query): static;

	/**
	 * Executes a query within a given data context
	 *
	 * @param array $context Optional variables to be passed to the query
	 *
	 * @throws \Exception when query is invalid or executor not callable
	 */
	abstract public function run(string $query, array $context = []): mixed;
}
