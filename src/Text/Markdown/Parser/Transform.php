<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\Text\Markdown\AST\Node;

/**
 * A component that transforms the fully resolved document.
 * Once the whole node tree has been resolved, each registered
 * transform may append to, rewrite or restructure the node list.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
interface Transform
{
	/**
	 * Transforms the resolved top-level node list and returns it.
	 *
	 * @param list<Node> $nodes
	 * @return list<Node>
	 */
	public function transform(array $nodes): array;
}
