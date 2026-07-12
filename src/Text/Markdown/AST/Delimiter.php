<?php

namespace Kirby\Text\Markdown\AST;

use Kirby\Text\Markdown\Inline\DelimitedInline;

/**
 * A delimiter run emitted while scanning inline inlines.
 *
 * Represents an entry on our delimiter stack: the run's
 * marker, its (mutable) length, and whether it can open and/or close.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Delimiter extends Node
{
	/**
	 * The run's length before any pairing consumed it,
	 * kept for the "rule of three".
	 */
	public readonly int $original;

	public function __construct(
		public readonly DelimitedInline $inline,
		public readonly string $marker,
		public int $length,
		public readonly bool $canOpen,
		public readonly bool $canClose,
		public readonly bool $intrawordBefore = false,
		public readonly bool $intrawordAfter = false
	) {
		$this->original = $length;
		parent::__construct(break: false);
	}
}
