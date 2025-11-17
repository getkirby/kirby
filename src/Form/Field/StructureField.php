<?php

namespace Kirby\Form\Field;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field;
use Kirby\Form\FieldClass;
use Kirby\Form\Form;
use Kirby\Form\Mixin\EmptyState;
use Kirby\Form\Mixin\Min;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

class StructureField extends FieldClass
{
	use EmptyState;
	use Min;

	protected Form $form;

	/**
	 * @param $batch Whether to enable batch editing
	 * @param $columns Optional columns definition to only show selected fields in the structure table.
	 * @param $default Set the default rows for the structure
	 * @param $duplicate Toggles duplicating rows for the structure
	 * @param $placeholder The placeholder text if no items have been added yet
	 * @param $fields Fields setup for the structure form. Works just like fields in regular forms.
	 * @param $limit The number of entries that will be displayed on a single page. Afterwards pagination kicks in.
	 * @param $max Maximum allowed entries in the structure. Afterwards the "Add" button will be switched off.
	 * @param $min Minimum required entries in the structure
	 * @param $prepend Toggles adding to the top or bottom of the list
	 * @param $sortable Toggles drag & drop sorting
	 * @param $sortBy Sorts the entries by the given field and order (i.e. `title desc`) Drag & drop is disabled in this case
	 */
	public function __construct(
		protected string $name,
		protected bool $batch = false,
		protected array $columns = [],
		protected mixed $default = [],
		protected bool $disabled = false,
		protected bool $duplicate = true,
		protected array|string|null $empty = null,
		protected array $fields = [],
		protected array|string|null $help = null,
		protected array|string|null $label = null,
		protected int|null $limit = null,
		protected int|null $max = null,
		protected int|null $min = null,
		protected bool $prepend = false,
		protected bool $required = false,
		protected bool $sortable = false,
		protected string|null $sortBy = null,
		protected bool $translate = true,
		protected array|null $when = null,
		protected string|null $width = null,
		protected mixed $value = [],
	) {
		$this->fill($value);
	}

	public function columns(): array
	{
		$columns   = [];
		$blueprint = array_change_key_case($this->columns);
		$fields    = $this->fields();

		// if no custom columns have been defined,
		// gather all fields as columns
		if ($blueprint === []) {
			// skip hidden fields
			$availableFields = array_filter(
				$fields,
				fn ($field) =>
					$field['type'] !== 'hidden' && $field['hidden'] !== true
			);
			$availableFields = array_column($availableFields, 'name');
			$blueprint       = array_fill_keys($availableFields, true);
		}

		foreach ($blueprint as $name => $column) {
			$field = $fields[$name] ?? null;

			// Skip empty and unsaveable fields
			// They should never be included as column
			if (
				empty($field) === true ||
				$field['saveable'] === false
			) {
				continue;
			}

			if (is_array($column) === false) {
				$column = [];
			}

			$column['type']  ??= $field['type'];
			$column['label'] ??= $field['label'] ?? $name;
			$column['label']   = I18n::translate($column['label'], $column['label']);

			$columns[$name] = $column;
		}

		// make the first column visible on mobile
		// if no other mobile columns are defined
		if (in_array(true, array_column($columns, 'mobile'), true) === false) {
			$columns[array_key_first($columns)]['mobile'] = true;
		}

		return $columns;
	}

	public function default(): array
	{
		return $this->rows($this->default);
	}

	public function fields(): array
	{
		if ($this->fields === []) {
			return [];
		}

		return $this->form()->fields()->toProps();
	}

	public function form(): Form
	{
		$this->form ??= new Form(
			fields: $this->fields,
			model: $this->model(),
			language: 'current'
		);

		return $this->form->reset();
	}

	public function props(): array
	{
		return [
			'batch'     => $this->batch(),
			'columns'   => $this->columns(),
			'default'   => $this->default(),
			'disabled'  => $this->isDisabled(),
			'duplicate' => $this->duplicate(),
			'empty'     => $this->empty(),
			'fields'    => $this->fields(),
			'help'      => $this->help(),
			'hidden'    => false,
			'label'     => $this->label(),
			'limit'     => $this->limit(),
			'max'       => $this->max(),
			'min'       => $this->min(),
			'name'      => $this->name(),
			'prepend'   => $this->prepend(),
			'required'  => $this->isRequired(),
			'saveable'  => true,
			'sortable'  => $this->sortable(),
			'sortBy'    => $this->sortBy(),
			'translate' => $this->translate(),
			'type'      => $this->type(),
			'when'      => $this->when(),
			'width'     => $this->width(),
		];
	}

	public function rows(array|string $value): array
	{
		$rows  = Data::decode($value, 'yaml');
		$value = [];

		foreach ($rows as $row) {
			if (is_array($row) === false) {
				continue;
			}

			$value[] = $this->form()->fill(input: $row, passthrough: true)->toFormValues();
		}

		return $value;
	}

	protected function throwFieldException(Field|FieldClass $field, int $index)
	{
		throw new InvalidArgumentException(
			key: 'structure.validation',
			data: [
				'field' => $field->label() ?? Str::ucfirst($field->name()),
				'index' => $index
			]
		);
	}

	public function toFormValue(): array
	{
		return $this->rows($this->value ?? []);
	}

	public function toStoredValue(): mixed
	{
		$data     = [];
		$form     = $this->form();
		$defaults = $form->defaults();

		foreach ($this->value as $index => $row) {
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

	public function type(): string
	{
		return 'structure';
	}

	public function validations(): array
	{
		return [
			'min',
			'max',
			'structure' => function (array $value) {
				if ($this->isEmptyValue($value) === true) {
					return true;
				}

				foreach ($value as $index => $value) {
					$form = $this->form();
					$form->fill(input: $value);

					foreach ($form->fields() as $field) {
						if ($field->errors() !== []) {
							$this->throwFieldException($field, $index + 1);
						}
					}
				}
			}
		];
	}

}
