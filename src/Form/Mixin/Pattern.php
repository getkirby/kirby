<?php

namespace Kirby\Form\Mixin;

trait Pattern
{
	/**
	 * A regular expression, which will be used to validate the input
	 */
	protected string|null $pattern;

	public function pattern(): string|null
	{
		return $this->pattern;
	}

	protected function setPattern(string|null $pattern): void
	{
		$this->pattern = $pattern;
	}
}
