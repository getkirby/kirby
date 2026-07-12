<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Inline;
use Kirby\Text\Markdown\Parser\Phrase;

/**
 * Base for link and image inlines resolved by the
 * `Stack` and `Brackets` parser passes rather
 * than by marker dispatch.
 *
 * @example
 * [text](url)
 * ![alt](url)
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class BracketedInline extends Inline
{
	final public function consume(Phrase $phrase): false
	{
		return false;
	}

	/**
	 * Builds the HTML element for a resolved bracket
	 *
	 * @param list<\Kirby\Text\Markdown\AST\Node> $children
	 */
	abstract public function element(array $resolved, array $children): Element;
}
