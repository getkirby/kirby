<?php

namespace Kirby\Query\Runners;

use ArrayAccess;
use Closure;
use Exception;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class Runner
{
	/**
	 * @param array $allowedFunctions Allowed global function closures
	 */
	public function __construct(
		public array $allowedFunctions = [],
		protected Closure|null $interceptor = null,
		protected ArrayAccess|array &$cache = [],
	) {
	}

	/**
	 * Executes a query within a given data context
	 *
	 * @param array $context Optional context variables to be passed to the query executor
	 *
	 * @throws Exception If the query is not valid or the executor is not callable.
	 */
	abstract public function run(string $query, array $context = []): mixed;
}
