<?php

namespace Kirby\Form\Mixin;

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
