<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\Text\Markdown\AST\Element;

/**
 * Parses a trailing `{#id .class}` attribute block
 * and applies it to an element.
 *
 * Block-level components carry the block in their content;
 * inline components carry it in the phrase after the match.
 */
trait Attributes
{
	/**
	 * Pattern matching the `#id .class` body of an attribute block.
	 */
	protected static string $regex = '(?:[#.][-\w]+[ ]*)+';

	/**
	 * Strips a trailing `{#id .class}` block off the element's content
	 * and sets it as the element's attributes.
	 */
	protected function attributes(
		Element $element,
		string $before,
		string $after = ''
	): Element {
		$regex = '/' . $before . '{(' . static::$regex . ')}' . $after . '$/';

		if (preg_match($regex, $element->content, $matches, PREG_OFFSET_CAPTURE) === 1) {
			$element->attributes = $this->parseAttributes($matches[1][0]);
			$element->content    = substr($element->content, 0, $matches[0][1]);
		}

		return $element;
	}

	/**
	 * Reads a trailing `{#id .class}` block from the phrase
	 * after the match, merges it into the element's attributes.
	 */
	protected function attributesFromPhrase(
		Element $element,
		Phrase $phrase
	): Element {
		$regex = '/^[ ]*{(' . static::$regex . ')}/';

		if (preg_match($regex, $phrase->after(), $matches) === 1) {
			$element->attributes += $this->parseAttributes($matches[1]);
			$phrase->extend(strlen($matches[0]));
		}

		return $element;
	}

	/**
	 * Parses a `#id .class` block body into `id` / `class` attributes.
	 *
	 * @return array<string, string>
	 */
	protected function parseAttributes(string $block): array
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
