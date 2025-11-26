<?php

namespace Kirby\Form\Mixin;

trait Separator
{
	/**
	 * Custom separator, which will be used to store a list of values in the content file
	 */
	protected string|null $separator;

	public function separator(): string
	{
		return $this->separator ?? ',';
	}
}
