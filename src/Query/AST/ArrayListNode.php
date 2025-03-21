<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 */
class ArrayListNode extends Node
{
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
