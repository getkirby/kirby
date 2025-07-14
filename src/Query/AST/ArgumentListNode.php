<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Represents a list of (method) arguments in the AST
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 * @unstable
 */
class ArgumentListNode extends Node
{
	public function __construct(
		public array $arguments = []
	) {
	}

	public function resolve(Visitor $visitor): array|string
	{
		// Resolve each argument
		$arguments = array_map(
			fn ($argument) => $argument->resolve($visitor),
			$this->arguments
		);

		// Keep as array or convert to string
		// depending on the visitor type
		return $visitor->arguments($arguments);
	}
}
