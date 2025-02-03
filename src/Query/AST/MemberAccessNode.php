<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents the access (e.g. method call) on a node
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
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
		$object    = $this->object->resolve($visitor);
		$arguments = $this->arguments?->resolve($visitor);
		$member    = $this->member->resolve($visitor);

		return $visitor->memberAccess(
			$object,
			$member,
			$arguments,
			$this->nullSafe
		);
	}
}
