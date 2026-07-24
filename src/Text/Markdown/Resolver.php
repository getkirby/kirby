<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;

/**
 * Resolves the deferred `content` that Blocks and Inlines
 * have left on each element into the final AST.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Resolver
{
	public function __construct(
		protected Parser $parser
	) {
	}

	/**
	 * Resolves a single node:
	 * its deferred `content` into child nodes,
	 * then its child nodes recursively.
	 */
	public function node(Node $element): Node
	{
		if ($element instanceof Element) {
			// resolve any deferred content into child nodes: block-level
			// source is re-parsed as blocks, everything else as inlines
			if ($element->content !== null) {
				$element->children = $element->block === true
					? $this->parser->blocks()->parse($element->content)
					: $this->parser->inlines()->parse($element->content);
				$element->content  = null;
			}

			// resolve the child nodes recursively
			if ($element->children !== null) {
				$element->children = $this->nodes($element->children);
			}
		}

		return $element;
	}

	/**
	 * Resolves a list of sibling nodes
	 * (and their deferred content) recursively.
	 *
	 * @param list<\Kirby\Text\Markdown\AST\Node> $nodes
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	public function nodes(array $nodes): array
	{
		// `node()` resolves each Element in place and returns the same
		// instance, so there is nothing to write back: iterating read-only
		// keeps `$nodes` from being copied (it is still held by the caller,
		// so any element write would separate the whole array). Non-Element
		// nodes carry no deferred content, so they are skipped outright.
		foreach ($nodes as $node) {
			if ($node instanceof Element) {
				$this->node($node);
			}
		}

		return $nodes;
	}
}
