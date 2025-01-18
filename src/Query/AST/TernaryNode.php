<?php

namespace Kirby\Query\AST;

class TernaryNode extends Node
{
	public function __construct(
		public Node $condition,
		public Node|null $trueBranch,
		public Node $falseBranch,
		public bool $trueBranchIsDefault = false,
	) {
	}
}
