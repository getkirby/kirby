<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class ClosureNode extends Node
{
	/**
	 * @param string[] $arguments The arguments names
	 */
	public function __construct(
		public array $arguments,
		public Node $body,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->closure($this);
	}
}
