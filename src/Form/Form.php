<?php

namespace Kirby\Form;

use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\FormValidationException;
use Kirby\Exception\NotFoundException;

/**
 * The main form class, that is being
 * used to create a list of form fields
 * and handles global form validation
 * and submission
 *
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
		array $fields = [],
		ModelWithContent|null $model = null,
		Language|string|null $language = null
	) {
		$this->fields = new Fields(
			fields: $fields,
			model: $model,
			language: $language
		);
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
	 * @throws NotFoundException
	 */
	public function field(string $name): Field
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
		array $input = [],
		bool $passthrough = true,
		bool $defaults = false
	): static {
		$this->fields->fill(
			input:       $input,
			passthrough: $passthrough,
			defaults:    $defaults
		);
		return $this;
	}

	/**
	 * Creates a new Form instance for the given model with the fields
	 * from the blueprint and the values from the content
	 */
	public static function for(
		ModelWithContent $model,
		Language|string|null $language = null,
	): static {
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
	 * Adds values to the passthrough array
	 * which will be added to the form data
	 * if the field does not exist
	 *
	 * @since 5.0.0
	 *
	 * @return ($values is null ? array : static)
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
	 * Sets the value for each field with a matching key in the input array
	 * but only if the field is not disabled
	 *
	 * @since 5.0.0
	 * @param $passthrough If true, values for undefined fields will be submitted
	 * @param $force If true, values for fields that cannot be submitted (e.g. disabled or untranslatable fields) will be submitted
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
		$errors = $this->fields->errors();

		return [
			'errors'  => $errors,
			'fields'  => $this->fields->toArray(),
			'invalid' => $errors !== []
		];
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
	 * @throws FormValidationException
	 */
	public function validate(): void
	{
		$this->fields->validate();
	}

}
