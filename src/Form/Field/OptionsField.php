<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Options Field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class OptionsField extends InputField
{
	use Mixin\Max;
	use Mixin\Min;
	use Mixin\Options;

	protected mixed $value = [];

	public function __construct(
		bool|null $autofocus = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		array|string|null $label = null,
		int|null $max = null,
		int|null $min = null,
		string|null $name = null,
		array|string|null $options = null,
		bool|null $required = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default: $default,
			disabled: $disabled,
			help: $help,
			label: $label,
			name: $name,
			required: $required,
			translate: $translate,
			when: $when,
			width: $width
		);

		$this->max     = $max;
		$this->min     = $min;
		$this->options = $options;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'max'     => $this->max(),
			'min'     => $this->min(),
			'options' => $this->options(),
		];
	}

	protected function validations(): array
	{
		return [
			'options',
			'max',
			'min'
		];
	}
}
