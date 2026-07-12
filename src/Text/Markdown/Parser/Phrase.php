<?php

namespace Kirby\Text\Markdown\Parser;

/**
 * Inline cursor: scans a line of inline text marker to marker, records
 * the byte span each inline claims (the "match"), and emits the plain text
 * between claims. Custom inline components receive it in `consume()` to
 * inspect the marker, claim their span and read the surrounding text.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Phrase extends Cursor
{
	/**
	 * Offset up to which text has already been emitted as nodes.
	 */
	protected int $emitted = 0;

	/**
	 * Byte length of the source text, cached on first use
	 * ($text never changes for a Phrase).
	 */
	protected int $length = -1;

	/**
	 * Byte span of the match the current inline claimed.
	 */
	protected int $matchStart = 0;
	protected int $matchEnd = 0;

	/**
	 * Byte offset of the marker character currently examined.
	 */
	protected int $offset = 0;

	/**
	 * Cache for `text()`: the marker offset the rest was sliced for.
	 */
	protected int $restOffset = -1;
	protected string $rest = '';

	/**
	 * Returns the text after the recorded match. Used to read what
	 * follows a sub-match (e.g. a trailing `{#id}` block).
	 */
	public function after(): string
	{
		return substr($this->text, $this->matchEnd);
	}

	/**
	 * Returns the byte at $distance from the marker
	 */
	public function at(int $distance = 0): string
	{
		return $this->text[$this->offset + $distance] ?? '';
	}

	/**
	 * Returns the character immediately before the marker.
	 */
	public function before(): string
	{
		if ($this->offset === 0) {
			return '';
		}

		// the preceding character is the last one before the marker; walk
		// back over UTF-8 continuation bytes (0b10xxxxxx) to its lead byte
		// and return that whole code point
		$start = $this->offset - 1;

		while ($start > 0 && (ord($this->text[$start]) & 0xC0) === 0x80) {
			$start--;
		}

		return substr($this->text, $start, $this->offset - $start);
	}

	/**
	 * Grows the recorded match by the given number of bytes.
	 */
	public function extend(int $length): void
	{
		$this->matchEnd += $length;
	}

	/**
	 * Advances the emit position past the recorded match.
	 */
	public function flush(): void
	{
		$this->emitted = $this->matchEnd;
	}

	/**
	 * Returns the unmarked text between the emit position
	 * and the start of the match.
	 */
	public function lead(): string
	{
		return substr($this->text, $this->emitted, $this->matchStart - $this->emitted);
	}

	/**
	 * Returns marker character currently examined.
	 */
	public function marker(): string
	{
		return $this->text[$this->offset];
	}

	/**
	 * Whether the recorded match actually starts at or before the marker.
	 * A reach-back match can begin after the marker itself.
	 */
	public function matched(): bool
	{
		return $this->matchStart <= $this->offset;
	}

	/**
	 * Returns the byte offset of the marker currently examined.
	 */
	public function offset(): int
	{
		return $this->offset;
	}

	/**
	 * Records a match of $length bytes that begins $offset bytes
	 * after the emit position.
	 */
	public function reach(int $offset, int $length): void
	{
		$this->matchStart = $this->emitted + $offset;
		$this->matchEnd   = $this->matchStart + $length;
	}

	/**
	 * Returns still-unconsumed text, from the emit position onward.
	 */
	public function remaining(): string
	{
		return substr($this->text, $this->emitted);
	}

	/**
	 * Moves the marker to the next marker character at or
	 * after the emit position; `false` when none remain.
	 */
	public function seek(string $markers): bool
	{
		if ($this->length === -1) {
			$this->length = strlen($this->text);
		}

		$next = $this->emitted + strcspn($this->text, $markers, $this->emitted);

		if ($next >= $this->length) {
			return false;
		}

		$this->offset = $next;

		return true;
	}

	/**
	 * Advances the emit position past an unclaimed marker,
	 * returning the text up to and including it.
	 */
	public function skip(): string
	{
		$text          = substr($this->text, $this->emitted, $this->offset + 1 - $this->emitted);
		$this->emitted = $this->offset + 1;

		return $text;
	}

	/**
	 * Returns a slice of the whole source text (not relative to the marker).
	 */
	public function source(int $start, int $length): string
	{
		return substr($this->text, $start, $length);
	}

	/**
	 * Records a match starting at the marker, spanning the given byte
	 * length or the length of the given matched string.
	 */
	public function take(int|string $length): void
	{
		if (is_string($length) === true) {
			$length = strlen($length);
		}

		$this->matchStart = $this->offset;
		$this->matchEnd   = $this->offset + $length;
	}

	/**
	 * Returns the text from the marker to the end,
	 * optionally sliced: `$offset` bytes past the marker.
	 */
	public function text(int $offset = 0, int|null $length = null): string
	{
		if ($this->restOffset !== $this->offset) {
			$this->rest       = substr($this->text, $this->offset);
			$this->restOffset = $this->offset;
		}

		return $offset === 0 && $length === null
			? $this->rest
			: substr($this->rest, $offset, $length);
	}
}
