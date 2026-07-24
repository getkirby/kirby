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
	 * @param list<\Kirby\Text\Markdown\AST\Node>|null $children resolved child nodes
	 * @param string|list<string>|null $content unparsed source, resolved into $children
	 * @param $block the deferred $content is block-level source (parsed as
	 *              blocks, not inlines); its children each render on their
	 *              own line
	 * @param $multiline render $children with the line-break layout rules,
	 *                  rather than concatenated inline
	 * @param $break whether this node sits on its own line among its siblings
	 *              (defaults to true for named elements)
	 */
	public function __construct(
		public string|null $name,
		public array $attributes = [],
		public array|null $children = null,
		public string|array|null $content = null,
		public bool $block = false,
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
