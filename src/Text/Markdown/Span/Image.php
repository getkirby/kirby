<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Span;

/**
 * Inline image
 *
 * Markdown uses an image syntax that is intended to resemble
 * the syntax for links, allowing for two styles: inline and reference.
 *
 * @example
 * ![Alt text](/path/to/img.jpg)
 * ![Alt text](/path/to/img.jpg "Optional title")
 * ![Alt text][id]
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Image extends Span
{
	public static function markers(): array
	{
		return ['!'];
	}

	public function consume(Phrase $phrase): Node|false|null
	{
		if ($phrase->at(1) !== '[') {
			return false;
		}

		// reuse the link parser on a cursor over the text past the leading `!`
		$link  = $this->parser->grammar()->span(Link::class);
		$inner = new Phrase($phrase->slice(1));
		$node  = $link?->consume($inner);

		if ($node instanceof Element === false) {
			return false;
		}

		$attributes = [
			'src'   => $node->attributes['href'],
			'alt'   => is_string($node->content) ? $node->content : null,
			'title' => $node->attributes['title']
		];

		// carry over only the trailing `{#id .class}` attributes
		// the link parsed, never the link's own href/title
		// (already mapped above)
		$attributes += array_diff_key(
			$node->attributes,
			['href' => null, 'title' => null]
		);

		$phrase->take($inner->consumed() + 1);

		return new Element(
			name:       'img',
			attributes: $attributes,
			break:      true
		);
	}
}
