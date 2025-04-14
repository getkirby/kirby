<?php

namespace Kirby\Form;

use Closure;
use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Data\Data;
use Kirby\Form\Field\ExceptionField;
use Kirby\Toolkit\A;
use Throwable;

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
	 * All values of form
	 */
	protected array $values = [];

	/**
	 * Form constructor
	 */
	public function __construct(array $props)
	{
		$fields = $props['fields'] ?? [];
		$values = $props['values'] ?? [];
		$input  = $props['input']  ?? [];
		$model  = $props['model']  ?? null;
		$strict = $props['strict'] ?? false;
		$inject = $props;

		// get the language for the form
		$language = Language::ensure($props['language'] ?? 'current');

		// prepare field properties for multilang setups
		$fields = static::prepareFieldsForLanguage(
			$fields,
			$language
		);

		// lowercase all value names
		$values = array_change_key_case($values);
		$input  = array_change_key_case($input);

		unset($inject['fields'], $inject['values'], $inject['input']);

		$this->fields = new Fields(
			model: $model,
			language: $language
		);

		$this->values = [];

		foreach ($fields as $name => $props) {
			// inject stuff from the form constructor (model, etc.)
			$props = [...$inject, ...$props];

			// inject the name
			$props['name'] = $name = strtolower($name);

			// check if the field is disabled and
			// overwrite the field value if not set
			$props['value'] = match ($props['disabled'] ?? false) {
				true    => $values[$name] ?? null,
				default => $input[$name] ?? $values[$name] ?? null
			};

			try {
				$field = Field::factory($props['type'], $props, $this->fields);
			} catch (Throwable $e) {
				$field = new ExceptionField(
					name: $props['name'],
					exception: $e
				);
			}

			if ($field->hasValue() === true) {
				$this->values[$name] = $field->value();
			}

			$this->fields->append($name, $field);
		}

		if ($strict !== true) {
			// use all given values, no matter
			// if there's a field or not.
			$input = [...$values, ...$input];

			foreach ($input as $key => $value) {
				$this->values[$key] ??= $value;
			}
		}
	}

	/**
	 * Returns the data required to write to the content file
	 * Doesn't include default and null values
	 */
	public function content(): array
	{
		return $this->data(false, false);
	}

	/**
	 * Returns data for all fields in the form
	 *
	 * @param false $defaults
	 */
	public function data($defaults = false, bool $includeNulls = true): array
	{
		$data = $this->values;

		foreach ($this->fields as $field) {
			if ($field->hasValue() === false || $field->unset() === true) {
				if ($includeNulls === true) {
					$data[$field->name()] = null;
				} else {
					unset($data[$field->name()]);
				}
			} else {
				if ($defaults === true && $field->isEmpty() === true) {
					$field->fill($field->default());
				}

				$data[$field->name()] = $field->toStoredValue();
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

	public static function for(
		ModelWithContent $model,
		array $props = []
	): static {
		// get the original model data
		$original = $model->content($props['language'] ?? null)->toArray();
		$values   = $props['values'] ?? [];

		// convert closures to values
		foreach ($values as $key => $value) {
			if ($value instanceof Closure) {
				$values[$key] = $value($original[$key] ?? null);
			}
		}

		// set a few defaults
		$props['values']   = [...$original, ...$values];
		$props['fields'] ??= [];
		$props['model']    = $model;

		// search for the blueprint
		$props['fields'] = $model->blueprint()->fields();

		$ignoreDisabled = $props['ignoreDisabled'] ?? false;

		// REFACTOR: this could be more elegant
		if ($ignoreDisabled === true) {
			$props['fields'] = array_map(function ($field) {
				$field['disabled'] = false;
				return $field;
			}, $props['fields']);
		}

		return new static($props);
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
	 * Disables fields in secondary languages when
	 * they are configured to be untranslatable
	 */
	protected static function prepareFieldsForLanguage(
		array $fields,
		Language $language
	): array {
		if ($language->isDefault() === true) {
			return $fields;
		}

		foreach ($fields as $fieldName => $fieldProps) {
			// switch untranslatable fields to readonly
			if (($fieldProps['translate'] ?? true) === false) {
				$fields[$fieldName]['unset']    = true;
				$fields[$fieldName]['disabled'] = true;
			}
		}

		return $fields;
	}

	/**
	 * Converts the data of fields to strings
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
	 */
	public function values(): array
	{
		return $this->values;
	}
}
