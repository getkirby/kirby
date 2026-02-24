<?php

namespace Kirby\Form\Mixin;

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
	protected string|null $pattern;

	public function pattern(): string|null
	{
		return $this->pattern;
	}
}
