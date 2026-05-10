<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a closure in the AST
 *
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 *
 * @unstable
 */
class ClosureNode extends Node
{
	/**
	 * @param string[] $arguments The arguments names
	 */
	public function __construct(
		public array $arguments,
		public Node $body,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->closure($this);
	}
}
