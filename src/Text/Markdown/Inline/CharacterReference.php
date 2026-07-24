<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Inline;
use Kirby\Text\Markdown\Parser\Phrase;

/**
 * Entity or numeric character reference
 *
 * A named entity (`&amp;`), a decimal (`&#42;`) or hexadecimal
 * (`&#x2A;`) numeric reference is decoded to the character it
 * denotes. Anything that is not a valid reference
 * leaves the `&` as literal text.
 *
 * @example
 * Tom &amp; Jerry, &copy; 2024, &#42; and &#x2A;
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class CharacterReference extends Inline
{
	// hexadecimal numeric reference: 1–6 hex digits
	protected const string HEX = '/^&#[xX]([0-9a-fA-F]{1,6});/';
	// decimal numeric reference: 1–7 digits
	protected const string DEC = '/^&#([0-9]{1,7});/';
	// named entity
	protected const string NAMED = '/^&[a-zA-Z][a-zA-Z0-9]*+;/';

	public static function markers(): array
	{
		return ['&'];
	}

	/**
	 * Converts a Unicode code point to its UTF-8 character,
	 * substituting U+FFFD for the null, out-of-range etc.
	 */
	protected static function codepoint(int $code): string
	{
		if (
			$code === 0 ||
			$code > 0x10FFFF ||
			($code >= 0xD800 && $code <= 0xDFFF)
		) {
			return "\u{FFFD}";
		}

		$char = mb_chr($code, 'UTF-8');

		return $char === false ? "\u{FFFD}" : $char;
	}

	public function consume(Phrase $phrase): Node|false
	{
		$rest = $phrase->text();


		if (preg_match(self::HEX, $rest, $matches) === 1) {
			$phrase->take($matches[0]);
			$text = self::codepoint(hexdec($matches[1]));
			return new Text($text, break: false);
		}

		if (preg_match(self::DEC, $rest, $matches) === 1) {
			$phrase->take($matches[0]);
			$text = self::codepoint((int)$matches[1]);
			return new Text($text, break: false);
		}

		if (preg_match(self::NAMED, $rest, $matches) === 1) {
			// recognized only if it is also in the HTML5 list
			$text = html_entity_decode($matches[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');

			if ($text !== $matches[0]) {
				$phrase->take($matches[0]);
				return new Text($text, break: false);
			}
		}

		return false;
	}
}
