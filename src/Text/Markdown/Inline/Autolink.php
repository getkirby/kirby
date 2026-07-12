<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Inline;
use Kirby\Text\Markdown\Parser\Phrase;

/**
 * Autolinked absolute URI in angle brackets
 *
 * A scheme of 2–32 characters, a colon, then any run of characters that
 * are neither whitespace nor `<`/`>`.
 *
 * @example
 * I found this on <https://example.com>.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Autolink extends Inline
{
	protected const string PATTERN = '/^<([a-zA-Z][a-zA-Z0-9+.\-]{1,31}:[^\s<>\x00-\x1f]*)>/';

	public static function markers(): array
	{
		return ['<'];
	}

	public function consume(Phrase $phrase): Node|false
	{
		if (
			$phrase->has('>') !== true ||
			($matches = $phrase->match(self::PATTERN)) === null
		) {
			return false;
		}

		$url = $matches[1];

		$phrase->take($matches[0]);

		return new Element(
			name:       'a',
			attributes: ['href' => Link::href($url)],
			children:   [new Text($url)],
			break:      false
		);
	}
}
