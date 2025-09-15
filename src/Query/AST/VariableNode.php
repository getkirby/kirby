<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a variable (e.g. an object) in the AST
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 * @unstable
 */
class VariableNode extends Node
{
	public function __construct(
		public string $name,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->variable($this->name);
	}
}
