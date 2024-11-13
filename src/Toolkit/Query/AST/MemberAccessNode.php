<?php

namespace Kirby\Toolkit\Query\AST;

class MemberAccessNode extends IdentifierNode {
	public function __construct(
		public Node $object,
		public string|int $member,
		public ?ArgumentListNode $arguments = null,
		public bool $nullSafe = false,
	) {}

	/**
	 * Returns the member name and replaces escaped dots with real dots if it's a string
	 */
	public function member(): string|int {
		if (is_string($this->member)) {
			return self::unescape($this->member);
		} else {
			return $this->member;
		}
	}
}
