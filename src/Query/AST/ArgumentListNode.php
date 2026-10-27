<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a list of (method) arguments in the AST
 *
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 *
 * @unstable
 */
class ArgumentListNode extends Node
{
	/**
	 * @param list<Node> $arguments
	 */
	public function __construct(
		public array $arguments = []
	) {
	}

	public function resolve(Visitor $visitor): array
	{
		$arguments = array_map(
			fn (Node $argument) => $argument->resolve($visitor),
			$this->arguments
		);

		return $visitor->arguments($arguments);
	}
}
