<?php

namespace Kirby\Toolkit\Query\AST;

class TernaryNode extends Node {
	public function __construct(
		public Node $condition,
		public ?Node $trueBranch,
		public Node $falseBranch,

		public bool $trueBranchIsDefault = false,
	) {}
}