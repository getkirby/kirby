<?php

namespace Kirby\Toolkit\Query\AST;

class LiteralNode extends Node {
	public function __construct(
		public mixed $value,
	) {}
}
