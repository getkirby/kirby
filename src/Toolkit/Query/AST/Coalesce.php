<?php

namespace Kirby\Toolkit\Query\AST;

class Coalesce extends Node {
	public function __construct(
		public Node $left,
		public Node $right,
	) {}
}
