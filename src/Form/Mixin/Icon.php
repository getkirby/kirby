<?php

namespace Kirby\Form\Mixin;

trait Icon
{
	/**
	 * Optional icon that will be shown at the end of the field
	 */
	protected string|null $icon;

	public function icon(): string|null
	{
		return $this->icon;
	}
}
