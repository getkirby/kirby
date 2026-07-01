<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Span;

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
class Code extends Span
{
	protected const PATTERN = '/^([`]++)[ ]*+(.+?)[ ]*+(?<![`])\1(?!`)/s';

	public static function markers(): array
	{
		return ['`'];
	}

	public function consume(Phrase $phrase): Node|false
	{
		$matches = $phrase->match(self::PATTERN);

		if ($matches === null) {
			return false;
		}

		$text = $matches[2];
		$text = preg_replace('/[ ]*+\n/', ' ', $text);

		$phrase->take($matches[0]);

		return new Element(
			name:     'code',
			children: [new Text($text)],
			break:    false
		);
	}
}
