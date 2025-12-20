<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Radio Field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class RadioField extends OptionField
{
	use Mixin\Columns;

	public function __construct(
		bool|null $autofocus = null,
		int|null $columns = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		array|string|null $label = null,
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
			options: $options,
			required: $required,
			translate: $translate,
			when: $when,
			width: $width
		);

		$this->columns = $columns;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'columns' => $this->columns(),
		];
	}
}
