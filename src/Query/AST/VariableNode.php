<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a variable (e.g. an object)
 *
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class VariableNode extends IdentifierNode
{
	public function __construct(
		public string $name,
	) {
	}

	/**
	 * Replaces escaped dots with real dots
	 */
	public function name(): string
	{
		return self::unescape($this->name);
	}

	public function resolve(Visitor $visitor): mixed
	{
		$name = $this->name();
		return $visitor->variable($name);
	}
}
