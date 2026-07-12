<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\Text\Markdown\AST\Node;

/**
 * A Stack for inline runs that contain no emphasis
 * or bracket markers.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class FlatStack extends Stack
{
	/**
	 * @var list<\Kirby\Text\Markdown\AST\Node>
	 */
	protected array $flat = [];

	public function __construct()
	{
	}

	public function add(Node $node): void
	{
		$this->flat[] = $node;
	}

	/**
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	public function flatten(): array
	{
		return $this->flat;
	}
}
