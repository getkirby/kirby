<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a global function call in the AST
 *
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 *
 * @unstable
 */
class GlobalFunctionNode extends Node
{
	public function __construct(
		public string $name,
		public ArgumentListNode $arguments,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->function(
			name:      $this->name,
			arguments: $this->arguments->resolve($visitor)
		);
	}
}
