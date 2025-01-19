<?php

namespace Kirby\Query\Visitors;

use Closure;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 *
 * Every visitor class must implement the following methods.
 * As PHP won't allow increasing the typing specificity, we
 * aren't actually adding them here in the abstract class, so that
 * the actual visitor classes can work with much more specific type hints.
 *
 * @method mixed argumentList(array $arguments)
 * @method mixed arrayList(array $elements)
 * @method mixed closure($ClosureNode $node))
 * @method mixed coalescence($left, $right)
 * @method mixed function($name, $arguments)
 * @method mixed literal($value)
 * @method mixed memberAccess($object, string|int $member, $arguments, bool $nullSafe = false)
 * @method mixed ternary($condition, $true, $false)
 * @method mixed variable(string $name)
 */
abstract class Visitor
{
	/**
	 * @param array<string,Closure> $functions valid global function closures
	 * @param array<string,mixed> $context data bindings for the query
	 */
	public function __construct(
		public array $functions = [],
		public array $context = [],
		protected Closure|null $interceptor = null
	) {
	}
}
