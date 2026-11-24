<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Option Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class OptionField extends InputField
{
	use Mixin\Options;

	protected string $value = '';

	protected function validations(): array
	{
		return [
			'option'
		];
	}
}
