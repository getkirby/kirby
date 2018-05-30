<?php

namespace Kirby\Toolkit;

use Exception;
use Kirby\Image\Image;
use Kirby\Toolkit\Str;

/**
* A set of validator methods
*
* @package   Kirby Toolkit
* @author    Bastian Allgeier <bastian@getkirby.com>
* @link      http://getkirby.com
* @copyright Bastian Allgeier
* @license   MIT
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
     * @return boolean
     */
    public static function value($value, array $rules): bool
    {
        foreach ($rules as $validatorName => $validatorOptions) {
            if (is_int($validatorName)) {
                $validatorName    = $validatorOptions;
                $validatorOptions = [];
            }

            if (is_array($validatorOptions) === false) {
                $validatorOptions = [$validatorOptions];
            }

            if (static::$validatorName($value, ...$validatorOptions) === false) {
                throw new Exception(sprintf('The "%s" validator failed', $validatorName));
            }
        }

        return true;
    }

    /**
     * Validate an input array against
     * a set of rules, usin all registered
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
                throw new Exception(sprintf($e->getMessage() . ' failed for field "%s"', $fieldName));
            }

            foreach ($fieldRules as $validatorName => $validatorOptions) {
                V::value();


                if (is_int($validatorName)) {
                    $validatorName    = $validatorOptions;
                    $validatorOptions = [];
                }

                if (is_array($validatorOptions) === false) {
                    $validatorOptions = [$validatorOptions];
                }

                if (static::$validatorName($fieldValue, ...$validatorOptions) === false) {
                    throw new Exception(sprintf('The "%s" validator failed for field "%s"', $validatorName, $fieldName));
                }
            }
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
        // check for missing validators
        if (isset(static::$validators[$method]) === false) {
            throw new Exception('The validator does not exist: ' . $method);
        }

        return call_user_func_array(static::$validators[$method], $arguments);
    }
}


/**
 * Default set of validators
 */
V::$validators = [
    'accepted' => function ($value): bool {
        return V::in($value, [1, true, 'yes', 'true', '1', 'on'], true) === true;
    },
    'alpha' => function ($value): bool {
        return V::match($value, '/^([a-z])+$/i') === true;
    },
    'alphanum' => function ($value): bool {
        return V::match($value, '/^[a-z0-9]+$/i') === true;
    },
    'between' => function ($value, $min, $max): bool {
        return V::min($value, $min) === true &&
               V::max($value, $max) === true;
    },
    'contains' => function ($value, $needle): bool {
        return Str::contains($value, $needle);
    },
    'date' => function ($value): bool {
        $date = date_parse($value);
        return ($date !== false &&
                $date['error_count'] === 0 &&
                $date['warning_count'] === 0);
    },
    'denied' => function ($value): bool {
        return V::in($value, [0, false, 'no', 'false', '0', 'off'], true) === true;
    },
    'different' => function ($value, $other, $strict = false): bool {
        if ($strict === true) {
            return $value !== $other;
        }
        return $value != $other;
    },
    'endsWith' => function (string $value, string $end): bool {
        return Str::endsWith($value, $end);
    },
    'email' => function ($value): bool {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    },
    'filename' => function ($value): bool {
        return V::match($value, '/^[a-z0-9@._-]+$/i') === true &&
               V::min($value, 2) === true;
    },
    'in' => function ($value, array $in, bool $strict = false): bool {
        return in_array($value, $in, $strict) === true;
    },
    'integer' => function ($value, bool $strict = false): bool {
        if ($strict === true) {
            return is_int($value) === true;
        }
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    },
    'ip' => function ($value): bool {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    },
    'less' => function ($value, float $max): bool {
        return V::size($value, $max, '<') === true;
    },
    'match' => function ($value, string $preg): bool {
        return preg_match($preg, $value) !== 0;
    },
    'max' => function ($value, float $max): bool {
        return V::size($value, $max, '<=') === true;
    },
    'min' => function ($value, float $min): bool {
        return V::size($value, $min, '>=') === true;
    },
    'maxLength' => function (string $value = null, $max): bool {
        return Str::length(trim($value)) <= $max;
    },
    'minLength' => function (string $value = null, $min): bool {
        return Str::length(trim($value)) >= $min;
    },
    'maxWords' => function (string $value = null, $max): bool {
        return V::max(explode(' ', trim($value)), $max) === true;
    },
    'minWords' => function (string $value = null, $min): bool {
        return V::min(explode(' ', trim($value)), $min) === true;
    },
    'more' => function ($value, float $min): bool {
        return V::size($value, $min, '>') === true;
    },
    'notContains' => function ($value, $needle): bool {
        return V::contains($value, $needle) === false;
    },
    'notIn' => function ($value, $notIn): bool {
        return V::in($value, $notIn) === false;
    },
    'num' => function ($value): bool {
        return is_numeric($value) === true;
    },
    'required' => function ($key, array $array): bool {
        return isset($array[$key]) === true &&
               V::notIn($array[$key], [null, '', []]) === true;
    },
    'same' => function ($value, $other, bool $strict = false): bool {
        if ($strict === true) {
            return $value === $other;
        }
        return $value == $other;
    },
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
    'startsWith' => function (string $value, string $start): bool {
        return Str::startsWith($value, $start);
    },
    'time' => function ($value): bool {
        return V::date($value);
    },
    'url' => function ($value): bool {
        // In search for the perfect regular expression: https://mathiasbynens.be/demo/url-regex
        $regex = '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iu';
        return preg_match($regex, $value) !== 0;
    }
];
