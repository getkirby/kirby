<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Text;

/**
 * Inline image
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
class Image extends BracketedInline
{
	public static function markers(): array
	{
		return ['!'];
	}

	/**
	 * The alt text of an image is the plain text of its label:
	 * inline markup is dropped, keeping only text content.
	 *
	 * @param list<\Kirby\Text\Markdown\AST\Node> $nodes
	 */
	public static function alt(array $nodes): string
	{
		$text = '';

		foreach ($nodes as $node) {
			$text .= match (true) {
				$node instanceof Text                                => $node->text,
				$node instanceof Element && $node->name === 'img'    => (string)($node->attributes['alt'] ?? ''),
				$node instanceof Element && $node->children !== null => static::alt($node->children),
				default                                              => ''
			};
		}

		return $text;
	}

	/**
	 * An image renders no children: its label becomes the alt text.
	 *
	 * @param list<\Kirby\Text\Markdown\AST\Node> $children
	 */
	public function element(array $resolved, array $children): Element
	{
		$attributes = $resolved['attributes'] ?? [];

		return new Element(
			name:       'img',
			attributes: [
				'src'   => $attributes['href'],
				'alt'   => static::alt($children),
				'title' => $attributes['title']
			] + array_diff_key($attributes, ['href' => null, 'title' => null]),
			break: true
		);
	}
}
