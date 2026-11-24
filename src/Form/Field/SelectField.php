<?php

namespace Kirby\Form\Field;

use Kirby\Form\FieldOptions;
use Kirby\Form\Mixin;
use Kirby\Reflection\Attributes\Prop;

/**
 * Select Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SelectField extends OptionField
{
	use Mixin\Icon;

	/**
	 * Text shown when no option is selected yet
	 */
	#[Prop]
	protected array|string|null $placeholder = '—';

	protected function fieldOptions(): FieldOptions
	{
		$props = FieldOptions::polyfill(['options' => $this->options ?? []]);

		// disable safe mode as the select field does not
		// render HTML for the option text
		return FieldOptions::factory($props['options'], safeMode: false);
	}

	public function placeholder(): string|null
	{
		return $this->stringTemplateI18n($this->placeholder);
	}
}
