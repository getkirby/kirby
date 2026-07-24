<?php

namespace Kirby\Text\Markdown;

/**
 * Base for a single Markdown component,
 * block- or inline/span-level.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class Component
{
	public function __construct(
		protected Parser $parser
	) {
	}

	/**
	 * The characters that should trigger
	 * calling this component's `::consume()` method.
	 *
	 * @return list<string>
	 */
	abstract public static function markers(): array;

	protected function data(): Data
	{
		return $this->parser->data();
	}
}
