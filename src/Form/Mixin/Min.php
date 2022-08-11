<?php

namespace Kirby\Form\Mixin;

trait Min
{
	protected $min;

	public function min(): ?int
	{
		return $this->min;
	}

	protected function setMin(int $min = null)
	{
		$this->min = $min;
	}
}
