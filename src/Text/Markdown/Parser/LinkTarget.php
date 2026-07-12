<?php

namespace Kirby\Text\Markdown\Parser;

/**
 * Grammar for a link's destination and title.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class LinkTarget
{
	/**
	 * The ASCII punctuation characters a backslash may escape.
	 */
	protected const string PUNCTUATION = '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~';

	/**
	 * Parses a link destination starting at $offset:
	 * either an angle-bracketed `<…>` or a bare run
	 * with balanced parentheses.
	 *
	 * @return array{string, int}|null
	 */
	public static function destination(
		string $text,
		int $offset
	): array|null {
		$length = strlen($text);

		// angle-bracketed form, e.g. `<https://example.com/a b>`
		if (($text[$offset] ?? '') === '<') {
			$value = '';

			for ($i = $offset + 1; $i < $length; $i++) {
				$char = $text[$i];

				// a backslash-escaped punctuation char is taken literally,
				// e.g. the `\>` in `<a\>b>` is part of the URL
				if (
					$char === '\\' &&
					self::escaped($text[$i + 1] ?? '') === true
				) {
					$value .= $text[++$i];
					continue;
				}

				// malformed: `<a\nb>` (line break) or `<a<b>` (nested `<`)
				if ($char === "\n" || $char === '<') {
					return null;
				}

				// the closing `>`, e.g. end of `<https://example.com>`
				if ($char === '>') {
					return [$value, $i + 1];
				}

				$value .= $char;
			}

			// reached the end without a closing `>`
			return null;
		}

		// Bare run, e.g. `https://example.com`: stops at the first space,
		// control character or the unbalanced `)` that closes the link.
		//
		// Fast path: scan once to the first byte that needs
		// interpreting  (a control/space (stop), a `(`/`)`
		// (paren balancing) or a `\` (escape)). Unless it is
		// a `(` or `\`, the run is a clean slice with no balancing
		// or unescaping to do, so return it without the per-byte
		// rebuild below (the common case: a plain URL).
		static $stops = null;
		$stops ??= implode('', array_map('chr', range(0, 0x20))) . '()\\';

		$scan = $offset + strcspn($text, $stops, $offset);
		$stop = $text[$scan] ?? '';

		if ($stop !== '(' && $stop !== '\\') {
			// nothing before the stop, e.g. `)` right after `(` in `[x]()`
			if ($scan === $offset) {
				return null;
			}

			// clean slice up to the stop, e.g. `https://example.com` out of
			// `https://example.com)` or `https://example.com "title")`
			return [substr($text, $offset, $scan - $offset), $scan];
		}

		// slow path: the run holds a `(` to balance or a `\` to unescape,
		// e.g. `https://example.com/foo(bar)` or `a\(b`
		$value = '';
		$depth = 0;

		for ($i = $offset; $i < $length; $i++) {
			$char = $text[$i];

			if (
				$char === '\\' &&
				self::escaped($text[$i + 1] ?? '') === true
			) {
				$value .= $text[++$i];
				continue;
			}

			// a space or control character ends the run
			if (ord($char) <= 0x20) {
				break;
			}

			if ($char === '(') {
				$depth++;
			} elseif ($char === ')') {
				// a `)` while balanced (`depth 0`) is the link's own closer,
				// e.g. the final `)` in `[x](a(b)c)` after `(b)` balanced out
				if ($depth === 0) {
					break;
				}

				$depth--;
			}

			$value .= $char;
		}

		// invalid: nothing consumed, or a `(` left open (e.g. `a(b`)
		if ($i === $offset || $depth !== 0) {
			return null;
		}

		return [$value, $i];
	}

	/**
	 * Whether a backslash escapes the given character, i.e. the char is
	 * ASCII punctuation.
	 */
	protected static function escaped(string $char): bool
	{
		return $char !== '' && strpbrk($char, self::PUNCTUATION) !== false;
	}

	/**
	 * Parses the inline `(destination "title")` target
	 * starting at the `(`.
	 *
	 * @return array{href: string, title: string|null, length: int}|null
	 */
	public static function parse(string $text): array|null
	{
		// the target must open with `(`, e.g. `(https://example.com)`
		if (($text[0] ?? '') !== '(') {
			return null;
		}

		$offset = self::skip($text, 1);

		// empty target `()` — a link with no destination
		if (($text[$offset] ?? '') === ')') {
			$href = '';
		} elseif (($destination = self::destination($text, $offset)) !== null) {
			[$href, $offset] = $destination;
		} else {
			return null;
		}

		$offset = self::skip($text, $offset);
		$title  = null;

		// an optional title after whitespace, e.g. the `"t"` in `(url "t")`
		if (($parsed = self::title($text, $offset)) !== null) {
			[$title, $offset] = $parsed;
			$offset           = self::skip($text, $offset);
		}

		// the target must close with `)`; junk before it (e.g. `(url x)`)
		// is invalid
		if (($text[$offset] ?? '') !== ')') {
			return null;
		}

		return [
			'href'   => $href,
			'title'  => $title,
			'length' => $offset + 1
		];
	}

	/**
	 * Advances past inline whitespace, including line breaks.
	 */
	protected static function skip(string $text, int $offset): int
	{
		return $offset + strspn($text, " \t\n", $offset);
	}

	/**
	 * Parses an optional link title (`"…"`, `'…'` or `(…)`)
	 * starting at $offset.
	 *
	 * @return array{string, int}|null
	 */
	public static function title(string $text, int $offset): array|null
	{
		// opening delimiter picks its matching closer: `"…"`, `'…'` or `(…)`
		$open  = $text[$offset] ?? '';
		$close = match ($open) {
			'"', "'" => $open,
			'('      => ')',
			default  => null
		};

		// no title here, e.g. the offset sits on the `)` of `(url)`
		if ($close === null) {
			return null;
		}

		$length = strlen($text);

		// Fast path: scan to the first byte needing interpretation (the
		// closing delimiter, a `\` (escape) or, in a paren title, a
		// forbidden `(`). Only a `\` needs the per-byte unescape loop below;
		// the rest resolve the title as a clean slice (the common case).
		$start = $offset + 1;
		$scan  = $start + strcspn($text, '\\' . $close . ($open === '(' ? '(' : ''), $start);
		$stop  = $text[$scan] ?? '';

		// a clean slice up to the closer, e.g. `My title` from `"My title"`
		if ($stop === $close) {
			return [substr($text, $start, $scan - $start), $scan + 1];
		}

		// no closer at all (e.g. `"unterminated`), or the forbidden `(`
		// inside a `(…)` title, which stopped the scan above
		if ($stop !== '\\') {
			return null;
		}

		// slow path: the title holds a backslash escape, e.g. `"a \" b"`
		$value = '';

		for ($i = $offset + 1; $i < $length; $i++) {
			$char = $text[$i];

			if (
				$char === '\\' &&
				self::escaped($text[$i + 1] ?? '') === true
			) {
				$value .= $text[++$i];
				continue;
			}

			// a `(…)` title may not contain an unescaped `(`, e.g. `(a(b)`
			if ($open === '(' && $char === '(') {
				return null;
			}

			if ($char === $close) {
				return [$value, $i + 1];
			}

			$value .= $char;
		}

		return null;
	}
}
