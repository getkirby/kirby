<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Checkboxes Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class CheckboxesField extends OptionsField
{
	use Mixin\Batch;
	use Mixin\Columns;

	public function fill(mixed $value): static
	{
		return parent::fill(
			value: Str::split($value, ',')
		);
	}

	public function toStoredValue(): mixed
	{
		return A::join($this->value, ', ');
	}
}
