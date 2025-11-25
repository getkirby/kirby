<?php

namespace Kirby\Form\Field;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Mixin;
use Kirby\Toolkit\Str;

class StructureField extends InputField
{
	use Mixin\Batch;
	use Mixin\EmptyState;
	use Mixin\Fields;
	use Mixin\Limit;
	use Mixin\Max;
	use Mixin\Min;
	use Mixin\Sortable;
	use Mixin\SortBy;
	use Mixin\TableColumns;

	/**
	 * Toggles duplicating rows for the structure
	 */
	protected bool|null $duplicate;

	protected mixed $value = [];

	public function __construct(
		bool|null $batch = null,
		array|null $columns = null,
		array|null $default = null,
		bool|null $disabled = null,
		array|null $duplicate = null,
		array|string|null $empty = null,
		array|null $fields = null,
		array|string|null $help = null,
		array|string|null $label = null,
		int|null $limit = null,
		string|null $name = null,
		int|null $max = null,
		int|null $min = null,
		bool|null $required = null,
		bool|null $sortable = null,
		string|null $sortBy = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null,
	) {
		parent::__construct(
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

		$this->batch     = $batch;
		$this->columns   = $columns;
		$this->duplicate = $duplicate;
		$this->empty     = $empty;
		$this->fields    = $fields;
		$this->limit     = $limit;
		$this->max       = $max;
		$this->min       = $min;
		$this->sortable  = $sortable;
		$this->sortBy    = $sortBy;
	}

	public function duplicate(): bool
	{
		return $this->duplicate ?? true;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'batch'     => $this->batch(),
			'columns'   => $this->columns(),
			'duplicate' => $this->duplicate(),
			'empty'     => $this->empty(),
			'fields'    => $this->fields(),
			'limit'     => $this->limit(),
			'max'       => $this->max(),
			'min'       => $this->min(),
			'sortable'  => $this->sortable(),
			'sortBy'    => $this->sortBy()
		];
	}

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 * @todo Remove psalm suppress after https://github.com/vimeo/psalm/issues/8673 is fixed
	 */
	public function fill(mixed $value): static
	{
		$this->value = Data::decode($value, 'yaml');
		return $this;
	}

	public function toFormValue(): array
	{
		$data = [];

		foreach ($this->value as $row) {
			$data[] = $this->form()
				->fill(
					input: $row,
					passthrough: true
				)
				->toFormValues();
		}

		return $data;
	}

	public function toStoredValue(): array
	{
		$data     = [];
		$form     = $this->form();
		$defaults = $form->defaults();

		foreach ($this->value as $row) {
			$row = $form
				->reset()
				->fill(
					input: $defaults,
				)
				->submit(
					input: $row,
					passthrough: true
				)
				->toStoredValues();

			// remove frontend helper id
			unset($row['_id']);

			$data[] = $row;
		}

		return $data;
	}

	protected function validations(): array
	{
		return [
			'min',
			'max',
			'structure' => $this->validateRows(...)
		];
	}

	protected function validateRows(array $value): void
	{
		if ($value === []) {
			return;
		}

		foreach ($this->value as $index => $value) {
			$form = $this->form()->fill(input: $value);

			foreach ($form->fields() as $field) {
				$errors = $field->errors();

				if ($errors !== []) {
					throw new InvalidArgumentException(
						key: 'structure.validation',
						data: [
							'field' => $field->label() ?? Str::ucfirst($field->name()),
							'index' => $index + 1
						]
					);
				}
			}
		}
	}
}
