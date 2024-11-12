<?php

namespace Kirby\Toolkit\Query\AST;

class MemberAccess extends Node {
	public function __construct(
		public Node $object,
		public string $member,
		public ?ArgumentList $arguments = null,
		public bool $nullSafe = false,
	) {}
}
