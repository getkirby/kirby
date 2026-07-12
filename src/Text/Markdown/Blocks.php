<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Orchestrates the Markdown parsing for block-level elements
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Blocks
{
	public function __construct(
		protected Parser $parser
	) {
	}

	/**
	 * Parses a list item's content into block nodes and reports whether
	 * the item is "loose": whether its own top-level blocks are separated
	 * by a blank line (blanks inside a nested block do not count, as those
	 * are consumed by the nested block itself).
	 *
	 * @param list<string> $lines
	 * @return array{0: list<\Kirby\Text\Markdown\AST\Node>, 1: bool}
	 */
	public function item(array $lines): array
	{
		return $this->walk($lines);
	}

	/**
	 * Finalizes a paragraph, dropping the trailing whitespace of its last
	 * line (a paragraph never ends in spaces or tabs).
	 */
	protected function paragraph(Element $paragraph): Element
	{
		$paragraph->content = rtrim($paragraph->content, " \t");

		return $paragraph;
	}

	/**
	 * Parses block-level lines into block nodes.
	 *
	 * @param string|list<string> $source list of pre-split lines or a string
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	public function parse(string|array $source): array
	{
		$lines = is_string($source) === true
			? explode("\n", trim($source, "\n"))
			: $source;

		[$nodes] = $this->walk($lines);

		return $nodes;
	}

	/**
	 * Walks pre-split lines through a Line cursor,
	 * offering each one to the blocks parsers.
	 *
	 * @param list<string> $lines
	 * @return array{0: list<\Kirby\Text\Markdown\AST\Node>, 1: bool} the
	 *                                                                parsed nodes and whether a blank line separates two of them
	 */
	protected function walk(array $lines): array
	{
		$nodes     = [];
		$line      = new Line($lines);
		$paragraph = null;
		$grammar   = $this->parser->grammar();
		$loose     = false;
		$blank     = false; // a blank line has followed some earlier content

		while ($line->valid() === true) {
			if ($line->isBlank() === true) {
				// only a blank between two top-level blocks matters; blanks
				// inside a nested block are consumed by that block, never
				// reaching this loop
				if ($nodes !== [] || $paragraph !== null) {
					$blank = true;
				}

				$line->next();
				continue;
			}

			// content resumes after such a blank: two blocks are separated
			// by a blank line
			if ($blank === true) {
				$loose = true;
				$blank = false;
			}

			// four or more spaces of indentation can only ever start an
			// indented code block; no marker-based block applies (and when
			// a paragraph is open, indented code cannot interrupt it, so
			// the line becomes a lazy continuation instead)
			$blocks = $line->indent() >= 4
				? $grammar->blocks(Block::INDENT)
				: $grammar->blocks($line->marker());

			foreach ($blocks as $block) {
				$node = $block->consume($line, $paragraph);

				if ($node === false) {
					continue;
				}

				// a fresh block (or a match with no output) ends
				// the open paragraph; a transform returns the paragraph itself
				if ($node !== $paragraph && $paragraph !== null) {
					$nodes[] = $this->paragraph($paragraph);
				}

				if ($node !== null) {
					$nodes[] = $node;
				}

				$paragraph = null;

				continue 2;
			}

			// no block started: extend the open paragraph, or begin a new one
			if (
				$paragraph !== null &&
				$line->isBlank(offset: -1) === false
			) {
				// keep the content's raw tabs (only the indentation is
				// removed); block structure was already decided on `text()`
				$paragraph->content .= "\n" . $line->content();

			} else {
				if ($paragraph !== null) {
					$nodes[] = $this->paragraph($paragraph);
				}

				$paragraph = new Element(
					name:      'p',
					multiline: true,
					content:   $line->content()
				);
			}

			$line->next();
		}

		if ($paragraph !== null) {
			$nodes[] = $this->paragraph($paragraph);
		}

		return [$nodes, $loose];
	}
}
