<?php

namespace Kirby\Form\Mixin;

trait Layout
{
	/**
	 * Switch the layout for the field
	 */
	protected string|null $layout;

	public function layout(): string|null
	{
		return $this->layout;
	}
}
