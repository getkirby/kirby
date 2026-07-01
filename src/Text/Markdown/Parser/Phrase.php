<?php

namespace Kirby\Text\Markdown\Parser;

/**
 * Represents current Markdown source phrase (inline text)
 * and a cursor that advances through the text line.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Phrase extends Cursor
{
	/**
	 * Offset of the marker character currently examined
	 */
	protected int $marker = 0;

	/**
	 * Start of the text not yet turned into elements
	 */
	protected int $position = 0;

	/**
	 * Start of the match the current mark recorded
	 */
	protected int $start = 0;

	/**
	 * End of the match the current mark recorded
	 */
	protected int $end = 0;

	protected int $restMarker = -1;
	protected string $rest = '';

	/**
	 * The text after the recorded match.
	 * Used to read what follows a sub-match
	 * (e.g. trailing `{#id}` block).
	 */
	public function after(): string
	{
		return substr($this->text, $this->end);
	}

	/**
	 * The character at the given offset
	 * after the marker, or '' past the end.
	 */
	public function at(int $offset): string
	{
		return $this->text[$this->marker + $offset] ?? '';
	}

	/**
	 * The number of bytes the recorded match/mark spans.
	 */
	public function consumed(): int
	{
		return $this->end - $this->start;
	}

	/**
	 * The remaining text from the emit position onward.
	 */
	public function context(): string
	{
		return substr($this->text, $this->position);
	}

	/**
	 * Grows the recorded match by the given number of bytes.
	 */
	public function extend(int $length): void
	{
		$this->end += $length;
	}

	/**
	 * Advances the emit position past the recorded match.
	 */
	public function flush(): void
	{
		$this->position = $this->end;
	}

	/**
	 * The unmarked text between the emit position
	 * and the start of the match.
	 */
	public function lead(): string
	{
		return substr($this->text, $this->position, $this->start - $this->position);
	}

	/**
	 * The marker character currently examined.
	 */
	public function marker(): string
	{
		return $this->text[$this->marker];
	}

	/**
	 * Whether the recorded match actually starts at or
	 * before the marker. A reach-back mark can match
	 * something that begins after the marker itself.
	 */
	public function matched(): bool
	{
		return $this->start <= $this->marker;
	}

	/**
	 * Records a match of $length bytes that begins
	 * $offset bytes after the emit position.
	 * A mark reaching back before its marker matches
	 * against `::context()`.
	 */
	public function reach(int $offset, int $length): void
	{
		$this->start = $this->position + $offset;
		$this->end   = $this->start + $length;
	}

	/**
	 * Moves the marker to the next marker character
	 * at or after the emit position; `false` when none remain.
	 */
	public function seek(string $markers): bool
	{
		$next = $this->position + strcspn($this->text, $markers, $this->position);

		if ($next >= strlen($this->text)) {
			return false;
		}

		$this->marker = $next;

		return true;
	}

	/**
	 * Advances the emit position past an unclaimed marker,
	 * returning the text up to and including it.
	 */
	public function skip(): string
	{
		$text           = substr($this->text, $this->position, $this->marker + 1 - $this->position);
		$this->position = $this->marker + 1;

		return $text;
	}

	/**
	 * The marker's rest, optionally sliced:
	 * $offset bytes after the marker, at most $length bytes.
	 */
	public function slice(
		int $offset,
		int|null $length = null
	): string {
		return substr($this->text, $this->marker + $offset, $length);
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

		$this->start = $this->marker;
		$this->end   = $this->marker + $length;
	}

	/**
	 * The marker's rest: the text from the marker to the end.
	 */
	public function text(): string
	{
		if ($this->restMarker !== $this->marker) {
			$this->rest       = substr(parent::text(), $this->marker);
			$this->restMarker = $this->marker;
		}

		return $this->rest;
	}
}
