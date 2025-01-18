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
}
