<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `pattern` prop for regular expression validation
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Pattern
{
	/**
	 * A regular expression, which will be used to validate the input
	 */
	#[Prop]
	protected string|null $pattern = null;

	public function pattern(): string|null
	{
		return $this->pattern;
	}
}
