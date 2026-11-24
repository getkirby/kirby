<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `batch` prop to show or hide the batch select interface
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Batch
{
	/**
	 * Show/hide the batch select interface
	 */
	#[Prop]
	protected bool $batch = false;

	public function batch(): bool
	{
		return $this->batch;
	}
}
