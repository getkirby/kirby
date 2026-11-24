<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `after` prop for rendering optional text after the field input
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait After
{
	/**
	 * Optional text that will be shown after the input
	 */
	#[Prop]
	protected array|string|null $after = null;

	public function after(): string|null
	{
		return $this->stringTemplateI18n($this->after);
	}
}
