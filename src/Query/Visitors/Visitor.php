<?php

namespace Kirby\Query\Visitors;

use Closure;
use Kirby\Query\AST\ClosureNode;

/**
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 *
 * @unstable
 */
abstract class Visitor
{
	/**
	 * @param array<string,Closure> $global valid global function closures
	 * @param array<string,mixed> $context data bindings for the query
	 */
	public function __construct(
		public array $global = [],
		public array $context = [],
		protected Closure|null $interceptor = null
	) {
	}

	abstract public function arguments(array $arguments): array;

	abstract public function arithmetic(
		int|float $left,
		string $operator,
		int|float $right
	): int|float;

	abstract public function arrayList(array $elements): array;

	abstract public function closure(ClosureNode $node): Closure;

	abstract public function coalescence(mixed $left, mixed $right): mixed;

	abstract public function comparison(
		mixed $left,
		string $operator,
		mixed $right
	): bool;

	abstract public function function(string $name, array $arguments): mixed;

	abstract public function literal(mixed $value): mixed;

	abstract public function logical(
		mixed $left,
		string $operator,
		mixed $right
	): bool;

	abstract public function memberAccess(
		mixed $object,
		string|int $member,
		array|null $arguments,
		bool $nullSafe = false
	): mixed;

	abstract public function ternary(
		mixed $condition,
		mixed $true,
		mixed $false
	): mixed;

	abstract public function variable(string $name): mixed;
}
