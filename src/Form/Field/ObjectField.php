<?php

namespace Kirby\Form\Field;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Mixin;

class ObjectField extends InputField
{
	use Mixin\EmptyState;
	use Mixin\Fields;

	protected array $value = [];

	public function __construct(
		array|null $default = null,
		bool|null $disabled = null,
		array|string|null $empty = null,
		array|null $fields = null,
		array|string|null $help = null,
		array|string|null $label = null,
		string|null $name = null,
		bool|null $required = null,
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

		$this->empty  = $empty;
		$this->fields = $fields;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'empty'  => $this->empty(),
			'fields' => $this->fields(),
		];
	}

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 * @todo Remove psalm suppress after https://github.com/vimeo/psalm/issues/8673 is fixed
	 */
	public function fill(mixed $value): static
	{
		return parent::fill(
			value: Data::decode($value, 'yaml')
		);
	}

	public function toFormValue(): array
	{
		if ($this->isEmptyValue($this->value) === true) {
			return [];
		}

		return $this->form()
			->fill(
				input: $this->value ?? [],
				passthrough: true
			)
			->toFormValues();
	}

	public function toStoredValue(): array
	{
		if ($this->isEmptyValue($this->value) === true) {
			return [];
		}

		$form     = $this->form();
		$defaults = $form->defaults();

		return $form
			->fill(
				input: $defaults,
			)
			->submit(
				input: $this->value,
				passthrough: true
			)
			->toStoredValues();
	}

	protected function validations(): array
	{
		return [
			'object' => $this->validateObject(...)
		];
	}

	protected function validateObject(array|string|null $value): void
	{
		if ($this->isEmptyValue($value) === true) {
			return;
		}

		$errors = $this->form()->fill($value)->errors();

		if ($errors === []) {
			return;
		}

		// use the first error for details
		$name  = array_key_first($errors);
		$error = $errors[$name];

		throw new InvalidArgumentException(
			key: 'object.validation',
			data: [
				'label'   => $error['label'] ?? $name,
				'message' => implode("\n", $error['message'])
			]
		);
	}
}
