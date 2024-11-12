<?php

namespace Kirby\Toolkit\Query\AST;

class GlobalFunctionNode extends Node {
	public function __construct(
		public string $name,
		public ArgumentListNode $arguments,
	) {}
}
