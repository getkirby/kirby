<?php

namespace Kirby\Form;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
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
	protected ModelWithContent $model;
	protected array $passthrough = [];

	public function __construct(
		array $fields = [],
		ModelWithContent|null $model = null,
		Language|string|null $language = null
	) {
		$this->model    = $model ?? App::instance()->site();
		$this->language = Language::ensure($language ?? 'current');

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
	 *
	 * @since 5.0.0
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
	 * Get the field object by name
	 * and handle nested fields correctly
	 *
	 * @since 5.0.0
	 * @throws \Kirby\Exception\NotFoundException
	 */
	public function field(string $name): Field|FieldClass
	{
		if ($field = $this->find($name)) {
			return $field;
		}

		throw new NotFoundException(
			message: 'The field could not be found'
		);
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
		if ($passthrough === true) {
			$this->passthrough($input);
		}

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
	 * Creates a new Fields instance for the given model and language
	 *
	 * @since 5.0.0
	 */
	public static function for(
		ModelWithContent $model,
		Language|string|null $language = null
	): static {
		return new static(
			fields: $model->blueprint()->fields(),
			model: $model,
			language: $language,
		);
	}

	/**
	 * Returns the language of the fields
	 *
	 * @since 5.0.0
	 */
	public function language(): Language
	{
		return $this->language;
	}

	/**
	 * Adds values to the passthrough array
	 * which will be added to the form data
	 * if the field does not exist
	 *
	 * @since 5.0.0
	 */
	public function passthrough(array|null $values = null): static|array
	{
		// use passthrough method as getter if the value is null
		if ($values === null) {
			return $this->passthrough;
		}

		foreach ($values as $key => $value) {
			$key = strtolower($key);

			// check if the field exists and don't passthrough
			// values for existing fields
			if ($this->get($key) !== null) {
				continue;
			}

			// resolve closure values
			if ($value instanceof Closure) {
				$value = $value($this->passthrough[$key] ?? null);
			}

			$this->passthrough[$key] = $value;
		}

		return $this;
	}

	/**
	 * Resets the value of each field
	 *
	 * @since 5.0.0
	 */
	public function reset(): static
	{
		// reset the passthrough values
		$this->passthrough = [];

		// reset the values of each field
		foreach ($this->data as $field) {
			$field->fill(null);
		}

		return $this;
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
		$language = $this->language();

		if ($passthrough === true) {
			$this->passthrough($input);
		}

		foreach ($input as $name => $value) {
			if (!$field = $this->get($name)) {
				continue;
			}

			// don't submit fields without a value
			if ($force === true && $field->hasValue() === false) {
				continue;
			}

			// don't submit fields that are not submittable
			if ($force === false && $field->isSubmittable($language) === false) {
				continue;
			}

			// resolve closure values
			if ($value instanceof Closure) {
				$value = $value($field->toFormValue());
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
	 *
	 * @since 5.0.0
	 */
	public function toFormValues(): array
	{
		return $this->toValues(
			fn ($field) => $field->toFormValue(),
			fn ($field) => $field->hasValue()
		);
	}

	/**
	 * Returns an array with the props of each field
	 * for the frontend
	 *
	 * @since 5.0.0
	 */
	public function toProps(): array
	{
		$fields      = $this->data;
		$props       = [];
		$language    = $this->language();
		$permissions = $this->model->permissions()->can('update');

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
	 *
	 * @since 5.0.0
	 */
	public function toStoredValues(): array
	{
		return $this->toValues(
			fn ($field) => $field->toStoredValue(),
			fn ($field) => $field->isStorable($this->language())
		);
	}

	/**
	 * Returns an array with the values of each field
	 * and adds passthrough values if they don't exist
	 * @unstable
	 */
	protected function toValues(Closure $method, Closure $filter): array
	{
		$values = $this->filter($filter)->toArray($method);

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
	 * @since 5.0.0
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
