<?php

namespace Kirby\Form\Mixin;

trait Pattern
{
	/**
	 * The pattern to validate the field value against.
	 */
	protected string|null $pattern;

	public function pattern(): string|null
	{
		return $this->pattern;
	}

	protected function setPattern(string|null $pattern = null): void
	{
		$this->pattern = $pattern;
	}
}
