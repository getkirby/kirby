<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a coalesce operation in the AST
 *
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 *
 * @unstable
 */
class CoalesceNode extends Node
{
	public function __construct(
		public Node $left,
		public Node $right,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->coalescence(
			left:  $this->left->resolve($visitor),
			right: $this->right->resolve($visitor)
		);
	}
}
