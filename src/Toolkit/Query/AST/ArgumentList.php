<?php

namespace Kirby\Toolkit\Query\AST;

class ArgumentList extends Node {
	public function __construct(
		public array $arguments,
	) {}
}
