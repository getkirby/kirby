<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Options Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class OptionsField extends InputField
{
	use Mixin\Max;
	use Mixin\Min;
	use Mixin\Options;

	protected array $value = [];

	protected function validations(): array
	{
		return [
			'options',
			'max',
			'min'
		];
	}
}
