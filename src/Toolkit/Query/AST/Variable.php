<?php

namespace Kirby\Toolkit\Query\AST;

class Variable extends Node {
	public function __construct(
		public string $name,
	) {}
}
