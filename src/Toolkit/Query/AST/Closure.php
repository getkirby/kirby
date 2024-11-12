<?php

namespace Kirby\Toolkit\Query\AST;

class Closure extends Node
{
	public function __construct(
		public ArgumentList $arguments,
		public Node $body,
	) {
	}
}
