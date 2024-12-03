<?php

namespace Kirby\Form;

use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Content\Content;
use Kirby\Content\Version;

class Reform
{
	protected Fields $fields;
	protected ModelWithContent $model;
	protected Language $language;
	public function __construct(
		ModelWithContent $model,
		array|null $fields = null,
		Language|string $language = null,
	) {
		$this->model    = $model;
		$this->fields   = new Fields($fields ?? $model->blueprint()->fields(), $model);
		$this->language = Language::ensure($language);
	}

	/**
	 * An array of all found errors
	 */
	public function errors(): array
	{
		return $this->fields->errors();
	}

	/**
	 * Returns a collection with all form fields
	 */
	public function fields(): Fields
	{
		return $this->fields;
	}

	/**
	 * Sets the value for each field with a matching key in the input array
	 */
	public function fill(array|Version|Content|ModelWithContent $input): static
	{
		$values = match(true) {
			$input instanceof ModelWithContent
				=> $input->content()->toArray(),
			$input instanceof Version
				=> $input->content()->toArray(),
			$input instanceof Content
				=> $input->toArray(),
			default => $input,
		};

		$this->fields->fill($values);
		return $this;
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
	 * Sets the value for each field with a matching key in the input array
	 */
	public function submit(array $input): static {
		// only submit values for translatable fields
		$this
			->fields
			->filter(fn($field) => $field->isDisabled($this->language) === false)
			->submit($input, $this->language);
		return $this;
	}

	/**
	 * Returns an array with the form value of each field
	 */
	public function toFormValues(bool $defaults = false): array
	{
		return $this->fields->toFormValues($defaults);
	}

	/**
	 * Returns an array with the stored value of each field
	 */
	public function toStoredValues(bool $defaults = false): array
	{
		return $this->fields->translatable($this->language)->toStoredValues($defaults);
	}
}
