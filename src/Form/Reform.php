<?php

namespace Kirby\Form;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Data\Data;
use Kirby\Exception\NotFoundException;
use Kirby\Form\Field\ExceptionField;
use Kirby\Toolkit\A;
use Throwable;

/**
 * A new form class to refactor form 
 * submissions for models
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Reform
{
	/**
	 * Fields in the form
	 */
	protected Fields $fields;

	public function __construct(
		protected ModelWithContent $model,
		array $fields,		
		Language|null $language = null
	) {
		$this->fields = new Fields(
			fields: $fields, 
			model: $model,
			language: $language
		);
	}

	/**
	 * Returns an array with the default value of each field
	 */
	public function defaults(): array
	{
		return $this->fields->defaults();
	}

	/**
	 * An array of all fou	nd errors
	 */
	public function errors(): array
	{
		return $this->fields->errors();
	}

	/**
	 * Returns form fields
	 */
	public function fields(): Fields
	{
		return $this->fields;
	}

	/**
	 * Fills the form with the given input
	 */
	public function fill(array $input): static
	{
		$this->fields->fill($input);
		return $this;
	}

	/**
	 * Creates a new Reform instance for the 
	 * given model and language
	 */
	public static function for(
		ModelWithContent $model,
		Language|null $language = null
	): static {
		return new static(
			model: $model,
			fields: $model->blueprint()->fields(),
			language: $language
		);
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
	 */
	public function language(): Language
	{
		return $this->fields->language();
	}

	/**
	 * Fills the form with the given input but only if the field is not disabled
	 */
	public function submit(array $input): static
	{
		$this->fields->submit($input);
		return $this;
	}

	/**
	 * Converts the form to a plain array
	 */
	public function toArray(): array
	{
		return [
			'errors'  => $this->fields->errors(),
			'fields'  => $this->fields->toArray(),
			'invalid' => $this->isInvalid()
		];
	}

	/**
	 * Returns an array with the form value of each field
	 */
	public function toFormValues(): array
	{
		return $this->fields->toFormValues();
	}

	/**
	 * Returns an array with the stored value of each field
	 */
	public function toStoredValues(): array
	{
		return $this->fields->toStoredValues();
	}
}
