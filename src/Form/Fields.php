<?php

namespace Kirby\Form;

use Closure;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Form\Field\UnknownField;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Str;

/**
 * A collection of Field objects
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * @extends \Kirby\Toolkit\Collection<\Kirby\Form\Field|\Kirby\Form\FieldClass>
 */
class Fields extends Collection
{
	protected Language $language;

	public function __construct(
		array $fields = [],
		protected ModelWithContent|null $model = null,
		Language|null $language = null
	) {
		foreach ($fields as $name => $field) {
			$this->__set($name, $field);
		}

		$this->language = $language ?? Language::ensure('current');
	}

	/**
	 * Internal setter for each object in the Collection.
	 * This takes care of validation and of setting
	 * the collection prop on each object correctly.
	 *
	 * @param \Kirby\Form\Field|\Kirby\Form\FieldClass|array $field
	 */
	public function __set(string $name, $field): void
	{
		if (is_array($field) === true) {
			// use the array key as name if the name is not set
			$field['model'] ??= $this->model;
			$field['name']  ??= $name;
			$field = Field::factory($field['type'], $field, $this);
		}

		parent::__set($field->name(), $field);
	}

	/**
	 * Goes through the given input and appends hidden fields
	 * for each key that is not already present in the collection.
	 * This is useful for forms that are used to update models
	 * with additional fields that are not part of the original
	 * blueprint.
	 *
	 * @since 5.0.0
	 */
	public function appendUnknownFields(array $input): static
	{
		foreach ($input as $name => $value) {
			if ($this->get($name) === null) {
				$this->data[$name] = new UnknownField(name: $name);
			}
		}

		return $this;
	}

	/**
	 * Returns an array with the default value of each field
	 */
	public function defaults(): array
	{
		return $this->toArray(fn ($field) => $field->default());
	}

	/**
	 * An array of all found in all fields errors
	 */
	public function errors(): array
	{
		$errors = [];

		foreach ($this->data as $name => $field) {
			$fieldErrors = $field->errors();

			if ($fieldErrors !== []) {
				$errors[$name] = [
					'label'   => $field->label(),
					'message' => $fieldErrors
				];
			}
		}

		return $errors;
	}

	/**
	 * Sets the value for each field with a matching key in the input array
	 */
	public function fill(array $input): static
	{
		foreach ($input as $name => $value) {
			if (!$field = $this->get($name)) {
				continue;
			}

			// don't change the value of non-fillable fields
			if ($field->isFillable() === false) {
				continue;
			}

			// resolve closure values
			if ($value instanceof Closure) {
				$value = $value($field->value());
			}

			$field->fill($value);
		}

		return $this;
	}

	/**
	 * Find a field by key/name
	 */
	public function findByKey(string $key): Field|FieldClass|null
	{
		if (str_contains($key, '+')) {
			return $this->findByKeyRecursive($key);
		}

		return parent::findByKey($key);
	}

	/**
	 * Find fields in nested forms recursively
	 */
	public function findByKeyRecursive(string $key): Field|FieldClass|null
	{
		$fields = $this;
		$names  = Str::split($key, '+');
		$index  = 0;
		$count  = count($names);
		$field  = null;

		foreach ($names as $name) {
			$index++;

			// search for the field by name
			$field = $fields->get($name);

			// if the field cannot be found,
			// there's no point in going further
			if ($field === null) {
				return null;
			}

			// there are more parts in the key
			if ($index < $count) {
				$form = $field->form();

				// the search can only continue for
				// fields with valid nested forms
				if ($form instanceof Form === false) {
					return null;
				}

				$fields = $form->fields();
			}
		}

		return $field;
	}

	/**
	 * Returns the language of the fields
	 * @since 5.0.0
	 */
	public function language(): Language
	{
		return $this->language;
	}

	/**
	 * Removes all unknown fields from the collection.
	 * This is useful if you want to be strict about
	 * the submitted or filled in values.
	 *
	 * @since 5.0.0
	 */
	public function removeUnknownFields(): static
	{
		$this->data = array_filter($this->data, fn ($field) => $field instanceof UnknownField === false);
		return $this;
	}

	/**
	 * Sets the value for each field with a matching key in the input array
	 * but only if the field is not disabled
	 * @since 5.0.0
	 */
	public function submit(array $input): static
	{
		$language = $this->language();

		foreach ($input as $name => $value) {
			if (!$field = $this->get($name)) {
				continue;
			}

			// don't change the value of non-submittable fields
			if ($field->isSubmittable($language) === false) {
				continue;
			}

			// resolve closure values
			if ($value instanceof Closure) {
				$value = $value($field->value());
			}

			// submit the value to the field
			// the field class might override this method
			// to handle submitted values differently
			$field->submit($value);
		}

		// reset the errors cache
		return $this;
	}

	/**
	 * Converts the fields collection to an
	 * array and also does that for every
	 * included field.
	 */
	public function toArray(Closure|null $map = null): array
	{
		return A::map($this->data, $map ?? fn ($field) => $field->toArray());
	}

	/**
	 * Returns an array with the form value of each field
	 * (e.g. used as data for Panel Vue components)
	 */
	public function toFormValues(bool $defaults = false): array
	{
		$values   = [];
		$language = $this->language();

		foreach ($this->data as $name => $field) {
			// don't add non-fillable fields
			if ($field->isFillable($language) === false) {
				continue;
			}

			$values[$name] = $field->toFormValue($defaults);
		}

		return $values;
	}

	/**
	 * Returns an array with the props of each field
	 * for the frontend
	 */
	public function toProps(): array
	{
		$props    = [];
		$language = $this->language();

		foreach ($this->data as $name => $field) {
			$props[$name] = $field->toArray();

			if ($field->isTranslatable($language) === false) {
				$props[$name]['disabled'] = true;
			}

			unset($props[$name]['value']);
		}

		return $props;
	}

	/**
	 * Returns an array with the stored value of each field
	 * (e.g. used for saving to content storage)
	 */
	public function toStoredValues(bool $defaults = false): array
	{
		$values   = [];
		$language = $this->language();

		foreach ($this->data as $name => $field) {
			// don't add non-storable fields to the store
			if ($field->isStorable($language) === false) {
				continue;
			}

			$values[$name] = $field->toStoredValue($defaults);
		}

		return $values;
	}
}
