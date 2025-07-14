<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a (array) list of elements in the AST
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 * @unstable
 */
class ArrayListNode extends Node
{
	public function __construct(
		public array $elements,
	) {
	}

	public function resolve(Visitor $visitor): array|string
	{
		// Resolve each array element
		$elements = array_map(
			fn ($element) => $element->resolve($visitor),
			$this->elements
		);

		// Keep as array or convert to string
		// depending on the visitor type
		return $visitor->arrayList($elements);
	}
}
