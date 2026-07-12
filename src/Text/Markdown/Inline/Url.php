<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Inline;
use Kirby\Text\Markdown\Parser\Phrase;

/**
 * Autolinked URL
 *
 * @example
 * I found this on https://example.com.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Url extends Inline
{
	protected const string PATTERN = '/\bhttps?+:[\/]{2}[^\s<\]]+\b\/*+/ui';

	public static function markers(): array
	{
		return [':'];
	}

	public function consume(Phrase $phrase): Node|false|null
	{
		if ($phrase->at(2) !== '/') {
			return false;
		}

		// an accepted autolink must begin at or before this `:` marker,
		// so the `:` is the scheme colon of `http`/`https` and the
		// byte before must be `p` or `s`
		$before = $phrase->at(-1);

		if (
			$before !== 'p' &&
			$before !== 'P' &&
			$before !== 's' &&
			$before !== 'S'
		) {
			return false;
		}

		$text = $phrase->remaining();

		if (
			strpos($text, 'http') === false ||
			// `]` ends the run: it is never part of a bare URL and must
			// stay available to close an enclosing link/image bracket
			preg_match(self::PATTERN, $text, $matches, PREG_OFFSET_CAPTURE) !== 1
		) {
			return false;
		}

		$phrase->reach($matches[0][1], strlen($matches[0][0]));

		return new Element(
			name:       'a',
			attributes: ['href' => $matches[0][0]],
			children:   [new Text($matches[0][0])],
			break:      false
		);
	}
}
