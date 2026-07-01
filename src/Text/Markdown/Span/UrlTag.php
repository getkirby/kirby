<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Span;

/**
 * Linked URL in angle brackets
 *
 * @example
 * I found this on <https://example.com>.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class UrlTag extends Span
{
	protected const PATTERN = '/^<(\w++:\/{2}[^ >]++)>/i';

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
			attributes: ['href' => $url],
			children:   [new Text($url)],
			break:      false
		);
	}
}
