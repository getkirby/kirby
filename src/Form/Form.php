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
	 */
	public function __construct(
		array $props = [],
		array $fields = [],
		ModelWithContent|null $model = null,
		Language|string|null $language = null
	) {
		if ($props !== []) {
			$this->legacyConstruct(...$props);
			return;
		}

		$this->fields = new Fields(
			fields: $fields,
			model: $model,
			language: $language
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
		$data     = [];
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
	 * Returns an array with the default value of each field
	 *
	 * @since 5.0.0
	 */
	public function defaults(): array
	{
		return $this->fields->defaults();
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
	 * Sets the value for each field with a matching key in the input array
	 *
	 * @since 5.0.0
	 */
	public function fill(
		array $input,
		bool $passthrough = true
	): static {
		$this->fields->fill(
			input:       $input,
			passthrough: $passthrough
		);
		return $this;
	}

	/**
	 * Creates a new Form instance for the given model with the fields
	 * from the blueprint and the values from the content
	 */
	public static function for(
		ModelWithContent $model,
		array $props = [],
		Language|string|null $language = null,
	): static {
		if ($props !== []) {
			return static::legacyFor(
				$model,
				...$props
			);
		}

		$form = new static(
			fields: $model->blueprint()->fields(),
			model: $model,
			language: $language
		);

		// fill the form with the latest content of the model
		$form->fill(input: $model->content($form->language())->toArray());

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
	 *
	 * @since 5.0.0
	 */
	public function language(): Language
	{
		return $this->fields->language();
	}

	/**
	 * Legacy constructor to support the old props array
	 *
	 * @deprecated 5.0.0 Use the new constructor with named parameters instead
	 */
	protected function legacyConstruct(
		array $fields = [],
		ModelWithContent|null $model = null,
		Language|string|null $language = null,
		array $values = [],
		array $input = [],
		bool $strict = false
	): void {
		$this->__construct(
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
	 * Legacy for method to support the old props array
	 *
	 * @deprecated 5.0.0 Use `::for()` with named parameters instead
	 */
	protected static function legacyFor(
		ModelWithContent $model,
		Language|string|null $language = null,
		bool $strict = false,
		array|null $input = [],
		array|null $values = [],
		bool $ignoreDisabled = false
	): static {
		$form = static::for(
			model: $model,
			language: $language,
		);

		$form->fill(
			input: $values ?? [],
			passthrough: $strict === false
		);

		$form->submit(
			input: $input ?? [],
			passthrough: $strict === false
		);

		return $form;
	}

	/**
	 * Adds values to the passthrough array
	 * which will be added to the form data
	 * if the field does not exist
	 *
	 * @since 5.0.0
	 */
	public function passthrough(
		array|null $values = null
	): static|array {
		if ($values === null) {
			return $this->fields->passthrough();
		}

		$this->fields->passthrough(
			values: $values
		);

		return $this;
	}

	/**
	 * Resets the value of each field
	 *
	 * @since 5.0.0
	 */
	public function reset(): static
	{
		$this->fields->reset();
		return $this;
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
	 * Sets the value for each field with a matching key in the input array
	 * but only if the field is not disabled
	 *
	 * @since 5.0.0
	 * @param bool $passthrough If true, values for undefined fields will be submitted
	 * @param bool $force If true, values for fields that cannot be submitted (e.g. disabled or untranslatable fields) will be submitted
	 */
	public function submit(
		array $input,
		bool $passthrough = true,
		bool $force = false
	): static {
		$this->fields->submit(
			input: $input,
			passthrough: $passthrough,
			force: $force
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
	 *
	 * @since 5.0.0
	 */
	public function toFormValues(): array
	{
		return $this->fields->toFormValues();
	}

	/**
	 * Returns an array with the props of each field
	 * for the frontend
	 *
	 * @since 5.0.0
	 */
	public function toProps(): array
	{
		return $this->fields->toProps();
	}

	/**
	 * Returns an array with the stored value of each field
	 * (e.g. used for saving to content storage)
	 *
	 * @since 5.0.0
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
