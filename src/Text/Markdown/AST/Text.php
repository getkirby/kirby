<?php

namespace Kirby\Text\Markdown\AST;

/**
 * A literal text leaf.
 *
 * Its content is HTML-escaped on render (without escaping quotes).
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Text extends Node
{
	public function __construct(
		public string $text,
		bool|null $break = null
	) {
		parent::__construct($break);
	}
}
