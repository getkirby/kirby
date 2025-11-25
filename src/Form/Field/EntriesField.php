<?php

namespace Kirby\Form\Field;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Form\Mixin\EmptyState;
use Kirby\Form\Mixin\Max;
use Kirby\Form\Mixin\Min;
use Kirby\Form\Mixin\Sortable;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Main class file of the entries field
 *
 * @package   Kirby Field
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 */
class EntriesField extends InputField
{
	use EmptyState;
	use Max;
	use Min;
	use Sortable;

	/**
	 * Field options for the field to be used and repeated.
	 * Supported field types are `color`, `date`, `email`, `number`, `select`,
	 * `slug`, `tel`, `text`, `time`, `url`.
	 */
	protected array|string|null $field;

	protected Form $form;
	protected mixed $value = [];

	public function __construct(
		bool|null $autofocus = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $empty = null,
		array|string|null $field = null,
		array|string|null $help = null,
		array|string|null $label = null,
		int|null $max = null,
		int|null $min = null,
		string|null $name = null,
		bool|null $required = null,
		bool|null $sortable = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default:   $default,
			disabled:  $disabled,
			help:      $help,
			label:     $label,
			name:      $name,
			required:  $required,
			translate: $translate,
			when:      $when,
			width:     $width
		);

		$this->setEmpty($empty);
		$this->setField($field);
		$this->setMax($max);
		$this->setMin($min);
		$this->setSortable($sortable);
	}

	public function field(): array
	{
		$props = $this->field;

		if (is_string($props) === true) {
			$props = ['type' => $props];
		}

		$props ??= ['type' => 'text'];

		if (in_array($props['type'], $this->supports(), true) === false) {
			throw new InvalidArgumentException(
				key: 'entries.supports',
				data: ['type' => $props['type']]
			);
		}

		// remove the unsupported props from the entry field
		unset($props['counter'], $props['label']);

		return $props;
	}

	public function fieldProps(): array
	{
		return $this->form()->fields()->first()->toArray();
	}

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 * @todo Remove psalm suppress after https://github.com/vimeo/psalm/issues/8673 is fixed
	 */
	public function fill(mixed $value): static
	{
		$this->value = Data::decode($value ?? '', 'yaml');
		return $this;
	}

	public function form(): Form
	{
		return $this->form ??= new Form(
			fields: [$this->field()],
			model:  $this->model()
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'empty'    => $this->empty(),
			'field'    => $this->fieldProps(),
			'max'      => $this->max(),
			'min'      => $this->min(),
			'sortable' => $this->sortable(),
		];
	}

	protected function setField(array|string|null $attrs): void
	{
		$this->field = $attrs;
	}

	public function supports(): array
	{
		return [
			'color',
			'date',
			'email',
			'number',
			'select',
			'slug',
			'tel',
			'text',
			'time',
			'url'
		];
	}

	public function toFormValue(): mixed
	{
		$form  = $this->form();
		$value = parent::toFormValue() ?? $this->emptyValue();

		return A::map(
			$value,
			fn ($value) => $form
				->reset()
				->fill(input: [$value])
				->fields()
				->first()
				->toFormValue()
		);
	}

	public function toStoredValue(): mixed
	{
		$form  = $this->form();
		$value = parent::toStoredValue();

		return A::map(
			$value,
			fn ($value) => $form
				->reset()
				->submit(input: [$value])
				->fields()
				->first()
				->toStoredValue()
		);
	}

	public function validations(): array
	{
		return [
			'entries' => function ($value) {
				if ($this->min && count($value) < $this->min) {
					throw new InvalidArgumentException(
						key: match ($this->min) {
							1       => 'entries.min.singular',
							default => 'entries.min.plural'
						},
						data: ['min' => $this->min]
					);
				}

				if ($this->max && count($value) > $this->max) {
					throw new InvalidArgumentException(
						key: match ($this->max) {
							1       => 'entries.max.singular',
							default => 'entries.max.plural'
						},
						data: ['max' => $this->max]
					);
				}

				$form = $this->form();

				foreach ($value as $index => $val) {
					$form->reset()->submit(input: [$val]);

					foreach ($form->fields() as $field) {
						$errors = $field->errors();

						if ($errors !== []) {
							throw new InvalidArgumentException(
								key: 'entries.validation',
								data: [
									'field' => $this->label() ?? Str::ucfirst($this->name()),
									'index' => $index + 1
								]
							);
						}
					}
				}
			}
		];
	}
}
