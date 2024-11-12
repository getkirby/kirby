<?php

namespace Kirby\Toolkit\Query\AST;

class ArrayListNode extends Node {
	public function __construct(
		public array $elements,
	) {}
}
