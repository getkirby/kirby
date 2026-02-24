<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `empty` prop to customize the empty state message
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait EmptyState
{
	/**
	 * Sets the text for the empty state box
	 */
	protected array|string|null $empty;

	public function empty(): string|null
	{
		return $this->stringTemplateI18n($this->empty);
	}
}
