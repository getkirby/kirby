<?php

namespace Kirby\Parsley\Schema;

use Kirby\Parsley\Element;
use Kirby\Parsley\Schema;
use Kirby\Toolkit\Str;

/**
 * The plain schema definition converts
 * the entire document into simple text blocks
 *
 * @package   Kirby Parsley
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.5.0
 */
class Plain extends Schema
{
	/**
	 * Creates the fallback block type
	 * if no other block can be found
	 */
	public function fallback(Element|string $element): array|null
	{
		if ($element instanceof Element) {
			$text = $element->innerText();
		} elseif (is_string($element) === true) {
			$text = trim($element);

			if (Str::length($text) === 0) {
				return null;
			}
		} else {
			return null;
		}

		return [
			'content' => [
				'text' => $text
			],
			'type' => 'text',
		];
	}

	/**
	 * Returns a list of all elements that
	 * should be skipped during parsing
	 */
	public function skip(): array
	{
		return [
			'base',
			'link',
			'meta',
			'script',
			'style',
			'title'
		];
	}
}
