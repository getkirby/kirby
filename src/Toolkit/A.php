<?php

namespace Kirby\Toolkit;

use Exception;

/**
 * The `A` class provides a set of handy methods
 * to simplify array handling and make it more
 * consistent. The class contains methods for
 * fetching elements from arrays, merging and
 * sorting or shuffling arrays.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class A
{

    /**
     * Appends the given array
     *
     * @param array $array
     * @param array $append
     * @return array
     */
    public static function append(array $array, array $append): array
    {
        return $array + $append;
    }

    /**
     * Gets an element of an array by key
     *
     * <code>
     * $array = [
     *   'cat'  => 'miao',
     *   'dog'  => 'wuff',
     *   'bird' => 'tweet'
     * ];
     *
     * echo A::get($array, 'cat');
     * // output: 'miao'
     *
     * echo A::get($array, 'elephant', 'shut up');
     * // output: 'shut up'
     *
     * $catAndDog = A::get($array, ['cat', 'dog']);
     * // result: ['cat' => 'miao', 'dog' => 'wuff'];
     * </code>
     *
     * @param   array  $array   The source array
     * @param   mixed  $key     The key to look for
     * @param   mixed  $default Optional default value, which should be
     *                          returned if no element has been found
     * @return  mixed
     */
    public static function get(array $array, $key, $default = null)
    {
        // return the entire array if the key is null
        if ($key === null) {
            return $array;
        }

        // get an array of keys
        if (is_array($key) === true) {
            $result = [];
            foreach ($key as $k) {
                $result[$k] = static::get($array, $k, $default);
            }
            return $result;
        }

        if (isset($array[$key]) === true) {
            return $array[$key];
        }

        // support dot notation
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);

            foreach ($keys as $innerKey) {
                if (isset($array[$innerKey]) === false) {
                    return $default;
                }

                $array = $array[$innerKey];
            }

            return $array;
        }

        return $default;
    }

    /**
     * @return string
     */
    public static function join($value, $separator = ', ')
    {
        if (is_string($value) === true) {
            return $value;
        }
        return implode($separator, $value);
    }

    const MERGE_OVERWRITE = 0;
    const MERGE_APPEND    = 1;
    const MERGE_REPLACE   = 2;

    /**
     * Merges arrays recursively
     *
     * @param  array   $array1
     * @param  array   $array2
     * @param  boolean $mode   Behavior for elements with numeric keys;
     *                         A::MERGE_APPEND:    elements are appended, keys are reset;
     *                         A::MERGE_OVERWRITE: elements are overwritten, keys are preserved
     *                         A::MERGE_REPLACE:   non-associative arrays are completely replaced
     * @return array
     */
    public static function merge($array1, $array2, $mode = A::MERGE_APPEND)
    {
        $merged = $array1;

        if (static::isAssociative($array1) === false && $mode === static::MERGE_REPLACE) {
            return $array2;
        }

        foreach ($array2 as $key => $value) {

            // append to the merged array, don't overwrite numeric keys
            if (is_int($key) === true && $mode == static::MERGE_APPEND) {
                $merged[] = $value;

            // recursively merge the two array values
            } elseif (is_array($value) === true && isset($merged[$key]) === true && is_array($merged[$key]) === true) {
                $merged[$key] = static::merge($merged[$key], $value, $mode);

            // simply overwrite with the value from the second array
            } else {
                $merged[$key] = $value;
            }
        }

        if ($mode == static::MERGE_APPEND) {
            // the keys don't make sense anymore, reset them
            // array_merge() is the simplest way to renumber
            // arrays that have both numeric and string keys;
            // besides the keys, nothing changes here
            $merged = array_merge($merged, []);
        }

        return $merged;
    }

    /**
     * Plucks a single column from an array
     *
     * <code>
     * $array[] = [
     *   'id' => 1,
     *   'username' => 'homer',
     * ];
     *
     * $array[] = [
     *   'id' => 2,
     *   'username' => 'marge',
     * ];
     *
     * $array[] = [
     *   'id' => 3,
     *   'username' => 'lisa',
     * ];
     *
     * var_dump(A::pluck($array, 'username'));
     * // result: ['homer', 'marge', 'lisa'];
     * </code>
     *
     * @param   array   $array   The source array
     * @param   string  $key     The key name of the column to extract
     * @return  array            The result array with all values
     *                           from that column.
     */
    public static function pluck(array $array, string $key)
    {
        $output = [];
        foreach ($array as $a) {
            if (isset($a[$key]) === true) {
                $output[] = $a[$key];
            }
        }

        return $output;
    }

    /**
     * Prepends the given array
     *
     * @param array $array
     * @param array $prepend
     * @return array
     */
    public static function prepend(array $array, array $prepend): array
    {
        return $prepend + $array;
    }

    /**
     * Shuffles an array and keeps the keys
     *
     * <code>
     * $array = [
     *   'cat'  => 'miao',
     *   'dog'  => 'wuff',
     *   'bird' => 'tweet'
     * ];
     *
     * $shuffled = A::shuffle($array);
     * // output: [
     * //    'dog' => 'wuff',
     * //    'cat' => 'miao',
     * //    'bird' => 'tweet'
     * // ];
     * </code>
     *
     * @param   array  $array The source array
     * @return  array  The shuffled result array
     */
    public static function shuffle(array $array): array
    {
        $keys = array_keys($array);
        $new  = [];

        shuffle($keys);

        // resort the array
        foreach ($keys as $key) {
            $new[$key] = $array[$key];
        }

        return $new;
    }

    /**
     * Returns the first element of an array
     *
     * <code>
     * $array = [
     *   'cat'  => 'miao',
     *   'dog'  => 'wuff',
     *   'bird' => 'tweet'
     * ];
     *
     * $first = A::first($array);
     * // first: 'miao'
     * </code>
     *
     * @param   array  $array    The source array
     * @return  mixed            The first element
     */
    public static function first(array $array)
    {
        return array_shift($array);
    }

    /**
     * Returns the last element of an array
     *
     * <code>
     * $array = [
     *   'cat'  => 'miao',
     *   'dog'  => 'wuff',
     *   'bird' => 'tweet'
     * ];
     *
     * $last = A::last($array);
     * // last: 'tweet'
     * </code>
     *
     * @param   array  $array    The source array
     * @return  mixed            The last element
     */
    public static function last(array $array)
    {
        return array_pop($array);
    }

    /**
     * Fills an array up with additional elements to certain amount.
     *
     * <code>
     * $array = [
     *   'cat'  => 'miao',
     *   'dog'  => 'wuff',
     *   'bird' => 'tweet'
     * ];
     *
     * $result = A::fill($array, 5, 'elephant');
     *
     * // result: [
     * //   'cat',
     * //   'dog',
     * //   'bird',
     * //   'elephant',
     * //   'elephant',
     * // ];
     * </code>
     *
     * @param   array  $array  The source array
     * @param   int    $limit  The number of elements the array should
     *                         contain after filling it up.
     * @param   mixed  $fill   The element, which should be used to
     *                         fill the array
     * @return  array          The filled-up result array
     */
    public static function fill(array $array, int $limit, $fill = 'placeholder'): array
    {
        if (count($array) < $limit) {
            $diff = $limit - count($array);
            for ($x = 0; $x < $diff; $x++) {
                $array[] = $fill;
            }
        }
        return $array;
    }

    /**
     * Move an array item to a new index
     *
     * @param array $array
     * @param int $from
     * @param int $to
     * @return array
     */
    public static function move(array $array, int $from, int $to): array
    {
        $total = count($array);

        if ($from >= $total || $from < 0) {
            throw new Exception('Invalid "from" index');
        }

        if ($to >= $total || $to < 0) {
            throw new Exception('Invalid "to" index');
        }

        // remove the item from the array
        $item = array_splice($array, $from, 1);

        // inject it at the new position
        array_splice($array, $to, 0, $item);

        return $array;
    }

    /**
     * Checks for missing elements in an array
     *
     * This is very handy to check for missing
     * user values in a request for example.
     *
     * <code>
     * $array = [
     *   'cat'  => 'miao',
     *   'dog'  => 'wuff',
     *   'bird' => 'tweet'
     * ];
     *
     * $required = ['cat', 'elephant'];
     *
     * $missng = A::missing($array, $required);
     * // missing: [
     * //    'elephant'
     * // ];
     * </code>
     *
     * @param   array  $array    The source array
     * @param   array  $required An array of required keys
     * @return  array            An array of missing fields. If this
     *                           is empty, nothing is missing.
     */
    public static function missing(array $array, array $required = []): array
    {
        $missing = [];
        foreach ($required as $r) {
            if (isset($array[$r]) === false) {
                $missing[] = $r;
            }
        }
        return $missing;
    }

    /**
     * Sorts a multi-dimensional array by a certain column
     *
     * <code>
     * $array[0] = [
     *   'id' => 1,
     *   'username' => 'mike',
     * ];
     *
     * $array[1] = [
     *   'id' => 2,
     *   'username' => 'peter',
     * ];
     *
     * $array[3] = [
     *   'id' => 3,
     *   'username' => 'john',
     * ];
     *
     * $sorted = A::sort($array, 'username ASC');
     * // Array
     * // (
     * //      [0] => Array
     * //          (
     * //              [id] => 3
     * //              [username] => john
     * //          )
     * //      [1] => Array
     * //          (
     * //              [id] => 1
     * //              [username] => mike
     * //          )
     * //      [2] => Array
     * //          (
     * //              [id] => 2
     * //              [username] => peter
     * //          )
     * // )
     *
     * </code>
     *
     * @param   array   $array The source array
     * @param   string  $field The name of the column
     * @param   string  $direction desc (descending) or asc (ascending)
     * @param   int     $method A PHP sort method flag or 'natural' for
     *                          natural sorting, which is not supported in
     *                          PHP by sort flags
     * @return  array   The sorted array
     */
    public static function sort(array $array, string $field, string $direction = 'desc', $method = SORT_REGULAR): array
    {
        $direction = strtolower($direction) == 'desc' ? SORT_DESC : SORT_ASC;
        $helper    = [];
        $result    = [];

        // build the helper array
        foreach ($array as $key => $row) {
            $helper[$key] = $row[$field];
        }

        // natural sorting
        if ($direction === SORT_DESC) {
            arsort($helper, $method);
        } else {
            asort($helper, $method);
        }

        // rebuild the original array
        foreach ($helper as $key => $val) {
            $result[$key] = $array[$key];
        }

        return $result;
    }

    /**
     * Checks wether an array is associative or not
     *
     * <code>
     * $array = ['a', 'b', 'c'];
     *
     * A::isAssociative($array);
     * // returns: false
     *
     * $array = ['a' => 'a', 'b' => 'b', 'c' => 'c'];
     *
     * A::isAssociative($array);
     * // returns: true
     * </code>
     *
     * @param   array    $array The array to analyze
     * @return  boolean  true: The array is associative false: It's not
     */
    public static function isAssociative(array $array): bool
    {
        return ctype_digit(implode(null, array_keys($array))) === false;
    }

    /**
     * Returns the average value of an array
     *
     * @param   array  $array The source array
     * @param   int    $decimals The number of decimals to return
     * @return  float  The average value
     */
    public static function average(array $array, int $decimals = 0): float
    {
        return round((array_sum($array) / sizeof($array)), $decimals);
    }

    /**
     * Merges arrays recursively
     *
     * <code>
     * $defaults = [
     *   'username' => 'admin',
     *   'password' => 'admin',
     * ];
     *
     * $options = A::extend($defaults, ['password' => 'super-secret']);
     * // returns: [
     * //   'username' => 'admin',
     * //   'password' => 'super-secret'
     * // ];
     * </code>
     *
     * @return array
     */
    public static function extend(...$arrays): array
    {
        return array_merge_recursive(...$arrays);
    }

    /**
     * Update an array with a second array
     * The second array can contain callbacks as values,
     * which will get the original values as argument
     *
     * <code>
     * $user = [
     *   'username' => 'homer',
     *   'email'    => 'homer@simpsons.com'
     * ];
     *
     * // simple updates
     * A::update($user, [
     *   'username' => 'homer j. simpson'
     * ]);
     *
     * // with callback
     * A::update($user, [
     *   'username' => function ($username) {
     *     return $username . ' j. simpson'
     *   }
     * ]);
     * </code>
     *
     * @param  array $array
     * @param  array $update
     * @return array
     */
    public static function update(array $array, array $update): array
    {
        foreach ($update as $key => $value) {
            if (is_a($value, 'Closure') === true) {
                $array[$key] = call_user_func($value, static::get($array, $key));
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * Wraps the given value in an array
     * if it's not an array yet.
     *
     * @param mixed|null $array
     * @return array
     */
    public static function wrap($array = null): array
    {
        if ($array === null) {
            return [];
        } elseif (is_array($array) === false) {
            return [$array];
        } else {
            return $array;
        }
    }
}
