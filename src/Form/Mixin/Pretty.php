<?php

namespace Kirby\Form\Mixin;

trait Pretty
{
	/**
	 * Saves pretty printed JSON in text files
	 */
	protected bool $pretty = false;

	public function pretty(): bool
	{
		return $this->pretty;
	}

	protected function setPretty(bool $pretty): void
	{
		$this->pretty = $pretty;
	}
}
