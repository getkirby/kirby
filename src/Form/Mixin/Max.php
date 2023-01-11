<?php

namespace Kirby\Form\Mixin;

trait Max
{
	protected $max;

	public function max(): int|null
	{
		return $this->max;
	}

	protected function setMax(int $max = null)
	{
		$this->max = $max;
	}
}
