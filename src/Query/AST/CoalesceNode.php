<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
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
		$left  = $this->left->resolve($visitor);
		$right = $this->right->resolve($visitor);
		return $visitor->coalescence($left, $right);
	}
}
