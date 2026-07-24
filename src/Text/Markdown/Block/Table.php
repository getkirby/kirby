<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Table
 *
 * @example
 * First Header  | Second Header
 * ------------- | -------------
 * Content Cell  | Content `Cell`
 * Content Cell  | Content **Cell**
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Table extends LeafBlock
{
	public static function markers(): array
	{
		return ['-', ':', '|'];
	}

	/**
	 * Parses the delimiter row into an alignment per column.
	 *
	 * @return list<string|null>|false
	 */
	protected function alignments(string $text): array|false
	{
		$alignments = [];
		$divider    = trim(trim($text), '|');

		foreach (explode('|', $divider) as $cell) {
			$cell = trim($cell);

			if ($cell === '') {
				return false;
			}

			$alignment = null;

			if ($cell[0] === ':') {
				$alignment = 'left';
			}

			if (substr($cell, -1) === ':') {
				$alignment = $alignment === 'left' ? 'center' : 'right';
			}

			$alignments[] = $alignment;
		}

		return $alignments;
	}

	/**
	 * Builds a single header or body cell (`<th>` / `<td>`),
	 * applying the column's text-alignment style when set.
	 */
	protected function cell(
		string $name,
		string $content,
		string|null $alignment
	): Element {
		return new Element(
			name:       $name,
			attributes: $alignment !== null ? ['style' => 'text-align: ' . $alignment . ';'] : [],
			multiline:  true,
			content:    trim($content)
		);
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		if (
			$paragraph === null ||
			$line->isBlank(offset: -1) === true
		) {
			return false;
		}

		if (
			strpos($paragraph->content, '|') === false &&
			$line->has('|') === false &&
			$line->has(':') === false
		) {
			return false;
		}

		if (strpos($paragraph->content, "\n") !== false) {
			return false;
		}

		if (rtrim($line->text(), ' -:|') !== '') {
			return false;
		}

		$alignments = $this->alignments($line->text());

		if ($alignments === false) {
			return false;
		}

		$header      = trim(trim($paragraph->content), '|');
		$headerCells = explode('|', $header);

		if (count($headerCells) !== count($alignments)) {
			return false;
		}

		$headerElements = [];

		foreach ($headerCells as $index => $headerCell) {
			$headerElements[] = $this->cell(
				'th',
				$headerCell,
				$alignments[$index] ?? null
			);
		}

		$thead = new Element(
			name:      'thead',
			children:  [
				new Element(
					name: 'tr',
					children: $headerElements,
					multiline: true
				)
			],
			multiline: true
		);

		$tbody = new Element(name: 'tbody', children: [], multiline: true);

		// the open paragraph becomes the table in place
		// (its text was the header)
		$paragraph->name     = 'table';
		$paragraph->children = [$thead, $tbody];
		$paragraph->content  = null;

		// the header paragraph and this delimiter row are now the table head
		$line->next();

		// consume the body rows until a blank or non-row line
		while ($line->valid() === true) {
			if ($line->isBlank() === true) {
				break;
			}

			if (
				count($alignments) !== 1 &&
				$line->marker() !== '|' &&
				strpos($line->text(), '|') === false
			) {
				break;
			}

			$tbody->children[] = $this->row($line->text(), $alignments);

			$line->next();
		}

		return $paragraph;
	}

	/**
	 * Builds a body row (`<tr>`) from a line,
	 * splitting it into `<td>` cells.
	 *
	 * @param list<string|null> $alignments
	 */
	protected function row(string $text, array $alignments): Element
	{
		$row = trim(trim($text), '|');

		preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]++`|`)++/', $row, $matches);

		$cells    = array_slice($matches[0], 0, count($alignments));
		$elements = [];

		foreach ($cells as $index => $cell) {
			$elements[] = $this->cell('td', $cell, $alignments[$index] ?? null);
		}

		return new Element(name: 'tr', children: $elements, multiline: true);
	}
}
