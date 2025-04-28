<?php

namespace Kirby\Form;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Data\Data;
use Kirby\Toolkit\A;

/**
 * The main form class, that is being
 * used to create a list of form fields
 * and handles global form validation
 * and submission
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Form
{
	/**
	 * Fields in the form
	 */
	protected Fields $fields;

	/**
	 * Form constructor
	 *
	 * @param array $props @deprecated 5.0.0 Use following parameters instead
	 */
	public function __construct(
		array $props = [],
		array $fields = [],
		ModelWithContent|null $model = null,
		Language|string|null $language = null
	) {
		if ($props !== []) {
			$this->__constructLegacy(...$props);
			return;
		}

		$this->fields = new Fields(
			fields: $fields,
			model: $model,
			language: $language
		);
	}

	/**
	 * Legacy constructor to support the old props array
	 * @deprecated 5.0.0 Use the new constructor with named parameters instead
	 */
	protected function __constructLegacy(
		array $props = [],
		array $fields = [],
		ModelWithContent|null $model = null,
		Language|string|null $language = null,
		array $values = [],
		array $input = [],
		bool $strict = false
	): void {
		$this->fields = new Fields(
			fields: $fields,
			model: $model,
			language: $language
		);

		$this->fill(
			input: $values,
			passthrough: $strict === false
		);

		$this->submit(
			input: $input,
			passthrough: $strict === false
		);
	}

	/**
	 * Returns the data required to write to the content file
	 * Doesn't include default and null values
	 *
	 * @deprecated 5.0.0 Use `::toStoredValues()` instead
	 */
	public function content(): array
	{
		return $this->data(false, false);
	}

	/**
	 * Returns data for all fields in the form
	 *
	 * @deprecated 5.0.0 Use `::toStoredValues()` instead
	 */
	public function data($defaults = false, bool $includeNulls = true): array
	{
		$data = [];
		$language = $this->fields->language();

		foreach ($this->fields as $field) {
			if ($field->isStorable($language) === false) {
				if ($includeNulls === true) {
					$data[$field->name()] = null;
				}

				continue;
			}

			if ($defaults === true && $field->isEmpty() === true) {
				$field->fill($field->default());
			}

			$data[$field->name()] = $field->toStoredValue();
		}

		foreach ($this->fields->passthrough() as $key => $value) {
			if (isset($data[$key]) === false) {
				$data[$key] = $value;
			}
		}

		return $data;
	}

	/**
	 * An array of all found errors
	 */
	public function errors(): array
	{
		return $this->fields->errors();
	}

	/**
	 * Get the field object by name
	 * and handle nested fields correctly
	 *
	 * @throws \Kirby\Exception\NotFoundException
	 */
	public function field(string $name): Field|FieldClass
	{
		return $this->fields->field($name);
	}

	/**
	 * Returns form fields
	 */
	public function fields(): Fields
	{
		return $this->fields;
	}

	/**
	 * Shortcut for `::fields()->fill()`
	 */
	public function fill(
		array $input,
		bool $passthrough = false
	): static {
		$this->fields->fill(
			input: $input,
			passthrough: $passthrough
		);

		return $this;
	}

	public static function for(
		ModelWithContent $model,
		array $props = []
	): static {
		$form = new static(
			fields: $model->blueprint()->fields(),
			model: $model,
			language: $props['language'] ?? 'current'
		);

		$passthrough = ($props['strict'] ?? false) !== true;
		$language    = $form->language();

		// fill the form with the latest content of the model
		$form->fill(
			input: $model->content($language)->toArray(),
			passthrough: $passthrough
		);

		// add additional initial values
		$form->fill(
			input: $props['values'] ?? [],
			passthrough: $passthrough
		);

		// submit input values
		$form->submit(
			input: $props['input'] ?? [],
			passthrough: $passthrough
		);

		return $form;
	}

	/**
	 * Checks if the form is invalid
	 */
	public function isInvalid(): bool
	{
		return $this->isValid() === false;
	}

	/**
	 * Checks if the form is valid
	 */
	public function isValid(): bool
	{
		return $this->fields->errors() === [];
	}

	/**
	 * Returns the language of the form
	 */
	public function language(): Language
	{
		return $this->fields->language();
	}

	/**
	 * Converts the data of fields to strings
	 *
	 * @deprecated 5.0.0 Use `::toStoredValues()` instead
	 */
	public function strings($defaults = false): array
	{
		return A::map(
			$this->data($defaults),
			fn ($value) => match (true) {
				is_array($value) => Data::encode($value, 'yaml'),
				default		     => $value
			}
		);
	}

	/**
	 * Shortcut for `::fields()->submit()`
	 */
	public function submit(
		array $input,
		bool $passthrough = false
	): static {
		$this->fields->submit(
			input: $input,
			passthrough: $passthrough
		);

		return $this;
	}

	/**
	 * Converts the form to a plain array
	 */
	public function toArray(): array
	{
		$array = [
			'errors'  => $this->fields->errors(),
			'fields'  => $this->fields->toArray(),
			'invalid' => $this->isInvalid()
		];

		return $array;
	}

	/**
	 * Returns an array with the form value of each field
	 * (e.g. used as data for Panel Vue components)
	 */
	public function toFormValues(): array
	{
		return $this->fields->toFormValues();
	}

	/**
	 * Shortcut for `::fields()->toProps()`
	 */
	public function toProps(): array
	{
		return $this->fields->toProps();
	}

	/**
	 * Returns an array with the stored value of each field
	 * (e.g. used for saving to content storage)
	 */
	public function toStoredValues(): array
	{
		return $this->fields->toStoredValues();
	}

	/**
	 * Validates the form and throws an exception if there are any errors
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function validate(): void
	{
		$this->fields->validate();
	}

	/**
	 * Returns form values
	 *
	 * @deprecated 5.0.0 Use `::toFormValues()` instead
	 */
	public function values(): array
	{
		return $this->fields->toFormValues();
	}
}
