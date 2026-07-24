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
	protected int $count = 0;
	protected int $indent = 0;
	protected int $index = 0;

	/**
	 * @param list<string> $lines the raw source lines
	 */
	public function __construct(
		protected array $lines
	) {
		$this->count = count($lines);
		$this->load();
	}

	/**
	 * Steps back to a previous line, e.g. to hand back
	 * blank lines that a nested block over-read.
	 */
	public function back(int $count = 1): void
	{
		$this->index -= $count;
		$this->load();
	}

	/**
	 * The raw current line, with its tabs and indentation intact.
	 */
	public function body(): string
	{
		return $this->body;
	}

	/**
	 * The current line's content with up to `$columns` columns of
	 * leading whitespace removed (the line's own indentation by default).
	 * Leading whitespace is measured in columns (a tab is a four-column
	 * stop) and the surplus is emitted as spaces, but the content past
	 * the indentation keeps its raw tabs — unlike `text()`, which expands
	 * them. Use it for content (code, paragraph text); use `text()` for
	 * block-structure decisions.
	 */
	public function content(int|null $columns = null): string
	{
		$columns ??= $this->indent;

		// a zero indent means no leading spaces or tabs (a leading tab would
		// expand to columns and lift the indent above zero), so there is
		// nothing to strip: the body is returned verbatim, no copy
		if ($this->indent === 0) {
			return $this->body;
		}

		$column = 0;
		$length = strlen($this->body);

		for ($i = 0; $i < $length; $i++) {
			$char = $this->body[$i];

			if ($char === ' ') {
				$column++;
			} elseif ($char === "\t") {
				$column += 4 - ($column % 4);
			} else {
				break;
			}
		}

		return
			str_repeat(' ', max(0, $column - $columns)) .
			substr($this->body, $i);
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
	 * De-indents the line at the current position. `body()` keeps the
	 * raw line so tabs survive verbatim into code content (CommonMark
	 * does not expand them); `text()` and `indent()` are measured on the
	 * tab-expanded line (four-column tab stop), where block structure —
	 * markers, thematic breaks, setext underlines — is decided.
	 */
	protected function load(): void
	{
		if ($this->valid() === false) {
			return;
		}

		$body     = $this->lines[$this->index];
		$expanded = self::normalizeTabs($body);

		$this->body   = $body;
		$this->indent = strspn($expanded, ' ');
		$this->text   = $this->indent > 0 ? substr($expanded, $this->indent) : $expanded;
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
	 * Advances by `$count` lines.
	 */
	public function next(int $count = 1): void
	{
		$this->index += $count;
		$this->load();
	}

	/**
	 * Normalizes tab characters to spaces
	 * on a four-column tab stop
	 */
	protected static function normalizeTabs(string $line): string
	{
		while (($before = strstr($line, "\t", true)) !== false) {
			$size   = 4 - mb_strlen($before, 'utf-8') % 4;
			$spaces = str_repeat(' ', $size);
			$after  = substr($line, strlen($before) + 1);
			$line   = $before . $spaces . $after;
		}

		return $line;
	}

	/**
	 * The tab-expanded, de-indented text of the line at `$offset`
	 * from the current position, or `null` past the ends. Used for
	 * lookahead without advancing the cursor.
	 */
	public function peek(int $offset): string|null
	{
		$line = $this->lines[$this->index + $offset] ?? null;

		if ($line === null) {
			return null;
		}

		$line = self::normalizeTabs($line);

		return ltrim($line, ' ');
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
	 *
	 * @rename ::isValid()
	 */
	public function valid(): bool
	{
		return $this->index < $this->count;
	}
}
