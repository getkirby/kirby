<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\HasSiblings;
use Kirby\Form\Fields;

/**
 * Provides sibling field context for field-to-field interactions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Siblings
{
	use HasSiblings;

	protected Fields|null $siblings = null;

	protected function siblingsCollection(): Fields
	{
		return $this->siblings ?? new Fields([$this]);
	}

	public function setSiblings(Fields|null $siblings = null): static
	{
		$this->siblings = $siblings;
		return $this;
	}
}
