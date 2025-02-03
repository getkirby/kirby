<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a variable (e.g. an object)
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
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
