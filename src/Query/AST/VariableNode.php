<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a variable (e.g. an object) in the AST
 *
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 *
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
