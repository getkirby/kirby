<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a logical negation of a value in the AST
 *
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 *
 * @unstable
 */
class NotNode extends Node
{
	public function __construct(
		public Node $value
	) {
	}

	public function resolve(Visitor $visitor): bool|string
	{
		return $visitor->not(
			value: $this->value->resolve($visitor)
		);
	}
}
