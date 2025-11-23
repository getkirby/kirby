<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\HasSiblings;
use Kirby\Form\Fields;

trait Siblings
{
	use HasSiblings;

	protected Fields|null $siblings = null;

	protected function siblingsCollection(): Fields
	{
		return $this->siblings ?? new Fields([$this]);
	}

	protected function setSiblings(Fields|null $siblings = null): void
	{
		$this->siblings = $siblings;
	}
}
