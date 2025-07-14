<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a logical operation between two values in the AST
 *
 * @package   Kirby Query
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 * @unstable
 */
class LogicalNode extends Node
{
	public function __construct(
		public Node $left,
		public string $operator,
		public Node $right
	) {
	}

	public function resolve(Visitor $visitor): bool|string
	{
		return $visitor->logical(
			left: $this->left->resolve($visitor),
			operator: $this->operator,
			right: $this->right->resolve($visitor)
		);
	}
}
