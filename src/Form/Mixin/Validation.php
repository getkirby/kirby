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
	 * Checks if the field is valid
	 */
	public function isValid(): bool
	{
		return $this->errors() === [];
	}

	/**
	 * Runs the validations defined for the field
	 */
	protected function validate(): array
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
