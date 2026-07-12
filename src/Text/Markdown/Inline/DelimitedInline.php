<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Delimiter;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Inline;
use Kirby\Text\Markdown\Parser\Phrase;

/**
 * Base for an inline whose markers
 * come in matched open/close pairs
 *
 * @example
 * **emphasis**
 * ==mark==
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class DelimitedInline extends Inline
{
	/**
	 * Measures the delimiter run at the cursor
	 * and emits a Delimiter token; the pairing
	 * itself happens later in the `Stack` pass.
	 */
	final public function consume(Phrase $phrase): Node|false
	{
		$marker = $phrase->marker();
		$rest   = $phrase->text();
		$length = strspn($rest, $marker);

		// only the first character of the tail is needed;
		// a UTF-8 code point is at most four bytes,
		// so slice that far and no further
		$after  = mb_substr(substr($rest, $length, 4), 0, 1, 'UTF-8');
		$before = $phrase->before();

		[$canOpen, $canClose] = $this->openClose($before, $after);

		$phrase->take($length);

		return new Delimiter(
			inline:          $this,
			marker:          $marker,
			length:          $length,
			canOpen:         $canOpen,
			canClose:        $canClose,
			intrawordBefore: static::word($before),
			intrawordAfter:  static::word($after)
		);
	}

	/**
	 * Picks the widest pairing that fits the number
	 * markers available on the shorter of a matched
	 * open/close run, returning how many  markers it
	 * consumes from each side and the HTML tag that
	 * wraps the content.
	 *
	 * @return array{int, string}|null
	 */
	final public function pair(int $available): array|null
	{
		foreach ($this->tags() as $width => $tag) {
			if ($available >= $width) {
				return [$width, $tag];
			}
		}

		return null;
	}

	/**
	 * Whether a matched open/close pair is barred
	 * from spanning whitespace.
	 */
	public function rejectsWhitespace(
		Delimiter $open,
		Delimiter $close
	): bool {
		return false;
	}

	/**
	 * The $width => $tag pairings this delimiter forms
	 * ordered  widest first.
	 *
	 * @example
	 * [2 => 'strong', 1 => 'em']
	 *
	 * @return array<int, string>
	 */
	abstract protected function tags(): array;

	/**
	 * Whether a run can `[open, close]`
	 * from the characters flanking it.
	 *
	 * @return array{bool, bool}
	 *
	 * @todo do we have a cleaner name for this?
	 */
	public function openClose(string $before, string $after): array
	{
		return static::flanks($before, $after);
	}

	/**
	 * The raw `[left-flanking, right-flanking]` classification
	 * of a run from the characters immediately around it.
	 *
	 * @return array{bool, bool}
	 */
	protected static function flanks(string $before, string $after): array
	{
		$spaceBefore = static::whitespace($before);
		$spaceAfter  = static::whitespace($after);
		$punctBefore = static::punctuation($before);
		$punctAfter  = static::punctuation($after);

		return [
			$spaceAfter === false &&
			(
				$punctAfter === false ||
				$spaceBefore === true ||
				$punctBefore === true
			),
			$spaceBefore === false &&
			(
				$punctBefore === false ||
				$spaceAfter === true ||
				$punctAfter === true
			)
		];
	}

	/**
	 * Whether the character is ASCII/Unicode
	 * punctuation or a symbol.
	 */
	protected static function punctuation(string $char): bool
	{
		if ($char === '') {
			return false;
		}

		$byte = ord($char[0]);

		// Performance optimization:
		// ASCII fast path: over U+0000–U+007F,
		// `\p{P}` ∪ `\p{S}` is exactly the printable
		// non-alphanumeric, non-space bytes below.
		// Avoids a Unicode `preg_match` on the
		// common ASCII case.
		if ($byte < 0x80) {
			return ($byte >= 0x21 && $byte <= 0x2F) ||
				($byte >= 0x3A && $byte <= 0x40) ||
				($byte >= 0x5B && $byte <= 0x60) ||
				($byte >= 0x7B && $byte <= 0x7E);
		}

		return preg_match('/^[\p{P}\p{S}]/u', $char) === 1;
	}

	/**
	 * Whether CommonMark's "rule of three" forbids
	 * pairing the given open and close runs: when at
	 * least one of them can both open and close,
	 * the pair is refused if their lengths sum to
	 * a multiple of three, unless both lengths
	 * are themselves multiples of three.
	 *
	 * @todo Investigate how much would fail of our fixtures if we remove this behavior simply.
	 */
	public function ruleOfThree(
		Delimiter $open,
		Delimiter $close
	): bool {
		return false;
	}

	/**
	 * Whether the character is Unicode whitespace.
	 */
	protected static function whitespace(string $char): bool
	{
		if ($char === '') {
			return true;
		}

		$byte = ord($char[0]);

		// Performance optimization:
		// ASCII fast path: U+0020 is the only ASCII `\p{Zs}`;
		// the rest of the class is `\t \n \v \f \r` (0x09–0x0D).
		// Non-ASCII falls through to the Unicode `preg_match`
		if ($byte < 0x80) {
			return $byte === 0x20 || ($byte >= 0x09 && $byte <= 0x0D);
		}

		return preg_match('/^[\p{Zs}\t\n\x0b\f\r]/u', $char) === 1;
	}

	/**
	 * Whether the character is a "word" character
	 * in the flanking sense: neither whitespace
	 * nor punctuation (so a run touching it sits
	 * inside a word).
	 */
	protected static function word(string $char): bool
	{
		return
			static::whitespace($char) === false &&
			static::punctuation($char) === false;
	}
}
