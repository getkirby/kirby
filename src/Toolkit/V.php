<?php

namespace Kirby\Toolkit;

use Countable;
use Exception;
use Kirby\Cms\Field;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Idn;
use Kirby\Uuid\Uuid;
use ReflectionFunction;
use Throwable;

/**
 * A set of validator methods
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class V
{
	/**
	 * An array with all installed validators
	 */
	public static array $validators = [];

	/**
	 * Validates the given input with all passed rules
	 * and returns an array with all error messages.
	 * The array will be empty if the input is valid
	 */
	public static function errors(
		$input,
		array $rules,
		array $messages = []
	): array {
		$errors = static::value($input, $rules, $messages, false);

		return $errors === true ? [] : $errors;
	}

	/**
	 * Runs a number of validators on a set of data and
	 * checks if the data is invalid
	 * @since 3.7.0
	 */
	public static function invalid(
		array $data = [],
		array $rules = [],
		array $messages = []
	): array {
		$errors = [];

		foreach ($rules as $field => $validations) {
			$validationIndex = -1;

			// See: http://php.net/manual/en/types.comparisons.php
			// only false for: null, undefined variable, '', []
			$value   = $data[$field] ?? null;
			$filled  = $value !== null && $value !== '' && $value !== [];
			$message = $messages[$field] ?? $field;

			// True if there is an error message for each validation method.
			$messageArray = is_array($message);

			foreach ($validations as $method => $options) {
				// If the index is numeric, there is no option
				// and `$value` is sent directly as a `$options` parameter
				if (is_numeric($method) === true) {
					$method  = $options;
					$options = [$value];
				} else {
					if (is_array($options) === false) {
						$options = [$options];
					}

					array_unshift($options, $value);
				}

				$validationIndex++;

				if ($method === 'required') {
					if ($filled) {
						// Field is required and filled.
						continue;
					}
				} elseif ($filled) {
					if (V::$method(...$options) === true) {
						// Field is filled and passes validation method.
						continue;
					}
				} else {
					// If a field is not required and not filled, no validation should be done.
					continue;
				}

				// If no continue was called we have a failed validation.
				if ($messageArray) {
					$errors[$field][] = $message[$validationIndex] ?? $field;
				} else {
					$errors[$field] = $message;
				}
			}
		}

		return $errors;
	}

	/**
	 * Creates a useful error message for the given validator
	 * and the arguments. This is used mainly internally
	 * to create error messages
	 */
	public static function message(
		string $validatorName,
		...$params
	): string|null {
		$validatorName  = strtolower($validatorName);
		$translationKey = 'error.validation.' . $validatorName;
		$validators     = array_change_key_case(static::$validators);
		$validator      = $validators[$validatorName] ?? null;

		if ($validator === null) {
			return null;
		}

		$reflection = new ReflectionFunction($validator);
		$arguments  = [];

		foreach ($reflection->getParameters() as $index => $parameter) {
			$value = $params[$index] ?? null;

			if (is_array($value) === true) {
				try {
					foreach ($value as $key => $item) {
						if (is_array($item) === true) {
							$value[$key] = implode('|', $item);
						}
					}
					$value = implode(', ', $value);
				} catch (Throwable) {
					$value = '-';
				}
			}

			$arguments[$parameter->getName()] = $value;
		}

		return I18n::template($translationKey, 'The "' . $validatorName . '" validation failed', $arguments);
	}

	/**
	 * Return the list of all validators
	 */
	public static function validators(): array
	{
		return static::$validators;
	}

	/**
	 * Validate a single value against
	 * a set of rules, using all registered
	 * validators
	 */
	public static function value(
		$value,
		array $rules,
		array $messages = [],
		bool $fail = true
	): bool|array {
		$errors = [];

		foreach ($rules as $validatorName => $validatorOptions) {
			if (is_int($validatorName)) {
				$validatorName    = $validatorOptions;
				$validatorOptions = [];
			}

			if (is_array($validatorOptions) === false) {
				$validatorOptions = [$validatorOptions];
			}

			$validatorName = strtolower($validatorName);

			if (static::$validatorName($value, ...$validatorOptions) === false) {
				$message = $messages[$validatorName] ?? static::message($validatorName, $value, ...$validatorOptions);
				$errors[$validatorName] = $message;

				if ($fail === true) {
					throw new Exception($message);
				}
			}
		}

		return empty($errors) === true ? true : $errors;
	}

	/**
	 * Validate an input array against
	 * a set of rules, using all registered
	 * validators
	 */
	public static function input(array $input, array $rules): bool
	{
		foreach ($rules as $fieldName => $fieldRules) {
			$fieldValue = $input[$fieldName] ?? null;

			// first check for required fields
			if (
				($fieldRules['required'] ?? false) === true &&
				$fieldValue === null
			) {
				throw new Exception(sprintf('The "%s" field is missing', $fieldName));
			}

			// remove the required rule
			unset($fieldRules['required']);

			// skip validation for empty fields
			if ($fieldValue === null) {
				continue;
			}

			try {
				static::value($fieldValue, $fieldRules);
			} catch (Exception $e) {
				throw new Exception(sprintf($e->getMessage() . ' for field "%s"', $fieldName));
			}
		}

		return true;
	}

	/**
	 * Calls an installed validator and passes all arguments
	 */
	public static function __callStatic(string $method, array $arguments): bool
	{
		$method     = strtolower($method);
		$validators = array_change_key_case(static::$validators);

		// check for missing validators
		if (isset($validators[$method]) === false) {
			throw new Exception('The validator does not exist: ' . $method);
		}

		return call_user_func_array($validators[$method], $arguments);
	}
}


/**
 * Default set of validators
 */
V::$validators = [
	/**
	 * Valid: `'yes' | true | 1 | 'on'`
	 */
	'accepted' => function ($value): bool {
		return V::in($value, [1, true, 'yes', 'true', '1', 'on'], true) === true;
	},

	/**
	 * Valid: `a-z | A-Z`
	 */
	'alpha' => function ($value, bool $unicode = false): bool {
		return V::match($value, ($unicode === true ? '/^([\pL])+$/u' : '/^([a-z])+$/i')) === true;
	},

	/**
	 * Valid: `a-z | A-Z | 0-9`
	 */
	'alphanum' => function ($value, bool $unicode = false): bool {
		return V::match($value, ($unicode === true ? '/^[\pL\pN]+$/u' : '/^([a-z0-9])+$/i')) === true;
	},

	/**
	 * Checks for numbers within the given range
	 */
	'between' => function ($value, $min, $max): bool {
		return
			V::min($value, $min) === true &&
			V::max($value, $max) === true;
	},

	/**
	 * Checks if the given string contains the given value
	 */
	'contains' => function ($value, $needle): bool {
		return Str::contains($value, $needle);
	},

	/**
	 * Checks for a valid date or compares two
	 * dates with each other.
	 *
	 * Pass only the first argument to check for a valid date.
	 * Pass an operator as second argument and another date as
	 * third argument to compare them.
	 */
	'date' => function (string|null $value, string $operator = null, string $test = null): bool {
		// make sure $value is a string
		$value ??= '';

		$args = func_get_args();

		// simple date validation
		if (count($args) === 1) {
			$date = date_parse($value);
			return $date !== false &&
					$date['error_count'] === 0 &&
					$date['warning_count'] === 0;
		}

		$value = strtotime($value);
		$test  = strtotime($test);

		if (is_int($value) !== true || is_int($test) !== true) {
			return false;
		}

		return match ($operator) {
			'!=' => $value !== $test,
			'<'  => $value < $test,
			'>'  => $value > $test,
			'<='  => $value <= $test,
			'>='  => $value >= $test,
			'=='  => $value === $test,

			default => throw new InvalidArgumentException('Invalid date comparison operator: "' . $operator . '". Allowed operators: "==", "!=", "<", "<=", ">", ">="')
		};
	},

	/**
	 * Valid: `'no' | false | 0 | 'off'`
	 */
	'denied' => function ($value): bool {
		return V::in($value, [0, false, 'no', 'false', '0', 'off'], true) === true;
	},

	/**
	 * Checks for a value, which does not equal the given value
	 */
	'different' => function ($value, $other, $strict = false): bool {
		if ($strict === true) {
			return $value !== $other;
		}
		return $value != $other;
	},

	/**
	 * Checks for valid email addresses
	 */
	'email' => function ($value): bool {
		if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
			try {
				$email = Idn::encodeEmail($value);
			} catch (Throwable) {
				return false;
			}

			return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		}

		return true;
	},

	/**
	 * Checks for empty values
	 */
	'empty' => function ($value = null): bool {
		$empty = ['', null, []];

		if (in_array($value, $empty, true) === true) {
			return true;
		}

		if (is_countable($value) === true) {
			return count($value) === 0;
		}

		return false;
	},

	/**
	 * Checks if the given string ends with the given value
	 */
	'endsWith' => function (string $value, string $end): bool {
		return Str::endsWith($value, $end);
	},

	/**
	 * Checks for a valid filename
	 */
	'filename' => function ($value): bool {
		return
			V::match($value, '/^[a-z0-9@._-]+$/i') === true &&
			V::min($value, 2) === true;
	},

	/**
	 * Checks if the value exists in a list of given values
	 */
	'in' => function ($value, array $in, bool $strict = false): bool {
		return in_array($value, $in, $strict) === true;
	},

	/**
	 * Checks for a valid integer
	 */
	'integer' => function ($value, bool $strict = false): bool {
		if ($strict === true) {
			return is_int($value) === true;
		}
		return filter_var($value, FILTER_VALIDATE_INT) !== false;
	},

	/**
	 * Checks for a valid IP address
	 */
	'ip' => function ($value): bool {
		return filter_var($value, FILTER_VALIDATE_IP) !== false;
	},

	/**
	 * Checks for valid json
	 */
	'json' => function ($value): bool {
		if (!is_string($value) || $value === '') {
			return false;
		}

		json_decode($value);

		return json_last_error() === JSON_ERROR_NONE;
	},

	/**
	 * Checks if the value is lower than the second value
	 */
	'less' => function ($value, float $max): bool {
		return V::size($value, $max, '<') === true;
	},

	/**
	 * Checks if the value matches the given regular expression
	 */
	'match' => function ($value, string $pattern): bool {
		return preg_match($pattern, $value) !== 0;
	},

	/**
	 * Checks if the value does not exceed the maximum value
	 */
	'max' => function ($value, float $max): bool {
		return V::size($value, $max, '<=') === true;
	},

	/**
	 * Checks if the value is higher than the minimum value
	 */
	'min' => function ($value, float $min): bool {
		return V::size($value, $min, '>=') === true;
	},

	/**
	 * Checks if the number of characters in the value equals or is below the given maximum
	 */
	'maxLength' => function (string $value = null, $max): bool {
		return Str::length(trim($value)) <= $max;
	},

	/**
	 * Checks if the number of characters in the value equals or is greater than the given minimum
	 */
	'minLength' => function (string $value = null, $min): bool {
		return Str::length(trim($value)) >= $min;
	},

	/**
	 * Checks if the number of words in the value equals or is below the given maximum
	 */
	'maxWords' => function (string $value = null, $max): bool {
		return V::max(explode(' ', trim($value)), $max) === true;
	},

	/**
	 * Checks if the number of words in the value equals or is below the given maximum
	 */
	'minWords' => function (string $value = null, $min): bool {
		return V::min(explode(' ', trim($value)), $min) === true;
	},

	/**
	 * Checks if the first value is higher than the second value
	 */
	'more' => function ($value, float $min): bool {
		return V::size($value, $min, '>') === true;
	},

	/**
	 * Checks that the given string does not contain the second value
	 */
	'notContains' => function ($value, $needle): bool {
		return V::contains($value, $needle) === false;
	},

	/**
	 * Checks that the given value is not empty
	 */
	'notEmpty' => function ($value): bool {
		return V::empty($value) === false;
	},

	/**
	 * Checks that the given value is not in the given list of values
	 */
	'notIn' => function ($value, $notIn): bool {
		return V::in($value, $notIn) === false;
	},

	/**
	 * Checks for a valid number / numeric value (float, int, double)
	 */
	'num' => function ($value): bool {
		return is_numeric($value) === true;
	},

	/**
	 * Checks if the value is present
	 */
	'required' => function ($value, $array = null): bool {
		// with reference array
		if (is_array($array) === true) {
			return isset($array[$value]) === true && V::notEmpty($array[$value]) === true;
		}

		// without reference array
		return V::notEmpty($value);
	},

	/**
	 * Checks that the first value equals the second value
	 */
	'same' => function ($value, $other, bool $strict = false): bool {
		if ($strict === true) {
			return $value === $other;
		}
		return $value == $other;
	},

	/**
	 * Checks that the value has the given size
	 */
	'size' => function ($value, $size, $operator = '=='): bool {
		// if value is field object, first convert it to a readable value
		// it is important to check at the beginning as the value can be string or numeric
		if ($value instanceof Field) {
			$value = $value->value();
		}

		if (is_numeric($value) === true) {
			$count = $value;
		} elseif (is_string($value) === true) {
			$count = Str::length(trim($value));
		} elseif (is_array($value) === true) {
			$count = count($value);
		} elseif (is_object($value) === true) {
			if ($value instanceof Countable) {
				$count = count($value);
			} elseif (method_exists($value, 'count') === true) {
				$count = $value->count();
			} else {
				throw new Exception('$value is an uncountable object');
			}
		} else {
			throw new Exception('$value is of type without size');
		}

		return match ($operator) {
			'<'     => $count < $size,
			'>'     => $count > $size,
			'<='    => $count <= $size,
			'>='    => $count >= $size,
			default => $count == $size
		};
	},

	/**
	 * Checks that the string starts with the given start value
	 */
	'startsWith' => function (string $value, string $start): bool {
		return Str::startsWith($value, $start);
	},

	/**
	 * Checks for valid time
	 */
	'time' => function ($value): bool {
		return V::date($value);
	},

	/**
	 * Checks for a valid Url
	 */
	'url' => function ($value): bool {
		// In search for the perfect regular expression: https://mathiasbynens.be/demo/url-regex
		// Added localhost support and removed 127.*.*.* ip restriction
		$regex = '_^(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:localhost)|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?$_iu';
		return preg_match($regex, $value ?? '') !== 0;
	},

	/**
	 * Checks for a valid Uuid, optionally for specific model type
	 */
	'uuid' => function (string $value, string $type = null): bool {
		return Uuid::is($value, $type);
	}
];
