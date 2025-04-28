<?php

namespace Kirby\Form;

use Closure;
use Kirby\Cms\App;
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
	 * Form constructor
	 */
	public function __construct(
		array $props = [],
	) {
		$this->legacyConstructor($props);
	}

	protected function legacyConstructor(array $props): void
	{
		$passthrough = ($props['strict'] ?? false) === false;
		$language    = Language::ensure($props['language'] ?? 'current');

		$this->fields = new Fields(
			fields: $props['fields'] ?? [],
			model: $props['model'] ?? App::instance()->site(),
			language: $language
		);

		if (isset($props['values']) === true) {
			$this->fields->fill(
				input: $props['values'],
				passthrough: $passthrough
			);
		}

		if (isset($props['input']) === true) {
			$this->fields->submit(
				input: $props['input'],
				passthrough: $passthrough
			);
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
	 */
	public function values(): array
	{
		return $this->fields->toFormValues();
	}
}
