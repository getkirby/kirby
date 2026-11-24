<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a (array) list of elements in the AST
 *
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 *
 * @unstable
 */
class ArrayListNode extends Node
{
	/**
	 * @param list<Node> $elements
	 */
	public function __construct(
		public array $elements,
	) {
	}

	public function resolve(Visitor $visitor): array|string
	{
		$elements = array_map(
			fn ($element) => $element->resolve($visitor),
			$this->elements
		);

		return $visitor->arrayList($elements);
	}
}
