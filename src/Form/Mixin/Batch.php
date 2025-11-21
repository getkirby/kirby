<?php

namespace Kirby\Form\Mixin;

trait Batch
{
	/**
	 * Show/hide the batch select interface
	 */
	protected bool $batch = false;

	public function batch(): bool
	{
		return $this->batch;
	}

	protected function setBatch(bool $batch): void
	{
		$this->batch = $batch;
	}
}
