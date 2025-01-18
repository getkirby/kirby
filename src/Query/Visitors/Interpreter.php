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
 * @author    Roman Steiner <>
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

			$self = new static(
				$self->functions,
				[...$self->context, ...$arguments]
			);

			if ($self->interceptor !== null) {
				$self->setInterceptor($self->interceptor);
			}

			return $node->body->resolve($self);
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
	public function function(string $name, $arguments): mixed
	{
		$function = $this->functions[$name] ?? null;

		if ($function === null) {
			throw new Exception("Invalid global function $name");
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
		array|string|null $arguments,
		string|int $member,
		bool $nullSafe
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
		mixed $false,
		bool $elvis
	): mixed {
		if ($elvis === true) {
			return $condition ?: $false;
		}

		return $condition ? $true : $false;
	}

	/**
	 * Get variable from context or global function
	 */
	public function variable(string $name): mixed
	{
		// what looks like a variable might actually be a global function
		// but if there is a variable with the same name,
		// the variable takes precedence
		if ($context = $this->context[$name] ?? null) {
			if ($context instanceof Closure) {
				return $context();
			}

			return $context;
		}

		if ($function = $this->functions[$name] ?? null) {
			return $function();
		}

		return null;
	}
}
