<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Structure;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Interface\ProvidesNestedForm;
use Kirby\Form\Mixin;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\Str;

/**
 * Allows editors to manage a list of structured items, each with its own set of fields
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class StructureField extends InputField implements ProvidesNestedForm
{
	use Mixin\Batch;
	use Mixin\Duplicate;
	use Mixin\EmptyState;
	use Mixin\Fields;
	use Mixin\Max;
	use Mixin\Min;
	use Mixin\Prepend;
	use Mixin\Sortable;
	use Mixin\SortBy;
	use Mixin\TableColumns;

	/**
	 * The number of entries that will be displayed on a single page. Afterwards pagination kicks in.
	 */
	#[Prop]
	protected int|null $limit = null;

	protected array $value = [];

	public function fill(mixed $value): static
	{
		if ($value instanceof Structure) {
			$value = $value->toArray(fn ($row) => $row->content()->toArray());
		}

		return parent::fill(
			value: Data::decode($value, 'yaml')
		);
	}

	public function limit(): int|null
	{
		return $this->limit;
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
							'field' => $field->label() ?? Str::label($field->name()),
							'index' => $index + 1
						]
					);
				}
			}
		}
	}
}
