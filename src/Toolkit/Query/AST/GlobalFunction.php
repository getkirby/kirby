<?php

namespace Kirby\Toolkit\Query\AST;

class GlobalFunction extends Node {
	public function __construct(
		public string $name,
		public ArgumentList $arguments,
	) {}
}
