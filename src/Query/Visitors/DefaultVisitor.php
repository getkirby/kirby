<?php

namespace Kirby\Query\Visitors;

use Closure;
use Exception;
use Kirby\Query\AST\ClosureNode;
use Kirby\Query\Runners\Scope;

/**
 * Processes a query AST
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 * @unstable
 */
class DefaultVisitor extends Visitor
{
	/**
	 * Processes list of arguments
	 */
	public function arguments(array $arguments): array
	{
		return $arguments;
	}

	/**
	 * Processes arithmetic operation
	 */
	public function arithmetic(
		int|float $left,
		string $operator,
		int|float $right
	): mixed {
		return match ($operator) {
			'+'     => $left + $right,
			'-'     => $left - $right,
			'*'     => $left * $right,
			'/'     => $left / $right,
			'%'     => $left % $right,
			default => throw new Exception("Unknown arithmetic operator: $operator")
		};
	}

	/**
	 * Processes array
	 */
	public function arrayList(array $elements): array
	{
		return $elements;
	}

	/**
	 * Processes node into actual closure
	 */
	public function closure(ClosureNode $node): Closure
	{
		$self = $this;

		return function (...$params) use ($self, $node) {
			// [key1, key2] + [value1, value2] =>
			// [key1 => value1, key2 => value2]
			$arguments = array_combine(
				$node->arguments,
				$params
			);

			// Create new nested visitor with combined
			// data context for resolving the closure body
			$visitor = new static(
				global:      $self->global,
				context:     [...$self->context, ...$arguments],
				interceptor: $self->interceptor
			);

			return $node->body->resolve($visitor);
		};
	}

	/**
	 * Processes coalescence operator
	 */
	public function coalescence(mixed $left, mixed $right): mixed
	{
		return $left ?? $right;
	}

	/**
	 * Processes comparison operation
	 */
	public function comparison(
		mixed $left,
		string $operator,
		mixed $right
	): bool {
		return match ($operator) {
			'=='    => $left == $right,
			'==='   => $left === $right,
			'!='    => $left != $right,
			'!=='   => $left !== $right,
			'<'     => $left < $right,
			'<='    => $left <= $right,
			'>'     => $left > $right,
			'>='    => $left >= $right,
			default => throw new Exception("Unknown comparison operator: $operator")
		};
	}

	/**
	 * Processes global function
	 */
	public function function(string $name, array $arguments = []): mixed
	{
		$function = $this->global[$name] ?? null;

		if ($function === null) {
			throw new Exception("Invalid global function in query: $name");
		}

		return $function(...$arguments);
	}

	/**
	 * Processes literals
	 */
	public function literal(mixed $value): mixed
	{
		return $value;
	}

	/**
	 * Processes logical operation
	 */
	public function logical(
		mixed $left,
		string $operator,
		mixed $right
	): bool {
		return match ($operator) {
			'&&', 'AND' => $left && $right,
			'||', 'OR'  => $left || $right,
			default     => throw new Exception("Unknown logical operator: $operator")
		};
	}

	/**
	 * Processes member access
	 */
	public function memberAccess(
		mixed $object,
		string|int $member,
		array|null $arguments = null,
		bool $nullSafe = false
	): mixed {
		if ($this->interceptor !== null) {
			$object = ($this->interceptor)($object);
		}

		return Scope::access($object, $member, $nullSafe, ...$arguments ?? []);
	}

	/**
	 * Processes ternary operator
	 */
	public function ternary(
		mixed $condition,
		mixed $true,
		mixed $false
	): mixed {
		if ($true === null) {
			return $condition ?: $false;
		}

		return $condition ? $true : $false;
	}

	/**
	 * Get variable from context or global function
	 */
	public function variable(string $name): mixed
	{
		return Scope::get($name, $this->context, $this->global);
	}
}
