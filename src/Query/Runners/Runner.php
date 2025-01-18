<?php

namespace Kirby\Query\Runners;

use ArrayAccess;
use Closure;
use Exception;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class Runner
{
	/**
	 * @param array $functions Allowed global function closures
	 */
	public function __construct(
		public array $functions = [],
		protected Closure|null $interceptor = null,
		protected ArrayAccess|array &$cache = [],
	) {
	}

	/**
	 * Executes a query within a given data context
	 *
	 * @param array $context Optional variables to be passed to the query
	 *
	 * @throws Exception when query is invalid or executor not callable
	 */
	abstract public function run(string $query, array $context = []): mixed;
}
