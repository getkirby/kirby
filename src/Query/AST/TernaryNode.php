<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a ternary condition
 * with a value for when the condition is true
 * and another value for when the condition is false
 *
 * @package   Kirby Query
 * @author    Roman Steiner <>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class TernaryNode extends Node
{
	public function __construct(
		public Node $condition,
		public Node $false,
		public Node|null $true = null
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		$condition = $this->condition->resolve($visitor);
		$true      = $this->true?->resolve($visitor);
		$false     = $this->false->resolve($visitor);
		return $visitor->ternary($condition, $true, $false);
	}
}
