<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Text Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TextField extends StringField
{
	use Mixin\After;
	use Mixin\Autocomplete;
	use Mixin\Before;
	use Mixin\Converter;
	use Mixin\Counter;
	use Mixin\Font;
	use Mixin\Icon;
	use Mixin\Pattern;

	public function default(): string
	{
		return $this->convert(value: parent::default()) ?? '';
	}

	public function fill(mixed $value): static
	{
		return parent::fill(
			value: $this->convert($value) ?? ''
		);
	}

	protected function validations(): array
	{
		return [
			...parent::validations(),
			'pattern'
		];
	}
}
