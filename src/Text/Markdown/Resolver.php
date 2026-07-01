<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;

/**
 * Resolves the deferred `content` that Blocks and Spans
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
	 * Parses an element's deferred `content` into
	 * its child nodes, at block or inline level.
	 *
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	protected function content(Element $element): array
	{
		if ($element->block === true) {
			return $this->parser->blocks()->parse(
				source: $element->content,
				unwrap: $element->omit === true
			);
		}

		return $this->parser->spans()->parse(
			text:     $element->content,
			disabled: $element->omit ?: []
		);
	}

	/**
	 * Resolves a single node:
	 * its deferred `content` into child nodes,
	 * then its child nodes recursively.
	 */
	public function node(Node $element): Node
	{
		if ($element instanceof Element) {
			// resolve any deferred content into child nodes
			if ($element->content !== null) {
				$element->children = $this->content($element);
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
		foreach ($nodes as $index => $node) {
			$nodes[$index] = $this->node($node);
		}

		return $nodes;
	}
}
