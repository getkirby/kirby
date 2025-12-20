<?php

namespace Kirby\Form\Mixin;

use Kirby\Toolkit\Str;

trait Label
{
	/**
	 * The field label can be set as string or associative array with translations
	 */
	protected array|string|null $label;

	public function label(): string|null
	{
		if ($this->label === null || $this->label === []) {
			return Str::ucfirst($this->name());
		}

		return $this->stringTemplateI18n($this->label);
	}
}
