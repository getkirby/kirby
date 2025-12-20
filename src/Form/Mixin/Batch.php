<?php

namespace Kirby\Form\Mixin;

trait Batch
{
	/**
	 * Show/hide the batch select interface
	 */
	protected bool|null $batch;

	public function batch(): bool
	{
		return $this->batch ?? false;
	}
}
