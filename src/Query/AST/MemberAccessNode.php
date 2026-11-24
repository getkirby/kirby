<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents the access (e.g. method call) on a node in the AST
 *
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 *
 * @unstable
 */
class MemberAccessNode extends Node
{
	public function __construct(
		public Node $object,
		public Node $member,
		public ArgumentListNode|null $arguments = null,
		public bool $nullSafe = false,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->memberAccess(
			object:    $this->object->resolve($visitor),
			member:    $this->member->resolve($visitor),
			arguments: $this->arguments?->resolve($visitor),
			nullSafe:  $this->nullSafe
		);
	}
}
