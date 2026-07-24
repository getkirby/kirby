<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Inline;
use Kirby\Text\Markdown\Parser\Phrase;

/**
 * Inline code span
 *
 * @example
 * This is some `code`.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class CodeSpan extends Inline
{
	protected const string PATTERN = '/^([`]++)(.+?)(?<![`])\1(?!`)/s';

	public static function markers(): array
	{
		return ['`'];
	}

	public function consume(Phrase $phrase): Node|false
	{
		$matches = $phrase->match(self::PATTERN);

		if ($matches === null) {
			// an opening backtick run with no matching closing run is
			// literal as a whole; consume it so the scanner does not start
			// a shorter code span inside it (e.g. ```foo`` stays literal)
			$run = strspn($phrase->text(), '`');
			$phrase->take($run);
			return new Text(str_repeat('`', $run));
		}

		// interior line endings become spaces
		$text = str_replace("\n", ' ', $matches[2]);

		// a single leading and trailing space is dropped when the content
		// is padded on both sides but is not only spaces
		if (
			strlen($text) >= 2 &&
			$text[0] === ' ' &&
			$text[strlen($text) - 1] === ' ' &&
			trim($text, ' ') !== ''
		) {
			$text = substr($text, 1, -1);
		}

		$phrase->take($matches[0]);

		return new Element(
			name:     'code',
			children: [new Text($text)],
			break:    false
		);
	}
}
