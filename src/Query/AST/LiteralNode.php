<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents literal values (e.g. string, int, bool) in the AST
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 * @unstable
 */
class LiteralNode extends Node
{
	public function __construct(
		public mixed $value,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		return $visitor->literal($this->value);
	}
}
