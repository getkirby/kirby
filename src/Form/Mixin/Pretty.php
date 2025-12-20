<?php

namespace Kirby\Form\Mixin;

trait Pretty
{
	/**
	 * Saves pretty printed JSON in text files
	 */
	protected bool|null $pretty;

	public function pretty(): bool
	{
		return $this->pretty ?? false;
	}
}
