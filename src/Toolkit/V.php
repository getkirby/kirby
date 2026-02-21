<?php

namespace Kirby\Toolkit;

use Countable;
use Exception;
use Kirby\Content\Field;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Idn;
use Kirby\Uuid\Uuid;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;

/**
 * A set of validator methods
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class V
{
	/**
	 * All custom validators
	 */
	public static array $validators = [];

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

	/**
	 * Valid: `'yes' | true | 1 | 'on'`
	 */
	public static function accepted($value): bool
	{
		return static::in($value, [1, true, 'yes', 'true', '1', 'on'], true) === true;
	}

	/**
	 * Valid: `a-z | A-Z`
	 */
	public static function alpha($value, bool $unicode = false): bool
	{
		return static::match($value, ($unicode === true ? '/^([\pL])+$/u' : '/^([a-z])+$/i')) === true;
	}

	/**
	 * Valid: `a-z | A-Z | 0-9`
	 */
	public static function alphanum($value, bool $unicode = false): bool
	{
		return static::match($value, ($unicode === true ? '/^[\pL\pN]+$/u' : '/^([a-z0-9])+$/i')) === true;
	}

	/**
	 * Checks for numbers within the given range
	 */
	public static function between($value, $min, $max): bool
	{
		return
			static::min($value, $min) === true &&
			static::max($value, $max) === true;
	}

	/**
	 * Checks with the callback sent by the user
	 * It's ideal for one-time custom validations
	 */
	public static function callback($value, callable $callback): bool
	{
		return $callback($value);
	}

	/**
	 * Checks if the given string contains the given value
	 */
	public static function contains($value, $needle): bool
	{
		return Str::contains($value, $needle);
	}

	/**
	 * Checks for a valid date or compares two
	 * dates with each other.
	 *
	 * Pass only the first argument to check for a valid date.
	 * Pass an operator as second argument and another date as
	 * third argument to compare them.
	 */
	public static function date(
		string|null $value,
		string|null $operator = null,
		string|null $test = null
	): bool {
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

			default => throw new InvalidArgumentException(
				message: 'Invalid date comparison operator: "' . $operator . '". Allowed operators: "==", "!=", "<", "<=", ">", ">="'
			)
		};
	}

	/**
	 * Valid: `'no' | false | 0 | 'off'`
	 */
	public static function denied($value): bool
	{
		return static::in($value, [0, false, 'no', 'false', '0', 'off'], true) === true;
	}

	/**
	 * Checks for a value, which does not equal the given value
	 */
	public static function different($value, $other, $strict = false): bool
	{
		if ($strict === true) {
			return $value !== $other;
		}

		return $value != $other;
	}

	/**
	 * Checks for valid email addresses
	 */
	public static function email($value): bool
	{
		if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
			try {
				$email = Idn::encodeEmail($value);
			} catch (Throwable) {
				return false;
			}

			return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		}

		return true;
	}

	/**
	 * Checks for empty values
	 */
	public static function empty($value = null): bool
	{
		$empty = ['', null, []];

		if (in_array($value, $empty, true) === true) {
			return true;
		}

		if (is_countable($value) === true) {
			return count($value) === 0;
		}

		return false;
	}

	/**
	 * Checks if the given string ends with the given value
	 */
	public static function endsWith(string $value, string $end): bool
	{
		return Str::endsWith($value, $end);
	}

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

		if ($errors === true) {
			return [];
		}

		return $errors;
	}

	/**
	 * Checks for a valid filename
	 */
	public static function filename($value): bool
	{
		return
			static::match($value, '/^[a-z0-9@._-]+$/i') === true &&
			static::min($value, 2) === true;
	}

	/**
	 * Checks if the value exists in a list of given values
	 */
	public static function in($value, array $in, bool $strict = false): bool
	{
		return in_array($value, $in, $strict) === true;
	}


	/**
	 * Validate an input array against
	 * a set of rules, using all registered
	 * validators
	 */
	public static function input(array $input, array $rules): bool
	{
		foreach ($rules as $field => $rules) {
			$value = $input[$field] ?? null;

			// first check for required fields
			if (
				($rules['required'] ?? false) === true &&
				$value === null
			) {
				throw new Exception(sprintf('The "%s" field is missing', $field));
			}

			// remove the required rule
			unset($rules['required']);

			// skip validation for empty fields
			if ($value === null) {
				continue;
			}

			try {
				static::value($value, $rules);
			} catch (Exception $e) {
				throw new Exception(sprintf($e->getMessage() . ' for field "%s"', $field));
			}
		}

		return true;
	}

	/**
	 * Checks for a valid integer
	 */
	public static function integer($value, bool $strict = false): bool
	{
		if ($strict === true) {
			return is_int($value) === true;
		}
		return filter_var($value, FILTER_VALIDATE_INT) !== false;
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
					// If a field is not required and not filled,
					// no validation should be done.
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
	 * Checks for a valid IP address
	 */
	public static function ip($value): bool
	{
		return filter_var($value, FILTER_VALIDATE_IP) !== false;
	}

	/**
	 * Checks for valid json
	 */
	public static function json($value): bool
	{
		if (is_string($value) === false) {
			return false;
		}

		return json_validate($value);
	}

	/**
	 * Checks if the value is lower than the second value
	 */
	public static function less($value, float $max): bool
	{
		return static::size($value, $max, '<') === true;
	}

	/**
	 * Checks if the value matches the given regular expression
	 */
	public static function match($value, string $pattern): bool
	{
		return preg_match($pattern, (string)$value) === 1;
	}

	/**
	 * Checks if the value does not exceed the maximum value
	 */
	public static function max($value, float $max): bool
	{
		return static::size($value, $max, '<=') === true;
	}

	/**
	 * Checks if the number of characters in the value equals
	 * or is below the given maximum
	 */
	public static function maxLength(string|null $value, $max): bool
	{
		return Str::length(trim($value)) <= $max;
	}

	/**
	 * Checks if the number of words in the value equals or
	 * is below the given maximum
	 */
	public static function maxWords(string|null $value, $max): bool
	{
		return static::max(explode(' ', trim($value)), $max) === true;
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
		$validatorName = strtolower($validatorName);
		$validators    = array_change_key_case(static::$validators);

		if (method_exists(static::class, $validatorName) === true) {
			$reflection = new ReflectionMethod(static::class, $validatorName);
		} elseif ($validator  = $validators[$validatorName] ?? null) {
			$reflection = new ReflectionFunction($validator);
		}

		if (isset($reflection) === false) {
			return null;
		}

		$arguments = [];

		foreach ($reflection->getParameters() as $index => $parameter) {
			$value = $params[$index] ?? null;

			if (is_array($value) === true) {
				foreach ($value as $key => $item) {
					if (is_array($item) === true) {
						$value[$key] = A::implode($item, '|');
					}
				}

				$value = implode(', ', $value);
			}

			$arguments[$parameter->getName()] = $value;
		}

		return I18n::template(
			'error.validation.' . $validatorName,
			'The "' . $validatorName . '" validation failed',
			$arguments
		);
	}

	/**
	 * Checks if the value is higher than the minimum value
	 */
	public static function min($value, float $min): bool
	{
		return static::size($value, $min, '>=') === true;
	}

	/**
	 * Checks if the number of characters in the value equals or
	 * is greater than the given minimum
	 */
	public static function minLength(string|null $value, $min): bool
	{
		return Str::length(trim($value)) >= $min;
	}

	/**
	 * Checks if the number of words in the value equals or
	 * is below the given maximum
	 */
	public static function minWords(string|null $value, $min): bool
	{
		return static::min(explode(' ', trim($value)), $min) === true;
	}

	/**
	 * Checks if the first value is higher than the second value
	 */
	public static function more($value, float $min): bool
	{
		return static::size($value, $min, '>') === true;
	}

	/**
	 * Checks that the given string does not contain the second value
	 */
	public static function notContains($value, $needle): bool
	{
		return static::contains($value, $needle) === false;
	}

	/**
	 * Checks that the given value is not empty
	 */
	public static function notEmpty($value): bool
	{
		return static::empty($value) === false;
	}

	/**
	 * Checks that the given value is not in the given list of values
	 */
	public static function notIn($value, $notIn): bool
	{
		return static::in($value, $notIn) === false;
	}

	/**
	 * Checks for a valid number / numeric value (float, int, double)
	 */
	public static function num($value): bool
	{
		return is_numeric($value) === true;
	}

	/**
	 * Checks if the value is present
	 */
	public static function required($value, $array = null): bool
	{
		// with reference array
		if (is_array($array) === true) {
			return isset($array[$value]) === true && static::notEmpty($array[$value]) === true;
		}

		// without reference array
		return static::notEmpty($value);
	}

	/**
	 * Checks that the first value equals the second value
	 */
	public static function same($value, $other, bool $strict = false): bool
	{
		if ($strict === true) {
			return $value === $other;
		}

		return $value == $other;
	}

	/**
	 * Checks that the value has the given size
	 */
	public static function size($value, $size, $operator = '=='): bool
	{
		// if value is field object, first convert it to a readable value
		// it is important to check at the beginning as the value can be string or numeric
		if ($value instanceof Field) {
			$value = $value->value();
		}

		$count = match (true) {
			is_numeric($value) => $value,
			is_string($value)  => Str::length(trim($value)),
			is_array($value)   => count($value),
			is_object($value)  => match (true) {
				$value instanceof Countable    => count($value),
				method_exists($value, 'count') => $value->count(),

				default => throw new Exception('$value is an uncountable object')
			},

			default => throw new Exception('$value is of type without size')
		};

		return match ($operator) {
			'<'     => $count < $size,
			'>'     => $count > $size,
			'<='    => $count <= $size,
			'>='    => $count >= $size,
			default => $count == $size
		};
	}

	/**
	 * Checks that the string starts with the given start value
	 */
	public static function startsWith(string $value, string $start): bool
	{
		return Str::startsWith($value, $start);
	}

	/**
	 * Checks for a valid unformatted telephone number
	 */
	public static function tel($value): bool
	{
		return static::match($value, '!^[+]{0,1}[0-9]+$!');
	}

	/**
	 * Checks for valid time
	 */
	public static function time($value): bool
	{
		return static::date($value);
	}

	/**
	 * Checks for a valid Url
	 */
	public static function url($value): bool
	{
		// In search for the perfect regular expression: https://mathiasbynens.be/demo/url-regex
		// Added localhost support and removed 127.*.*.* ip restriction
		$regex = '_^(?:(?:https?|ftp):\/\/)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:localhost)|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:\/[^\s]*)?$_iu';
		return preg_match($regex, $value ?? '') !== 0;
	}

	/**
	 * Checks for a valid Uuid, optionally for specific model type
	 */
	public static function uuid(
		string $value,
		string|array|null $type = null
	): bool {
		return Uuid::is($value, $type);
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

		foreach ($rules as $validator => $options) {
			if (is_int($validator) === true) {
				$validator = $options;
				$options   = [];
			}

			$options   = A::wrap($options);
			$validator = strtolower($validator);

			if (static::$validator($value, ...$options) === false) {
				$errors[$validator] =
					$messages[$validator] ??
					static::message($validator, $value, ...$options);

				if ($fail === true) {
					throw new Exception($errors[$validator]);
				}
			}
		}

		if ($errors === []) {
			return true;
		}

		return $errors;
	}
}
