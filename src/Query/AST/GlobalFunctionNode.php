<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class GlobalFunctionNode extends Node
{
	public function __construct(
		public string $name,
		public ArgumentListNode $arguments,
	) {
	}

	public function resolve(Visitor $visitor): mixed
	{
		$arguments = $this->arguments->resolve($visitor);
		return $visitor->function($this->name, $arguments);
	}
}
