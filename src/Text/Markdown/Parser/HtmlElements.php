<?php

namespace Kirby\Text\Markdown\Parser;

/**
 * Helper for the parser to recognize HTML element data
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class HtmlElements
{
	/**
	 * Pattern matching a single HTML attribute (optionally with a value)
	 */
	public const ATTRIBUTE_REGEX = '[a-zA-Z_:][\w:.-]*+(?:\s*+=\s*+(?:[^"\'=<>`\s]+|"[^"]*+"|\'[^\']*+\'))?+';

	/**
	 * Inline ("text-level") HTML elements, which do not start a block
	 *
	 * @var list<string>
	 */
	public const TEXT_LEVEL = [
		'a', 'br', 'bdo', 'abbr', 'blink', 'nextid', 'acronym', 'basefont',
		'b', 'em', 'big', 'cite', 'small', 'spacer', 'listing',
		'i', 'rp', 'del', 'code', 'strike', 'marquee',
		'q', 'rt', 'ins', 'font', 'strong',
		's', 'tt', 'kbd', 'mark',
		'u', 'xm', 'sub', 'nobr',
		'sup', 'ruby',
		'var', 'span',
		'wbr', 'time',
	];
}
