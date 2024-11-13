<?php

namespace Kirby\Toolkit\Query\AST;

class GlobalFunctionNode extends IdentifierNode
{
	public function __construct(
		public string $name,
		public ArgumentListNode $arguments,
	) {
	}

	/**
	 * Replace escaped dots with real dots
	 */
	public function name(): string
	{
		return str_replace('\.', '.', $this->name);
	}
}
