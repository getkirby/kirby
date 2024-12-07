<?php

namespace Kirby\Form;

use Closure;
use Kirby\Cms\ModelWithContent;
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
	/**
	 * Cache for the errors array
	 *
	 * @var array<string, array<string, string>>|null
	 */
	protected array|null $errors = null;

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

		// reset the errors cache if new fields are added
		$this->errors = null;
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
		if ($this->errors !== null) {
			return $this->errors; // @codeCoverageIgnore
		}

		$this->errors = [];

		foreach ($this->data as $name => $field) {
			$errors = $field->errors();

			if ($errors !== []) {
				$this->errors[$name] = [
					'label'   => $field->label(),
					'message' => $errors
				];
			}
		}

		return $this->errors;
	}

	/**
	 * Sets the value for each field with a matching key in the input array
	 */
	public function fill(array $input): static
	{
		foreach ($input as $name => $value) {
			$this->get($name)?->fill($value);
		}

		// reset the errors cache
		$this->errors = null;

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
	 */
	public function toFormValues(bool $defaults = false): array
	{
		return $this->toArray(fn ($field) => $field->toFormValue($defaults));
	}

	/**
	 * Returns an array with the stored value of each field
	 */
	public function toStoredValues(bool $defaults = false): array
	{
		return $this->toArray(fn ($field) => $field->toStoredValue($defaults));
	}
}
