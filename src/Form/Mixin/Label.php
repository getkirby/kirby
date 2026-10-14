<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;
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
	#[Prop(computed: true)]
	protected array|string|null $label = null;

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return Str::label($this->name());
		}

		return $this->stringTemplateI18n($this->label);
	}
}
