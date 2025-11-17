<?php

namespace Kirby\Form\Field;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\FieldClass;
use Kirby\Form\Form;
use Kirby\Form\Mixin\EmptyState;
use Kirby\Form\Mixin\Max;
use Kirby\Form\Mixin\Min;
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
class EntriesField extends FieldClass
{
	use EmptyState;
	use Max;
	use Min;

	protected array $field;
	protected Form $form;
	protected bool  $sortable = true;
	protected mixed $value = [];

	public function __construct(array $params = [])
	{
		parent::__construct($params);

		$this->setEmpty($params['empty'] ?? null);
		$this->setField($params['field'] ?? null);
		$this->setMax($params['max'] ?? null);
		$this->setMin($params['min'] ?? null);
		$this->setSortable($params['sortable'] ?? true);
	}

	public function field(): array
	{
		return $this->field;
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
			model: $this->model
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

	protected function setField(array|string|null $attrs = null): void
	{
		if (is_string($attrs) === true) {
			$attrs = ['type' => $attrs];
		}

		$attrs ??= ['type' => 'text'];

		if (in_array($attrs['type'], $this->supports()) === false) {
			throw new InvalidArgumentException(
				key: 'entries.supports',
				data: ['type' => $attrs['type']]
			);
		}

		// remove the unsupported props from the entry field
		unset($attrs['counter'], $attrs['label']);

		$this->field = $attrs;
	}

	protected function setSortable(bool|null $sortable = true): void
	{
		$this->sortable = $sortable;
	}

	public function sortable(): bool
	{
		return $this->sortable;
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
