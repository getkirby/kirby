<?php

namespace Kirby\Form\Field;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field;
use Kirby\Form\Form;
use Kirby\Form\Interface\ProvidesNestedForm;
use Kirby\Form\Mixin;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\A;
use Kirby\Toolkit\BlockCollectionAccess;
use Kirby\Toolkit\I18n;

/**
 * Allows editors to define a list of conditions,
 * each consisting of a field, an operator and a value,
 * e.g. for `when` clauses or complex filters.
 *
 * The operators are stored with the same syntax as
 * the operators of `Kirby\Toolkit\Collection::filter()`
 * so the conditions can be passed on directly.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class FilterField extends InputField implements ProvidesNestedForm
{
	use Mixin\EmptyState;
	use Mixin\Max;
	use Mixin\Min;

	/**
	 * Fields that can be matched in a condition. The field
	 * definition is used to render the value input of the condition.
	 * Supported field types are `color`, `date`, `email`, `number`,
	 * `select`, `slug`, `tel`, `text`, `time`, `url`.
	 */
	#[Prop]
	protected array $fields = [];
	protected Form|null $form = null;

	/**
	 * Limits the available operators to the given list,
	 * e.g. `['==', '!=']`. By default, all operators are available.
	 */
	#[Prop(getter: 'operatorOptions')]
	protected array|null $operators = null;
	protected array $value = [];

	/**
	 * Returns the props of all fields that
	 * can be matched in a condition
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function fields(): array
	{
		if ($this->fields === []) {
			return [];
		}

		return $this->form()->fields()->toProps(defaults: true);
	}

	#[BlockCollectionAccess]
	public function fill(mixed $value): static
	{
		return parent::fill(
			value: $this->normalize($value)
		);
	}

	/**
	 * Creates the nested form for all fields
	 * that can be matched in a condition
	 *
	 * @throws InvalidArgumentException If a field type is not supported
	 */
	public function form(): Form
	{
		if ($this->form === null) {
			$this->form = new Form(
				fields:   $this->fields,
				model:    $this->model(),
				language: 'current'
			);

			foreach ($this->form->fields() as $field) {
				if (in_array($field->type(), $this->supports(), true) === false) {
					throw new InvalidArgumentException(
						key: 'filter.supports',
						data: ['type' => $field->type()]
					);
				}
			}
		}

		return $this->form->reset();
	}

	/**
	 * Normalizes the given value to a list
	 * of conditions with the keys `field`,
	 * `operator` and `value`
	 */
	protected function normalize(mixed $value): array
	{
		$value      = Data::decode($value ?? '', 'yaml');
		$conditions = [];

		foreach ($value as $condition) {
			if (is_array($condition) === false) {
				continue;
			}

			$conditions[] = [
				'field'    => $condition['field'] ?? '',
				'operator' => $condition['operator'] ?? A::first($this->operators()),
				'value'    => $condition['value'] ?? '',
			];
		}

		return $conditions;
	}

	/**
	 * Returns the operators with their labels
	 * for the operator select of each condition
	 *
	 * @return list<array{value: string, text: string}>
	 */
	public function operatorOptions(): array
	{
		$labels = $this->supportedOperators();

		return A::map(
			$this->operators(),
			fn ($operator) => [
				'value' => $operator,
				'text'  => I18n::translate('field.filter.operator.' . $labels[$operator])
			]
		);
	}

	/**
	 * Returns all available operators
	 *
	 * @return list<string>
	 * @throws InvalidArgumentException If an operator is not supported
	 */
	public function operators(): array
	{
		$supported = array_keys($this->supportedOperators());

		if ($this->operators === null || $this->operators === []) {
			return $supported;
		}

		foreach ($this->operators as $operator) {
			if (in_array($operator, $supported, true) === false) {
				throw new InvalidArgumentException(
					key: 'filter.operator',
					data: ['operator' => $operator]
				);
			}
		}

		return array_values($this->operators);
	}

	/**
	 * Returns all supported operators with the
	 * key of their translated label
	 *
	 * @return array<string, string>
	 */
	public function supportedOperators(): array
	{
		return [
			'=='  => 'is',
			'!='  => 'is.not',
			'*='  => 'contains',
			'!*=' => 'contains.not',
			'^='  => 'starts',
			'!^=' => 'starts.not',
			'$='  => 'ends',
			'!$=' => 'ends.not',
			'>'   => 'greater',
			'>='  => 'greater.equal',
			'<'   => 'less',
			'<='  => 'less.equal',
		];
	}

	/**
	 * Returns the field types that can be matched in a condition
	 *
	 * @return list<string>
	 */
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

	/**
	 * Returns the nested field for the given name
	 * with a fresh value
	 */
	protected function target(Form $form, string $name): Field|null
	{
		if ($name === '') {
			return null;
		}

		return $form->fields()->findByKey($name)?->reset();
	}

	/**
	 * @return list<array{field: string, operator: string, value: mixed}>
	 */
	public function toFormValue(): array
	{
		$form = $this->form();

		return A::map(
			$this->value,
			function (array $condition) use ($form) {
				if ($field = $this->target($form, $condition['field'])) {
					$condition['value'] = $field->fill($condition['value'])->toFormValue();
				}

				return $condition;
			}
		);
	}

	/**
	 * @return list<array{field: string, operator: string, value: mixed}>
	 */
	public function toStoredValue(): array
	{
		$form = $this->form();

		return A::map(
			$this->value,
			function (array $condition) use ($form) {
				if ($field = $this->target($form, $condition['field'])) {
					$condition['value'] = $field->submit($condition['value'])->toStoredValue();
				}

				return $condition;
			}
		);
	}

	protected function validateConditions(array $value): void
	{
		if ($this->min && count($value) < $this->min) {
			throw new InvalidArgumentException(
				key: match ($this->min) {
					1       => 'filter.min.singular',
					default => 'filter.min.plural'
				},
				data: ['min' => $this->min]
			);
		}

		if ($this->max && count($value) > $this->max) {
			throw new InvalidArgumentException(
				key: match ($this->max) {
					1       => 'filter.max.singular',
					default => 'filter.max.plural'
				},
				data: ['max' => $this->max]
			);
		}

		$form      = $this->form();
		$operators = $this->operators();

		foreach ($value as $index => $condition) {
			$field = $this->target($form, $condition['field'] ?? '');

			if (
				$field === null ||
				in_array($condition['operator'] ?? null, $operators, true) === false ||
				$field->submit($condition['value'] ?? '')->errors() !== []
			) {
				throw new InvalidArgumentException(
					key: 'filter.validation',
					data: [
						'field' => $this->label(),
						'index' => $index + 1
					]
				);
			}
		}
	}

	protected function validations(): array
	{
		return [
			'filter' => fn ($value) => $this->validateConditions($value)
		];
	}
}
