<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\Parser\Attributes;

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
	use Attributes;

	public function __construct(
		protected Parser $parser
	) {
	}

	protected function data(): Data
	{
		return $this->parser->data();
	}

	/**
	 * The characters that should trigger
	 * calling this component's `::consume()` method.
	 *
	 * @return list<string>
	 */
	abstract public static function markers(): array;
}
