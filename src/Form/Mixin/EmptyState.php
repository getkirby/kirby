<?php

namespace Kirby\Form\Mixin;

trait EmptyState
{
	protected string|array|null $empty;

	protected function setEmpty(string|array|null $empty = null)
	{
		$this->empty = $this->i18n($empty);
	}

	public function empty(): string|null
	{
		return $this->stringTemplate($this->empty);
	}
}
