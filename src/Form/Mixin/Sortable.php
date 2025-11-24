<?php

namespace Kirby\Form\Mixin;

trait Sortable
{
	/**
	 * If `true`, entries are sortable via drag & drop
	 */
	protected bool|null $sortable;

	public function sortable(): bool
	{
		return $this->sortable ?? true;
	}

	protected function setSortable(bool|null $sortable): void
	{
		$this->sortable = $sortable;
	}
}
