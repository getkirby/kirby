<?php

namespace Kirby\Form;

use Closure;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
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
	protected Language $language;
	protected array $passthrough = [];

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

			// don't change the value of non-value field
			if ($field->hasValue() === false) {
				continue;
			}

			// resolve closure values
			if ($value instanceof Closure) {
				$value = $value($field->toFormValue());
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
	 */
	public function language(): Language
	{
		return $this->language;
	}

	/**
	 * Adds values to the passthrough array
	 * which will be added to the form data
	 * if the field does not exist
	 */
	public function passthrough(array $values = []): static
	{
		// always start with a fresh set of passthrough values
		$this->passthrough = [];

		if ($values === []) {
			return $this;
		}

		foreach ($values as $key => $value) {
			// check if the field exists and don't passthrough
			// values for existing fields
			if ($this->get(strtolower($key)) !== null) {
				continue;
			}

			$this->passthrough[$key] = $value;
		}

		return $this;
	}

	/**
	 * Sets the value for each field with a matching key in the input array
	 * but only if the field is not disabled
	 * @since 5.0.0
	 */
	public function submit(
		array $input
	): static {
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
	public function toFormValues(): array
	{
		return $this->toValues(fn ($field) => $field->toFormValue());
	}

	/**
	 * Returns an array with the props of each field
	 * for the frontend
	 */
	public function toProps(): array
	{
		$fields      = $this->data;
		$props       = [];
		$language    = $this->language();
		$permissions = $this->model->permissions()->can('update');

		if (
			$this->model instanceof Page ||
			$this->model instanceof Site
		) {
			// the title should never be updated directly via
			// fields section to avoid conflicts with the rename dialog
			unset($fields['title']);
		}

		foreach ($fields as $name => $field) {
			$props[$name] = $field->toArray();

			// the field should be disabled in the form if the user
			// has no update permissions for the model or if the field
			// is not translatable into the current language
			if ($permissions === false || $field->isTranslatable($language) === false) {
				$props[$name]['disabled'] = true;
			}

			// the value should not be included in the props
			// we pass on the values to the frontend via the model
			// view props to make them globally available for the view.
			unset($props[$name]['value']);
		}

		return $props;
	}

	/**
	 * Returns an array with the stored value of each field
	 * (e.g. used for saving to content storage)
	 */
	public function toStoredValues(): array
	{
		return $this->toValues(fn ($field) => $field->toStoredValue());
	}

	/**
	 * Returns an array with the values of each field
	 * and adds passthrough values if they don't exist
	 * @internal
	 */
	protected function toValues(Closure $method): array
	{
		$values = $this->toArray($method);

		foreach ($this->passthrough as $key => $value) {
			if (isset($values[$key]) === false) {
				$values[$key] = $value;
			}
		}

		return $values;
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
