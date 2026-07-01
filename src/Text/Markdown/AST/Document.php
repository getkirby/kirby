<?php

namespace Kirby\Text\Markdown\AST;

/**
 * Root node of the AST: an ordered sequence of sibling nodes
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Document extends Node
{
	/**
	 * @param list<Node> $children
	 */
	public function __construct(
		public readonly array $children
	) {
		parent::__construct();
	}
}
