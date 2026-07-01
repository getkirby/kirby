<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Span;

/**
 * Backslash escaped character
 *
 * A `\` followed by an escapable special character
 *
 * @example
 * You are my star \* tonight.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class EscapedChar extends Span
{
	/**
	 * Characters that can be backslash-escaped
	 *
	 * @var list<string>
	 */
	public const SPECIAL_CHARACTERS = [
		'\\', '`', '*', '_', '{', '}', '[', ']', '(', ')',
		'>', '#', '+', '-', '.', '!', '|', '~'
	];

	public static function markers(): array
	{
		return ['\\'];
	}

	public function consume(Phrase $phrase): Node|false
	{
		$char = $phrase->at(1);

		if (
			$char !== '' &&
			in_array($char, self::SPECIAL_CHARACTERS, true) === true
		) {
			$phrase->take(2);

			return new Html($char, break: false);
		}

		return false;
	}
}
