<?php

namespace Kirby\Form;

use Closure;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;
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
	public function __construct(
		array $fields = [],
		protected ModelWithContent|null $model = null
	) {
		foreach ($fields as $name => $field) {
			$this->__set($name, $field);
		}
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
			$this->get($name)?->fill($value);
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
		return $this->toArray(fn ($field) => $field->toFormValue($defaults));
	}

	/**
	 * Returns an array with the stored value of each field
	 * (e.g. used for saving to content storage)
	 */
	public function toStoredValues(bool $defaults = false): array
	{
		return $this->toArray(fn ($field) => $field->toStoredValue($defaults));
	}

	/**
	 * Checks for errors in all fields and throws an
	 * exception if there are any
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function validate(): void
	{
		$errors = $this->errors();

		if ($errors !==	[]) {
			throw new InvalidArgumentException(
				fallback: 'Invalid form with errors',
				details: $errors
			);
		}
	}
}
