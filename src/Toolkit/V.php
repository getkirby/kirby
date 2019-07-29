<?php

namespace Kirby\Toolkit;

use Exception;
use Kirby\Http\Idn;
use Kirby\Toolkit\Str;
use ReflectionFunction;
use Throwable;

/**
 * A set of validator methods
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class V
{

    /**
     * An array with all installed validators
     *
     * @var array
     */
    public static $validators = [];

    /**
     * Validates the given input with all passed rules
     * and returns an array with all error messages.
     * The array will be empty if the input is valid
     *
     * @param mixed $input
     * @param array $rules
     * @param array $messages
     * @return array
     */
    public static function errors($input, array $rules, $messages = []): array
    {
        $errors = static::value($input, $rules, $messages, false);

        return $errors === true ? [] : $errors;
    }

    /**
     * Creates a useful error message for the given validator
     * and the arguments. This is used mainly internally
     * to create error messages
     *
     * @param string $validatorName
     * @param mixed ...$params
     * @return string|null
     */
    public static function message(string $validatorName, ...$params): ?string
    {
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
                    foreach ($value as $index => $item) {
                        if (is_array($item) === true) {
                            $value[$index] = implode('|', $item);
                        }
                    }
                    $value = implode(', ', $value);
                } catch (Throwable $e) {
                    $value = '-';
                }
            }

            $arguments[$parameter->getName()] = $value;
        }

        return I18n::template($translationKey, 'The "' . $validatorName . '" validation failed', $arguments);
    }

    /**
     * Return the list of all validators
     *
     * @return array
     */
    public static function validators(): array
    {
        return static::$validators;
    }

    /**
     * Validate a single value against
     * a set of rules, using all registered
     * validators
     *
     * @param  mixed    $value
     * @param  array    $rules
     * @param  array    $messages
     * @param  boolean  $fail
     * @return boolean|array
     */
    public static function value($value, array $rules, array $messages = [], bool $fail = true)
    {
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
     *
     * @param  array    $input
     * @param  array    $rules
     * @return boolean
     */
    public static function input(array $input, array $rules): bool
    {
        foreach ($rules as $fieldName => $fieldRules) {
            $fieldValue = $input[$fieldName] ?? null;

            // first check for required fields
            if (($fieldRules['required'] ?? false) === true && $fieldValue === null) {
                throw new Exception(sprintf('The "%s" field is missing', $fieldName));
            }

            // remove the required rule
            unset($fieldRules['required']);

            // skip validation for empty fields
            if ($fieldValue === null) {
                continue;
            }

            try {
                V::value($fieldValue, $fieldRules);
            } catch (Exception $e) {
                throw new Exception(sprintf($e->getMessage() . ' for field "%s"', $fieldName));
            }

            static::value($fieldValue, $fieldRules);
        }

        return true;
    }

    /**
     * Calls an installed validator and passes all arguments
     *
     * @param  string   $method
     * @param  array    $arguments
     * @return boolean
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
    'alpha' => function ($value): bool {
        return V::match($value, '/^([a-z])+$/i') === true;
    },

    /**
     * Valid: `a-z | A-Z | 0-9`
     */
    'alphanum' => function ($value): bool {
        return V::match($value, '/^[a-z0-9]+$/i') === true;
    },

    /**
     * Checks for numbers within the given range
     */
    'between' => function ($value, $min, $max): bool {
        return V::min($value, $min) === true &&
               V::max($value, $max) === true;
    },

    /**
     * Checks if the given string contains the given value
     */
    'contains' => function ($value, $needle): bool {
        return Str::contains($value, $needle);
    },

    /**
     * Checks for a valid date
     */
    'date' => function ($value): bool {
        $date = date_parse($value);
        return ($date !== false &&
                $date['error_count'] === 0 &&
                $date['warning_count'] === 0);
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
                $parts   = Str::split($value, '@');
                $address = $parts[0] ?? null;
                $domain  = Idn::encode($parts[1] ?? '');
                $email   = $address . '@' . $domain;
            } catch (Throwable $e) {
                return false;
            }

            return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        }

        return true;
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
        return V::match($value, '/^[a-z0-9@._-]+$/i') === true &&
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
     * Checks if the value is present in the given array
     */
    'required' => function ($key, array $array): bool {
        return isset($array[$key]) === true &&
               V::notIn($array[$key], [null, '', []]) === true;
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
        if (is_numeric($value) === true) {
            $count = $value;
        } elseif (is_string($value) === true) {
            $count = Str::length(trim($value));
        } elseif (is_array($value) === true) {
            $count = count($value);
        } elseif (is_object($value) === true) {
            if ($value instanceof \Countable) {
                $count = count($value);
            } elseif (method_exists($value, 'count') === true) {
                $count = $value->count();
            } else {
                throw new Exception('$value is an uncountable object');
            }
        } else {
            throw new Exception('$value is of type without size');
        }

        switch ($operator) {
            case '<':
                return $count < $size;
            case '>':
                return $count > $size;
            case '<=':
                return $count <= $size;
            case '>=':
                return $count >= $size;
            default:
                return $count == $size;
        }
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
        $regex = '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iu';
        return preg_match($regex, $value) !== 0;
    }
];
