<?php

namespace Kirby\Text\Markdown\AST;

use Kirby\Exception\InvalidArgumentException;

/**
 * A generic Markdown component that produces HTML.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Element extends Node
{
	/**
	 * @param array<string, string|null> $attributes
	 * @param list<Node>|null $children resolved child nodes
	 * @param string|list<string>|null $content unparsed source, resolved into $children
	 * @param $block parse $content as block-level source, not inline spans
	 * @param list<class-string>|bool $omit mark types to skip in inline $content, or true to drop a tight block's wrapping `<p>`
	 * @param $multiline render $children each on their own line (block layout)
	 */
	public function __construct(
		public string|null $name,
		public array $attributes = [],
		public array|null $children = null,
		public string|array|null $content = null,
		public bool $block = false,
		public array|bool $omit = [],
		public bool $multiline = false,
		bool|null $break = null
	) {
		if ($content !== null && $children !== null) {
			throw new InvalidArgumentException(
				message: 'An element cannot carry both content and children'
			);
		}

		parent::__construct($break);
	}

	/**
	 * Whether this node introduces a line break when rendered
	 * inside a sibling list. A `null` tag name renders the children
	 * without a surrounding tag (as a fragment).
	 */
	public function hasBreak(): bool
	{
		return $this->break ?? ($this->name !== null);
	}
}
