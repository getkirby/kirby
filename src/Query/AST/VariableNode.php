<?php

namespace Kirby\Query\AST;

use Closure;
use Kirby\Query\Visitors\CodeGen;
use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Visitor;

/**
 * Represents a variable (e.g. an object)
 *
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

	public function resolve(Visitor $visitor): mixed
	{
		$name = $this->name();
		return $visitor->variable($name);
	}
}
