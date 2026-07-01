<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Base for a single block-level Markdown component
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class Block extends Component
{
	/**
	 * Dispatch trigger for indented lines (at least four spaces).
	 * Tabs are expanded and the line is de-indented before dispatch,
	 * so a real first-character trigger is never a tab.
	 */
	public const string INDENT = "\t";

	/**
	 * Tries to start a block at the $line,
	 * consuming every line  it owns.
	 * Returns the generated Node, `null` if the block matched
	 * but emits nothing, or `false` if it isn't this block.
	 *
	 * @param $paragraph open paragraph the loop is building
	 */
	abstract public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false|null;
}
