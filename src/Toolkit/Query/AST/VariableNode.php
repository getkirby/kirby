<?php

namespace Kirby\Toolkit\Query\AST;

class VariableNode extends Node {
	public function __construct(
		public string $name,
	) {}
}
