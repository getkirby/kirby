<?php

namespace Kirby\Form\Mixin;

use Closure;
use Exception;
use Kirby\Form\Validations;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\V;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Validation
{
	/**
	 * An array of all found errors
	 */
	protected array|null $errors = null;
	protected bool $required = false;
	protected array $validate = [];

	/**
	 * Runs all validations and returns an array of
	 * error messages
	 */
	public function errors(): array
	{
		return $this->errors ??= $this->validate();
	}

	/**
	 * Checks if the field is invalid
	 */
	public function isInvalid(): bool
	{
		return $this->isValid() === false;
	}

	/**
	 * If `true`, the field has to be filled in correctly to be saved.
	 */
	public function isRequired(): bool
	{
		return $this->required;
	}

	/**
	 * Checks if the field is valid
	 */
	public function isValid(): bool
	{
		return $this->errors() === [];
	}

	/**
	 * @deprecated 5.0.0 Use `::isRequired` instead
	 */
	public function required(): bool
	{
		return $this->isRequired();
	}

	/**
	 * Set the required state
	 */
	protected function setRequired(bool $required = false): void
	{
		$this->required = $required;
	}

	/**
	 * Set custom validation rules for the field
	 */
	protected function setValidate(string|array $validate = []): void
	{
		$this->validate = A::wrap($validate);
	}

	/**
	 * Runs the validations defined for the field
	 */
	public function validate(): array
	{
		$validations = $this->validations();
		$value       = $this->value();
		$errors      = [];

		// validate required values
		if ($this->needsValue() === true) {
			$errors['required'] = I18n::translate('error.validation.required');
		}

		foreach ($validations as $key => $validation) {
			if (is_int($key) === true) {
				// predefined validation
				try {
					Validations::$validation($this, $value);
				} catch (Exception $e) {
					$errors[$validation] = $e->getMessage();
				}
				continue;
			}

			if ($validation instanceof Closure) {
				try {
					$validation->call($this, $value);
				} catch (Exception $e) {
					$errors[$key] = $e->getMessage();
				}
			}
		}

		if (
			empty($this->validate) === false &&
			($this->isEmpty() === false || $this->isRequired() === true)
		) {
			$rules = A::wrap($this->validate);

			$errors = [
				...$errors,
				...V::errors($value, $rules)
			];
		}

		return $errors;
	}

	/**
	 * Defines all validation rules
	 */
	protected function validations(): array
	{
		return [];
	}
}
