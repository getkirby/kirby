<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents the access (e.g. method call) on a node
 *
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class MemberAccessNode extends IdentifierNode
{
	public function __construct(
		public Node $object,
		public string|int $member,
		public ArgumentListNode|null $arguments = null,
		public bool $nullSafe = false,
	) {
	}

	/**
	 * Returns the member name and replaces escaped dots
	 * with real dots if it's a string
	 */
	public function member(): string|int
	{
		if (is_string($this->member) === true) {
			return self::unescape($this->member);
		}

		return $this->member;
	}

	public function resolve(Visitor $visitor): mixed
	{
		$object    = $this->object->resolve($visitor);
		$arguments = $this->arguments?->resolve($visitor);

		return $visitor->memberAccess(
			$object,
			$arguments,
			$this->member,
			$this->nullSafe
		);
	}
}
