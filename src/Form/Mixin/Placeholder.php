<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

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
	#[Prop]
	protected array|string|null $placeholder = null;

	public function placeholder(): string|null
	{
		return $this->stringTemplateI18n($this->placeholder);
	}
}
