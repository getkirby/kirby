<?php

namespace Kirby\Text\Markdown\Parser;

/**
 * Represents current Markdown source line
 * and a cursor that advances through lines.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Line extends Cursor
{
	protected string $body = '';
	protected int $indent = 0;
	protected int $index = 0;

	/**
	 * @param list<string> $lines the raw source lines
	 */
	public function __construct(
		protected array $lines
	) {
		$this->load();
	}

	/**
	 * The current line with tabs expanded to spaces.
	 */
	public function body(): string
	{
		return $this->body;
	}

	/**
	 * The number of leading space characters on the current line.
	 */
	public function indent(): int
	{
		return $this->indent;
	}

	/**
	 * Whether the line (current or at `$offset` from the current position)
	 * is empty or all whitespace.
	 */
	public function isBlank(int $offset = 0): bool
	{
		$line = $this->lines[$this->index + $offset] ?? null;

		return $line !== null && strspn($line, " \t\n\r\0\x0B") === strlen($line);
	}

	/**
	 * Expands tabs and de-indents the line at the current position.
	 */
	protected function load(): void
	{
		if ($this->valid() === false) {
			return;
		}

		$body = $this->lines[$this->index];

		// expand tabs to spaces on a four-column tab stop
		while (($before = strstr($body, "\t", true)) !== false) {
			$short = 4 - mb_strlen($before, 'utf-8') % 4;
			$body  = $before . str_repeat(' ', $short) . substr($body, strlen($before) + 1);
		}

		$this->body   = $body;
		$this->indent = strspn($body, ' ');
		$this->text   = $this->indent > 0 ? substr($body, $this->indent) : $body;
	}

	/**
	 * The first character of the de-indented current line.
	 */
	public function marker(): string
	{
		return $this->text[0] ?? '';
	}

	/**
	 * Whether the de-indented current line
	 * matches the given pattern.
	 */
	public function matches(string $regex): bool
	{
		return preg_match($regex, $this->text) === 1;
	}

	/**
	 * Advances to the next line.
	 */
	public function next(): void
	{
		$this->index++;
		$this->load();
	}

	/**
	 * A slice of the de-indented current line.
	 */
	public function slice(int $offset, int|null $length = null): string
	{
		return substr($this->text, $offset, $length);
	}

	/**
	 * Whether the de-indented current line
	 * starts with the given needle.
	 */
	public function startsWith(string $needle): bool
	{
		return str_starts_with($this->text, $needle);
	}

	/**
	 * Whether a line remains at the current position.
	 */
	public function valid(): bool
	{
		return $this->index < count($this->lines);
	}
}
