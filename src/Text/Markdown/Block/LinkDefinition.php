<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Inline\Link;
use Kirby\Text\Markdown\Parser\Line;
use Kirby\Text\Markdown\Parser\LinkTarget;

/**
 * Link reference definition
 *
 * Anywhere in the document, you define your link without producing
 * any output itself. The link reference will then be used by the
 * reference syntax of `Kirby\Text\Markdown\Inline\Link`.
 *
 * @example
 * [id]: http://example.com/  "Optional Title Here"
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class LinkDefinition extends LeafBlock
{
	public static function markers(): array
	{
		return ['['];
	}

	/**
	 * The definition's candidate text: the current line joined with the
	 * following non-blank lines. A definition's label, destination and
	 * title may each span several lines, but it cannot cross a blank line.
	 * Peeks only, leaving the cursor on the opening line.
	 */
	protected function candidate(Line $line): string
	{
		$segments = [$line->text()];

		for ($i = 1; ($peek = $line->peek($i)) !== null && trim($peek) !== ''; $i++) {
			$segments[] = $peek;
		}

		return implode("\n", $segments);
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): false|null {
		// a definition may neither interrupt a paragraph
		// nor be indented as code
		if (
			($paragraph !== null && $line->isBlank(offset: -1) === false) ||
			$line->indent() >= 4
		) {
			return false;
		}

		$text = $this->candidate($line);

		// the label runs from `[` to the first unescaped `]`, then `:`
		// (it may span multiple lines)
		if (preg_match('/^\[((?:[^\[\]\\\\]|\\\\.)+)\]:/', $text, $matches) !== 1) {
			return false;
		}

		$id = Link::label($matches[1]);

		if ($id === '') {
			return false;
		}

		$definition = $this->definition($text, strlen($matches[0]));

		if ($definition === null) {
			return false;
		}

		[$url, $title, $end] = $definition;

		// the first definition of a label wins
		if ($this->data()->get('LinkDefinition', $id) === null) {
			$this->data()->set('LinkDefinition', $id, [
				'url'   => $url,
				'title' => $title
			]);
		}

		// consume every line the definition spans
		for ($i = substr_count(substr($text, 0, $end), "\n"); $i >= 0; $i--) {
			$line->next();
		}

		return null;
	}

	/**
	 * Parses the destination and optional title following the label.
	 *
	 * @return array{string, string|null, int}|null
	 */
	protected function definition(string $text, int $offset): array|null
	{
		$length  = strlen($text);
		$offset += strspn($text, " \t\n", $offset);

		if ($offset >= $length) {
			return null;
		}

		$destination = LinkTarget::destination($text, $offset);

		if ($destination === null) {
			return null;
		}

		[$url, $offset] = $destination;
		$afterUrl       = $offset;

		// an optional title, separated from the destination by whitespace
		$title = null;
		$space = strspn($text, " \t\n", $offset);

		if (
			$space > 0 &&
			($parsed = LinkTarget::title($text, $offset + $space)) !== null
		) {
			[$title, $offset] = $parsed;
		}

		// the definition must fill its last line (only trailing whitespace
		// may follow); a title with trailing junk is dropped
		$end = $this->lineEnd($text, $offset);

		if ($end === null && $title !== null) {
			$title = null;
			$end   = $this->lineEnd($text, $afterUrl);
		}

		if ($end === null) {
			return null;
		}

		return [
			Link::href($url),
			$title !== null ? Link::decode($title) : null,
			$end
		];
	}

	/**
	 * Returns the offset past the trailing spaces after $offset
	 */
	protected function lineEnd(string $text, int $offset): int|null
	{
		$offset += strspn($text, " \t", $offset);

		if ($offset >= strlen($text) || $text[$offset] === "\n") {
			return $offset;
		}

		return null;
	}
}
