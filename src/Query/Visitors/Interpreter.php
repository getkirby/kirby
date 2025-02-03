<?php

namespace Kirby\Query\Visitors;

use Closure;
use Exception;
use Kirby\Query\AST\ClosureNode;
use Kirby\Query\Runners\Runtime;

/**
 * Interprets and directly executes a query AST
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Interpreter extends Visitor
{
	/**
	 * Executes list of arguments
	 */
	public function argumentList(array $arguments): array
	{
		return $arguments;
	}

	/**
	 * Executes array
	 */
	public function arrayList(array $elements): array
	{
		return $elements;
	}

	/**
	 * Converts node into closure
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

			// create new nested visitor with combined
			// data context for resolving the closure body
			$visitor = new static(
				functions: $self->functions,
				context: [...$self->context, ...$arguments],
				interceptor: $self->interceptor
			);

			return $node->body->resolve($visitor);
		};
	}

	/**
	 * Executes coalescence operator
	 */
	public function coalescence(mixed $left, mixed $right): mixed
	{
		return $left ?? $right;
	}

	/**
	 * Executes global function
	 */
	public function function(string $name, array $arguments = []): mixed
	{
		$function = $this->functions[$name] ?? null;

		if ($function === null) {
			throw new Exception("Invalid global function: $name");
		}

		return $function(...$arguments);
	}

	/**
	 * Executes literals
	 */
	public function literal(mixed $value): mixed
	{
		return $value;
	}

	/**
	 * Executes member access
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

		return Runtime::access(
			$object,
			$member,
			$nullSafe,
			...$arguments ?? []
		);
	}

	/**
	 * Executes ternary operator
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
		return Runtime::get($name, $this->context, $this->functions);
	}
}
