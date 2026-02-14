<?php

namespace Kirby\Query\Visitors;

use Closure;

/**
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 *
 * @unstable
 * @method mixed arguments(array $arguments)
 * @method mixed arithmetic(mixed $left, string $operator, mixed $right)
 * @method mixed arrayList(array $elements)
 * @method mixed closure($ClosureNode $node))
 * @method mixed coalescence($left, $right)
 * @method mixed comparison(mixed $left, string $operator, mixed $right)
 * @method mixed function($name, $arguments)
 * @method mixed literal($value)
 * @method mixed logical(mixed $left, string $operator, mixed $right)
 * @method mixed memberAccess($object, string|int $member, $arguments, bool $nullSafe = false)
 * @method mixed ternary($condition, $true, $false)
 * @method mixed variable(string $name)
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
}
