<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents literal values (e.g. string, int, bool)
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
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
