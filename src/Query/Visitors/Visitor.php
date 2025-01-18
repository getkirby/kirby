<?php

namespace Kirby\Query\Visitors;

use Closure;
use Kirby\Query\AST\ClosureNode;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class Visitor
{
	public Closure|null $interceptor = null;

	/**
	 * @param array<string,Closure> $functions valid global function closures
	 * @param array<string,mixed> $context data bindings for the query
	 */
	public function __construct(
		public array $functions = [],
		public array $context = []
	) {
	}

	abstract public function argumentList(array $arguments);
	abstract public function arrayList(array $elements);
	abstract public function closure(ClosureNode $node);
	abstract public function coalescence(mixed $left, mixed $right);
	abstract public function function(string $name, $arguments);
	abstract public function literal(mixed $value);
	abstract public function memberAccess(mixed $object, array|string|null $arguments, string|int $member, bool $nullSafe);
	abstract public function ternary(mixed $condition, mixed $true, mixed $false);
	abstract public function variable(string $name);

	/**
	 * Sets and activates an interceptor closure
	 * that is called for each resolved value.
	 *
	 * @todo can't this be moved to the constructor?
	 */
	public function setInterceptor(Closure $interceptor): void
	{
		$this->interceptor = $interceptor;
	}
}
