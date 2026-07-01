<?php

namespace Kirby\Text\Markdown\AST;

/**
 * Base for all Markdown components
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class Node
{
	public function __construct(
		public readonly bool|null $break = null
	) {
	}

	/**
	 * Whether this node introduces a line break when rendered
	 * inside a sibling list. Leaf nodes default to false.
	 */
	public function hasBreak(): bool
	{
		return $this->break ?? false;
	}
}
