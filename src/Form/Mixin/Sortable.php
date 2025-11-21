<?php

namespace Kirby\Form\Mixin;

trait Sortable
{
	/**
	 * If `true`, entries are sortable via drag & drop
	 */
	protected bool $sortable = true;

	public function sortable(): bool
	{
		return $this->sortable;
	}

	protected function setSortable(bool $sortable): void
	{
		$this->sortable = $sortable;
	}
}
