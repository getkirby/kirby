<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\Parser\Phrase;

/**
 * Base for an inline resolved by the
 * delimiter stack's bracket pass rather
 * than by marker dispatch.
 *
 * @example
 * `[text](url)`
 * `![alt](url)`
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class BracketedInline extends Inline
{
	/**
	 * Bracketed inlines are resolved by the stack,
	 * not by marker  dispatch, so a marker reaching
	 * this method never starts one.
	 */
	final public function consume(Phrase $phrase): false
	{
		return false;
	}
}
