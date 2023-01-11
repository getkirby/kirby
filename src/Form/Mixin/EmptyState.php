<?php

namespace Kirby\Form\Mixin;

trait EmptyState
{
	protected $empty;

	protected function setEmpty($empty = null)
	{
		$this->empty = $this->i18n($empty);
	}

	public function empty(): string|null
	{
		return $this->stringTemplate($this->empty);
	}
}
