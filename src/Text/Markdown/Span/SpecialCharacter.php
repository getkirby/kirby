<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Span;

/**
 * HTML entity
 * A named or numeric character reference
 *
 * @example
 * Have you ever watch Tom &amp; Jerry
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class SpecialCharacter extends Span
{
	protected const PATTERN = '/^&(#?+[0-9a-zA-Z]++);/';

	public static function markers(): array
	{
		return ['&'];
	}

	public function consume(Phrase $phrase): Node|false|null
	{
		if (
			$phrase->at(1) === ' ' ||
			$phrase->has(';') === false
		) {
			return false;
		}

		$matches = $phrase->match(self::PATTERN);

		if ($matches === null) {
			return false;
		}

		$phrase->take($matches[0]);

		return new Html('&' . $matches[1] . ';', break: false);
	}
}
