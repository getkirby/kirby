<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * String Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class StringField extends InputField
{
	use Mixin\Maxlength;
	use Mixin\Minlength;
	use Mixin\Placeholder;
	use Mixin\Spellcheck;

	protected string $value = '';

	protected function validations(): array
	{
		return [
			'minlength',
			'maxlength'
		];
	}
}
