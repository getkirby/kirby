<?php

namespace Kirby\Toolkit\Query\AST;

class CoalesceNode extends Node {
	public function __construct(
		public Node $left,
		public Node $right,
	) {}
}
