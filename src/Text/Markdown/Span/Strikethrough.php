<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Span;

/**
 * Strikethrough span
 *
 * @example
 * This is ~~struck~~.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Strikethrough extends Span
{
	protected const PATTERN = '/^~~(?=\S)(.+?)(?<=\S)~~/';

	public static function markers(): array
	{
		return ['~'];
	}

	public function consume(Phrase $phrase): Node|false|null
	{
		if ($phrase->at(1) === '') {
			return false;
		}

		if (
			$phrase->at(1) === '~' &&
			($matches = $phrase->match(self::PATTERN)) !== null
		) {
			$phrase->take($matches[0]);

			return new Element(
				name:      'del',
				multiline: true,
				break:     false,
				content:   $matches[1]
			);
		}

		return false;
	}
}
