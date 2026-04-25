<?php

namespace Kirby\Form\Mixin;

use Kirby\Toolkit\Str;

/**
 * Provides the `label` prop with translation support
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Label
{
	/**
	 * The field label can be set as string or associative array with translations
	 */
	protected array|string|null $label;

	public function label(): string|null
	{
		if ($this->label === null || $this->label === []) {
			return Str::label($this->name());
		}

		return $this->stringTemplateI18n($this->label);
	}
}
