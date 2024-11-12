<?php

namespace Kirby\Toolkit\Query\AST;

class ArgumentListNode extends Node {
	public function __construct(
		public array $arguments,
	) {}
}
