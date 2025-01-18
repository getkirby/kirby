<?php

namespace Kirby\Query\AST;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <>
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
}
