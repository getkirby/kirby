<?php

namespace Kirby\Form\Mixin;

trait Counter
{
	/**
	 * Shows or hides the character counter in the top right corner
	 */
	protected bool $counter = true;

	public function counter(): bool
	{
		return $this->counter;
	}

	protected function setCounter(bool $counter): void
	{
		$this->counter = $counter;
	}
}
