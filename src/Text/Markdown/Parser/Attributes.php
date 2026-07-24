<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\Text\Markdown\AST\Element;

/**
 * Parses a trailing `{#id .class}` attribute block
 * and applies it to an element.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Attributes
{
	public const string PATTERN = '(?:[#.][-\w]+[ ]*)+';

	/**
	 * Strips a trailing `{#id .class}` block
	 * off the element's content and sets it
	 * as the element's attributes.
	 */
	public static function apply(
		Element $element,
		string $before,
		string $after = ''
	): Element {
		if (str_contains($element->content, '{') === false) {
			return $element;
		}

		$pattern = '{(' . self::PATTERN . ')}';
		$pattern = '/' . $before . $pattern . $after . '$/';

		if (preg_match($pattern, $element->content, $matches, PREG_OFFSET_CAPTURE) === 1) {
			$element->attributes = self::parse($matches[1][0]);
			$element->content    = substr($element->content, 0, $matches[0][1]);
		}

		return $element;
	}

	/**
	 * Parses a `#id .class` block body into
	 * `id`/`class` attributes.
	 *
	 * @return array<string, string>
	 */
	public static function parse(string $block): array
	{
		$attributes = [];
		$classes    = [];

		foreach (preg_split('/[ ]+/', $block, -1, PREG_SPLIT_NO_EMPTY) as $part) {
			if ($part[0] === '#') {
				$attributes['id'] = substr($part, 1);
			} else {
				$classes[] = substr($part, 1);
			}
		}

		if ($classes !== []) {
			$attributes['class'] = implode(' ', $classes);
		}

		return $attributes;
	}
}
