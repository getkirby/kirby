<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Checkboxes Field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class CheckboxesField extends OptionsField
{
	use Mixin\Batch;
	use Mixin\Columns;

	public function __construct(
		bool|null $autofocus = null,
		bool|null $batch = null,
		int|null $columns = null,
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
			max: $max,
			min: $min,
			name: $name,
			options: $options,
			required: $required,
			translate: $translate,
			when: $when,
			width: $width
		);

		$this->batch   = $batch;
		$this->columns = $columns;
	}

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 * @todo Remove psalm suppress after https://github.com/vimeo/psalm/issues/8673 is fixed
	 */
	public function fill(mixed $value): static
	{
		$this->value = Str::split($value, ',');
		return $this;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'batch'   => $this->batch(),
			'columns' => $this->columns(),
		];
	}

	public function toStoredValue(): mixed
	{
		return A::join($this->value, ', ');
	}
}
