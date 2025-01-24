<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
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
