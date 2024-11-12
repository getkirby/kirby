<?php

namespace Kirby\Toolkit\Query;

use Closure;
use Exception;

abstract class Runner {
	/**
	 * Runner constructor.
	 *
	 * @param array $allowedFunctions An array of allowed global function closures.
	 */
	public function __construct(
		public array $allowedFunctions = [],
	) {}

	/**
	 * Executes a query within a given data context.
	 *
	 * @param string $query The query string to be executed.
	 * @param array $context An optional array of context variables to be passed to the query executor.
	 * @return mixed The result of the executed query.
	 * @throws \Exception If the query is not valid or the executor is not callable.
	 */
	abstract public function run(string $query, array $context = []): mixed;
}
