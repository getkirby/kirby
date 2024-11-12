<?php

namespace Kirby\Toolkit\Query\AST;

class MemberAccessNode extends Node {
	public function __construct(
		public Node $object,
		public string|int $member,
		public ?ArgumentListNode $arguments = null,
		public bool $nullSafe = false,
	) {}
}
