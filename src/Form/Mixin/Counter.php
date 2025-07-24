<?php

namespace Kirby\Form\Mixin;

trait Counter
{
	/**
	 * The text to show before the counter
	 */
	protected bool $counter;

	public function counter(): bool
	{
		return $this->counter;
	}

	protected function setCounter(bool $counter = true): void
	{
		$this->counter = $counter;
	}
}
