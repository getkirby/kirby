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
	 * Parses block-level lines into block nodes.
	 *
	 * @param string|list<string> $source list of pre-split lines or a string
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	public function parse(
		string|array $source,
		bool $unwrap = false
	): array {
		if (is_string($source) === true) {
			$lines = explode("\n", trim($source, "\n"));
			return $this->walk($lines);
		}

		$nodes = $this->walk($source);

		// For a tight list item, `$unwrap` drops the `<p>` wrapper
		// around the leading paragraph, so the item renders its text
		// inline (e.g. `<li>text</li>` instead of `<li><p>text</p></li>`).
		if (
			$unwrap === true &&
			in_array('', $source, true) === false
		) {
			$first = $nodes[0] ?? null;

			// drop the wrapping paragraph: a name-less element
			// renders its children without a surrounding tag
			if ($first instanceof Element && $first->name === 'p') {
				$first->name = null;
			}
		}

		return $nodes;
	}

	/**
	 * Walks pre-split lines through a Line cursor,
	 * offering each one to the blocks parsers.
	 *
	 * @param list<string> $lines
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	protected function walk(array $lines): array
	{
		$nodes     = [];
		$line      = new Line($lines);
		$paragraph = null;
		$grammar   = $this->parser->grammar();

		while ($line->valid() === true) {
			if ($line->isBlank() === true) {
				$line->next();
				continue;
			}

			$blocks = $grammar->blocks($line->marker());

			if ($line->indent() >= 4) {
				array_unshift($blocks, ...$grammar->blocks(Block::INDENT));
			}

			foreach ($blocks as $block) {
				$node = $block->consume($line, $paragraph);

				if ($node === false) {
					continue;
				}

				// a fresh block (or a match with no output) ends
				// the open paragraph; a transform returns the paragraph itself
				if ($node !== $paragraph && $paragraph !== null) {
					$nodes[] = $paragraph;
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
				$paragraph->content .= "\n" . $line->text();

			} else {
				if ($paragraph !== null) {
					$nodes[] = $paragraph;
				}

				$paragraph = new Element(
					name:      'p',
					multiline: true,
					content:   $line->text()
				);
			}

			$line->next();
		}

		if ($paragraph !== null) {
			$nodes[] = $paragraph;
		}

		return $nodes;
	}
}
