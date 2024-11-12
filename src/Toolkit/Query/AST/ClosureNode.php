<?php

namespace Kirby\Toolkit\Query\AST;

class ClosureNode extends Node
{
	public function __construct(
		public ArgumentListNode $arguments,
		public Node $body,
	) {
	}
}
