<?php

namespace Kirby\Form\Mixin;

trait Min
{
	protected int|null $min;

	public function min(): int|null
	{
		return $this->min;
	}

	protected function setMin(int|null $min = null)
	{
		$this->min = $min;
	}
}
