<?php

namespace Kirby\Query\AST;

class ClosureNode extends Node
{
	/**
	 * @param string[] $arguments The arguments names
	 */
	public function __construct(
		public array $arguments,
		public Node $body,
	) {
	}
}
