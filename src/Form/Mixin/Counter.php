<?php

namespace Kirby\Form\Mixin;

trait Counter
{
	/**
	 * Shows or hides the character counter in the top right corner
	 */
	protected bool|null $counter;

	public function counter(): bool
	{
		return $this->counter ?? true;
	}

	protected function setCounter(bool|null $counter): void
	{
		$this->counter = $counter;
	}
}
