<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `separator` prop for custom list value storage
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Separator
{
	/**
	 * Custom separator, which will be used to store a list of values in the content file
	 */
	protected string|null $separator;

	public function separator(): string
	{
		return $this->separator ?? ',';
	}
}
