<?php

namespace Kirby\Form\Mixin;

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
	protected array|string|null $after;

	public function after(): string|null
	{
		return $this->stringTemplateI18n($this->after);
	}
}
