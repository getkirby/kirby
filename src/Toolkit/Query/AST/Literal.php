<?php

namespace Kirby\Toolkit\Query\AST;

class Literal extends Node {
	public function __construct(
		public mixed $value,
	) {}
}
