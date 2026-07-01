<?php

namespace Kirby\Text\Markdown\AST;

/**
 * A raw HTML leaf.
 *
 * Its content is emitted verbatim, except in safe mode
 * where it is escaped unless the content is `$trusted`
 * (e.g. library-generated HTML entities).
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Html extends Node
{
	public function __construct(
		public string $html,
		public readonly bool $trusted = false,
		bool|null $break = null
	) {
		parent::__construct($break);
	}
}
