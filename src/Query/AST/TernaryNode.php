<?php

namespace Kirby\Query\AST;

/**
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
		public Node|null $trueBranch,
		public Node $falseBranch,
		public bool $trueBranchIsDefault = false,
	) {
	}
}
