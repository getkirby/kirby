<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Span;

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
class Url extends Span
{
	public static function markers(): array
	{
		return [':'];
	}

	public function consume(Phrase $phrase): Node|false|null
	{
		if ($phrase->at(2) !== '/') {
			return false;
		}

		// the search context is the same string for both the cheap prefilter
		// and the match, so compute the line-tail substring only once
		$context = $phrase->context();

		if (
			strpos($context, 'http') === false ||
			preg_match('/\bhttps?+:[\/]{2}[^\s<]+\b\/*+/ui', $context, $matches, PREG_OFFSET_CAPTURE) !== 1
		) {
			return false;
		}

		$url = $matches[0][0];

		$phrase->reach($matches[0][1], strlen($url));

		return new Element(
			name:       'a',
			attributes: ['href' => $url],
			children:   [new Text($url)],
			break:      false
		);
	}
}
