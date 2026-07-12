<?php

namespace Kirby\Text\Markdown\Inline;

/**
 * Strikethrough span
 *
 * @example
 * This is ~~struck~~.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Strikethrough extends DelimitedInline
{
	public static function markers(): array
	{
		return ['~'];
	}

	/**
	 * A matched pair of double tildes pairs,
	 * wrapping its content in `<del>`
	 */
	protected function tags(): array
	{
		return [2 => 'del'];
	}
}
