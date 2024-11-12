<?php

namespace Kirby\Toolkit\Query\AST;

class ArrayList extends Node {
	public function __construct(
		public array $elements,
	) {}
}
