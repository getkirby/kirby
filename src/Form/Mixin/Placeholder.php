<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `placeholder` prop for input placeholder text
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Placeholder
{
	/**
	 * Optional placeholder value that will be shown when the field is empty
	 */
	protected array|string|null $placeholder;

	public function placeholder(): string|null
	{
		return $this->stringTemplateI18n($this->placeholder);
	}
}
