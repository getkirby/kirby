<?php

namespace Kirby\Form\Field;

/**
 * Hidden field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class HiddenField extends ValueField
{
	protected mixed $value = null;

	public function isHidden(): bool
	{
		return true;
	}

	public function props(): array
	{
		return [
			'hidden'   => $this->isHidden(),
			'name'     => $this->name(),
			'saveable' => $this->hasValue(),
			'type'     => $this->type(),
		];
	}
}
