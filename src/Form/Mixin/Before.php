<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `before` prop for rendering optional text before the field input
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Before
{
	/**
	 * Optional text that will be shown before the input
	 */
	#[Prop]
	protected array|string|null $before = null;

	public function before(): string|null
	{
		return $this->stringTemplateI18n($this->before);
	}
}
