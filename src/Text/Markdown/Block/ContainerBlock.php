<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Blocks;
use Kirby\Text\Markdown\Parser;

/**
 * Base for a block that contains other blocks
 * with shared lazy-continuation handling.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class ContainerBlock extends Block
{
	protected Blocks|null $probe = null;

	/**
	 * Returns the text of the deepest trailing paragraph in a block tree
	 *
	 * @param list<\Kirby\Text\Markdown\AST\Node> $nodes
	 */
	protected function findTrailingParagraph(array $nodes): string|null
	{
		$node = $nodes === [] ? null : $nodes[array_key_last($nodes)];

		if ($node instanceof Element === false) {
			return null;
		}

		if (is_string($node->content) === true) {
			return $node->name === 'p' || $node->name === null
				? $node->content
				: null;
		}

		if (is_array($node->content) === true) {
			return $this->findTrailingParagraph($this->nodes($node->content));
		}

		if ($node->children !== null) {
			return $this->findTrailingParagraph($node->children);
		}

		return null;
	}

	/**
	 * Whether the `$candidate` line lazily continues the block.
	 *
	 * Inside a container block, a paragraph may run on across lines
	 * that drop the block's own marker, e.g. the blockquote `>` or
	 * the list item's indentation. Such a marker-less line stays part
	 * of the block only when it continues that open paragraph; if the
	 * block's last content is not a paragraph, or the line starts a
	 * new block, the block ends instead.
	 *
	 * The line qualifies when, appended to the block's collected `$lines`,
	 * it extends the deepest trailing paragraph and nothing more.
	 *
	 * @param list<string> $lines the block's content collected so far
	 */
	protected function isLazyContinuation(array $lines, string $candidate): bool
	{
		$before = $this->findTrailingParagraph($this->nodes($lines));

		// there must be a trailing paragraph to continue
		if ($before === null) {
			return false;
		}

		$after = $this->findTrailingParagraph($this->nodes([...$lines, $candidate]));

		// and appending the line must have extended exactly that paragraph
		return $after !== null && str_starts_with($after, $before . "\n");
	}

	/**
	 * Parses lines with the throwaway probe and returns just the
	 * block nodes, dropping the looseness flag.
	 *
	 * @param list<string> $lines
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	protected function nodes(array $lines): array
	{
		return $this->probe()->item($lines)[0];
	}

	/**
	 * The throwaway block parser, created on first use
	 * so that blocks which never test lazy continuation
	 * pay nothing for it.
	 */
	protected function probe(): Blocks
	{
		return $this->probe ??= new Blocks(new Parser());
	}
}
