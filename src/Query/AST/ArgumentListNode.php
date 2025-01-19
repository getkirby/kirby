<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a list of (method) arguments
 *
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class ArgumentListNode extends Node
{
	public function __construct(
		public array $arguments = []
	) {
	}

	public function resolve(Visitor $visitor): array|string
	{
		$arguments = array_map(
			fn ($argument) => $argument->resolve($visitor),
			$this->arguments
		);

		return $visitor->argumentList($arguments);
	}
}
