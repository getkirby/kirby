<?php

namespace Kirby\Toolkit;

use Closure;
use Exception;
use InvalidArgumentException;

/**
 * The `A` class provides a set of handy methods
 * to simplify array handling and make it more
 * consistent. The class contains methods for
 * fetching elements from arrays, merging and
 * sorting or shuffling arrays.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class A
{
	/**
	 * Appends the given array
	 */
	public static function append(array $array, array $append): array
	{
		return static::merge($array, $append, A::MERGE_APPEND);
	}

	/**
	 * Recursively loops through the array and
	 * resolves any item defined as `Closure`,
	 * applying the passed parameters
	 * @since 3.5.6
	 *
	 * @param mixed ...$args Parameters to pass to the closures
	 */
	public static function apply(array $array, mixed ...$args): array
	{
		array_walk_recursive($array, function (&$item) use ($args) {
			if ($item instanceof Closure) {
				$item = $item(...$args);
			}
		});

		return $array;
	}

	/**
	 * Returns the average value of an array
	 *
	 * @param array $array The source array
	 * @param int $decimals The number of decimals to return
	 * @return float|null The average value
	 */
	public static function average(array $array, int $decimals = 0): float|null
	{
		if ($array === []) {
			return null;
		}

		return round((array_sum($array) / sizeof($array)), $decimals);
	}

	/**
	 * Counts the number of elements in an array
	 */
	public static function count(array $array): int
	{
		return count($array);
	}

	/**
	 * Merges arrays recursively
	 *
	 * ```php
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
	 * ```
	 *
	 * @psalm-suppress NamedArgumentNotAllowed
	 */
	public static function extend(array ...$arrays): array
	{
		return array_merge_recursive(...$arrays);
	}

	/**
	 * Checks if every element in the array passes the test
	 *
	 * ```php
	 * $array = [1, 30, 39, 29, 10, 13];
	 *
	 * $isBelowThreshold = fn($value) => $value < 40;
	 * echo A::every($array, $isBelowThreshold) ? 'true' : 'false';
	 * // output: 'true'
	 *
	 * $isIntegerKey = fn($value, $key) => is_int($key);
	 * echo A::every($array, $isIntegerKey) ? 'true' : 'false';
	 * // output: 'true'
	 * ```
	 *
	 * @since 3.9.8
	 * @param callable(mixed $value, int|string $key, array $array):bool $test
	 */
	public static function every(array $array, callable $test): bool
	{
		foreach ($array as $key => $value) {
			if (!$test($value, $key, $array)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Fills an array up with additional elements to certain amount.
	 *
	 * ```php
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
	 * ```
	 *
	 * @param array $array The source array
	 * @param int $limit The number of elements the array should
	 *                   contain after filling it up.
	 * @param mixed $fill The element, which should be used to
	 *                    fill the array. If it's a callable, it
	 *                    will be called with the current index
	 * @return array The filled-up result array
	 */
	public static function fill(
		array $array,
		int $limit,
		mixed $fill = 'placeholder'
	): array {
		for ($x = count($array); $x < $limit; $x++) {
			$array[] = is_callable($fill) ? $fill($x) : $fill;
		}

		return $array;
	}

	/**
	 * Filter the array using the given callback
	 * using both value and key
	 * @since 3.6.5
	 */
	public static function filter(array $array, callable $callback): array
	{
		return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
	}

	/**
	 * Finds the first element matching the given callback
	 *
	 * ```php
	 * $array = [1, 30, 39, 29, 10, 13];
	 *
	 * $isAboveThreshold = fn($value) => $value > 30;
	 * echo A::find($array, $isAboveThreshold);
	 * // output: '39'
	 *
	 * $array = [
	 *   'cat' => 'miao',
	 *   'cow' => 'moo',
	 *   'colibri' => 'humm',
	 *   'dog' => 'wuff',
	 *   'chicken' => 'cluck',
	 *   'bird' => 'tweet'
	 * ];
	 *
	 * $keyNotStartingWithC = fn($value, $key) => $key[0] !== 'c';
	 * echo A::find($array, $keyNotStartingWithC);
	 * // output: 'wuff'
	 * ```
	 *
	 * @since 3.9.8
	 * @param callable(mixed $value, int|string $key, array $array):bool $callback
	 */
	public static function find(array $array, callable $callback): mixed
	{
		foreach ($array as $key => $value) {
			if ($callback($value, $key, $array)) {
				return $value;
			}
		}

		return null;
	}

	/**
	 * Returns the first element of an array
	 *
	 * ```php
	 * $array = [
	 *   'cat'  => 'miao',
	 *   'dog'  => 'wuff',
	 *   'bird' => 'tweet'
	 * ];
	 *
	 * $first = A::first($array);
	 * // first: 'miao'
	 * ```
	 *
	 * @param array $array The source array
	 * @return mixed The first element
	 */
	public static function first(array $array): mixed
	{
		return array_shift($array);
	}

	/**
	 * Gets an element of an array by key
	 *
	 * ```php
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
	 * ```
	 *
	 * @param array $array The source array
	 * @param string|int|array|null $key The key to look for
	 * @param mixed $default Optional default value, which
	 *                       should be returned if no element
	 *                       has been found
	 */
	public static function get(
		array $array,
		string|int|array|null $key,
		mixed $default = null
	) {
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

		// extract data from nested array structures using the dot notation
		if (str_contains($key, '.') === true) {
			$keys     = explode('.', $key);
			$firstKey = array_shift($keys);

			// if the input array also uses dot notation,
			// try to find a subset of the $keys
			if (isset($array[$firstKey]) === false) {
				$currentKey = $firstKey;

				while ($innerKey = array_shift($keys)) {
					$currentKey .= '.' . $innerKey;

					// the element needs to exist and also needs
					// to be an array; otherwise we cannot find the
					// remaining keys within it (invalid array structure)
					if (
						isset($array[$currentKey]) === true &&
						is_array($array[$currentKey]) === true
					) {
						// $keys only holds the remaining keys
						// that have not been shifted off yet
						return static::get(
							$array[$currentKey],
							implode('.', $keys),
							$default
						);
					}
				}

				// searching through the full chain of keys wasn't successful
				return $default;
			}

			// if the input array uses a completely nested structure,
			// recursively progress layer by layer
			if (is_array($array[$firstKey]) === true) {
				return static::get(
					$array[$firstKey],
					implode('.', $keys),
					$default
				);
			}

			// the $firstKey element was found, but isn't an array, so we cannot
			// find the remaining keys within it (invalid array structure)
			return $default;
		}

		return $default;
	}

	/**
	 * Checks if array has a value
	 */
	public static function has(
		array $array,
		mixed $value,
		bool $strict = false
	): bool {
		return in_array($value, $array, $strict);
	}

	/**
	 * Join array elements as a string,
	 * also supporting nested arrays
	 */
	public static function implode(
		array $array,
		string $separator = ''
	): string {
		$result = '';

		foreach ($array as $value) {
			if (empty($result) === false) {
				$result .= $separator;
			}

			if (is_array($value) === true) {
				$value = static::implode($value, $separator);
			}

			$result .= $value;
		}

		return $result;
	}

	/**
	 * Checks whether an array is associative or not
	 *
	 * ```php
	 * $array = ['a', 'b', 'c'];
	 *
	 * A::isAssociative($array);
	 * // returns: false
	 *
	 * $array = ['a' => 'a', 'b' => 'b', 'c' => 'c'];
	 *
	 * A::isAssociative($array);
	 * // returns: true
	 * ```
	 *
	 * @param array $array The array to analyze
	 * @return bool true: The array is associative false: It's not
	 */
	public static function isAssociative(array $array): bool
	{
		return ctype_digit(implode('', array_keys($array))) === false;
	}

	/**
	 * Joins the elements of an array to a string
	 */
	public static function join(
		array|string $value,
		string $separator = ', '
	): string {
		if (is_string($value) === true) {
			return $value;
		}

		return implode($separator, $value);
	}

	/**
	 * Takes an array and makes it associative by an argument.
	 * If the argument is a callable, it will be used to map the array.
	 * If it is a string, it will be used as a key to pluck from the array.
	 *
	 * ```php
	 * $array = [['id'=>1], ['id'=>2], ['id'=>3]];
	 * $keyed = A::keyBy($array, 'id');
	 *
	 * // Now you can access the array by the id
	 * ```
	 */
	public static function keyBy(array $array, string|callable $keyBy): array
	{
		$keys =
			is_callable($keyBy) ?
			static::map($array, $keyBy) :
			static::pluck($array, $keyBy);

		if (count($keys) !== count($array)) {
			throw new InvalidArgumentException(
				message: 'The "key by" argument must be a valid key or a callable'
			);
		}

		return array_combine($keys, $array);
	}

	/**
	 * Returns the last element of an array
	 *
	 * ```php
	 * $array = [
	 *   'cat'  => 'miao',
	 *   'dog'  => 'wuff',
	 *   'bird' => 'tweet'
	 * ];
	 *
	 * $last = A::last($array);
	 * // last: 'tweet'
	 * ```
	 *
	 * @param array $array The source array
	 * @return mixed The last element
	 */
	public static function last(array $array): mixed
	{
		return array_pop($array);
	}

	/**
	 * A simple wrapper around array_map
	 * with a sane argument order
	 * @since 3.6.0
	 */
	public static function map(array $array, callable $map): array
	{
		return array_map($map, $array);
	}

	public const MERGE_OVERWRITE = 0;
	public const MERGE_APPEND    = 1;
	public const MERGE_REPLACE   = 2;

	/**
	 * Merges arrays recursively
	 *
	 * If last argument is an integer, it defines the
	 * behavior for elements with numeric keys;
	 * - A::MERGE_OVERWRITE:  elements are overwritten, keys are preserved
	 * - A::MERGE_APPEND:     elements are appended, keys are reset;
	 * - A::MERGE_REPLACE:    non-associative arrays are completely replaced
	 */
	public static function merge(array|int ...$arrays): array
	{
		// get mode from parameters
		$last = A::last($arrays);
		$mode = is_int($last) ? array_pop($arrays) : A::MERGE_APPEND;

		// get the first two arrays that should be merged
		$merged = array_shift($arrays);
		$join   = array_shift($arrays);

		if (
			static::isAssociative($merged) === false &&
			$mode === static::MERGE_REPLACE
		) {
			$merged = $join;
		} else {
			foreach ($join as $key => $value) {
				// append to the merged array, don't overwrite numeric keys
				if (
					is_int($key) === true &&
					$mode === static::MERGE_APPEND
				) {
					$merged[] = $value;

				// recursively merge the two array values
				} elseif (
					is_array($value) === true &&
					isset($merged[$key]) === true &&
					is_array($merged[$key]) === true
				) {
					$merged[$key] = static::merge($merged[$key], $value, $mode);

				// simply overwrite with the value from the second array
				} else {
					$merged[$key] = $value;
				}
			}

			if ($mode === static::MERGE_APPEND) {
				// the keys don't make sense anymore, reset them
				// array_merge() is the simplest way to renumber
				// arrays that have both numeric and string keys;
				// besides the keys, nothing changes here
				$merged = array_merge($merged, []);
			}
		}

		// if more than two arrays need to be merged, add the result
		// as first array and the mode to the end and call the method again
		if ($arrays !== []) {
			array_unshift($arrays, $merged);
			array_push($arrays, $mode);
			return static::merge(...$arrays);
		}

		return $merged;
	}

	/**
	 * Plucks a single column from an array
	 *
	 * ```php
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
	 * ```
	 *
	 * @param array $array The source array
	 * @param string $key The key name of the column to extract
	 * @return array The result array with all values
	 *               from that column.
	 */
	public static function pluck(array $array, string $key): array
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
	 */
	public static function prepend(array $array, array $prepend): array
	{
		return static::merge($prepend, $array, A::MERGE_APPEND);
	}

	/**
	 * Reduce an array to a single value
	 */
	public static function reduce(
		array $array,
		callable $callback,
		$initial = null
	): mixed {
		return array_reduce($array, $callback, $initial);
	}

	/**
	 * Checks for missing elements in an array
	 *
	 * This is very handy to check for missing
	 * user values in a request for example.
	 *
	 * ```php
	 * $array = [
	 *   'cat'  => 'miao',
	 *   'dog'  => 'wuff',
	 *   'bird' => 'tweet'
	 * ];
	 *
	 * $required = ['cat', 'elephant'];
	 *
	 * $missing = A::missing($array, $required);
	 * // missing: [
	 * //    'elephant'
	 * // ];
	 * ```
	 *
	 * @param array $array The source array
	 * @param array $required An array of required keys
	 * @return array An array of missing fields. If this
	 *               is empty, nothing is missing.
	 */
	public static function missing(array $array, array $required = []): array
	{
		return array_values(array_diff($required, array_keys($array)));
	}

	/**
	 * Move an array item to a new index
	 */
	public static function move(array $array, int $from, int $to): array
	{
		$total = count($array);

		if ($from >= $total || $from < 0) {
			throw new Exception(message: 'Invalid "from" index');
		}

		if ($to >= $total || $to < 0) {
			throw new Exception(message: 'Invalid "to" index');
		}

		// remove the item from the array
		$item = array_splice($array, $from, 1);

		// inject it at the new position
		array_splice($array, $to, 0, $item);

		return $array;
	}


	/**
	 * Normalizes an array into a nested form by converting
	 * dot notation in keys to nested structures
	 *
	 * @param array $ignore List of keys in dot notation that should
	 *                      not be converted to a nested structure
	 */
	public static function nest(array $array, array $ignore = []): array
	{
		// convert a simple ignore list to a nested $key => true array
		if (isset($ignore[0]) === true) {
			$ignore = array_map(fn () => true, array_flip($ignore));
			$ignore = A::nest($ignore);
		}

		$result = [];

		foreach ($array as $fullKey => $value) {
			// extract the first part of a multi-level key, keep the others
			$subKeys = is_int($fullKey) ? [$fullKey] : explode('.', $fullKey);
			$key     = array_shift($subKeys);

			// skip the magic for ignored keys
			if (($ignore[$key] ?? null) === true) {
				$result[$fullKey] = $value;
				continue;
			}

			// untangle elements where the key uses dot notation
			if ($subKeys !== []) {
				$value = static::nestByKeys($value, $subKeys);
			}

			// now recursively do the same for each array level if needed
			if (is_array($value) === true) {
				$value = static::nest($value, $ignore[$key] ?? []);
			}

			// merge arrays with previous results if necessary
			// (needed when the same keys are used both with and without dot notation)
			if (
				is_array($result[$key] ?? null) === true &&
				is_array($value) === true
			) {
				$value = array_replace_recursive($result[$key], $value);
			}

			$result[$key] = $value;
		}

		return $result;
	}

	/**
	 * Recursively creates a nested array from a set of keys
	 * with a key on each level
	 *
	 * @param mixed $value Arbitrary value that will end up at the bottom of the tree
	 * @param array $keys List of keys to use sorted from the topmost level
	 * @return array|mixed Nested array or (if `$keys` is empty) the input `$value`
	 */
	public static function nestByKeys($value, array $keys)
	{
		// shift off the first key from the list
		$firstKey = array_shift($keys);

		// stop further recursion if there are no more keys
		if ($firstKey === null) {
			return $value;
		}

		// return one level of the output tree, recurse further
		return [
			$firstKey => static::nestByKeys($value, $keys)
		];
	}

	/**
	 * Returns a number of random elements from an array,
	 * either in original or shuffled order
	 *
	 * @throws \Exception When $count is larger than array length
	 */
	public static function random(
		array $array,
		int $count = 1,
		bool $shuffle = false
	): array {
		if ($count > count($array)) {
			throw new InvalidArgumentException(
				message: '$count is larger than available array items'
			);
		}

		if ($shuffle === true) {
			return array_slice(self::shuffle($array), 0, $count);
		}

		if ($count === 1) {
			$key = array_rand($array);
			return [$key => $array[$key]];
		}

		return self::get($array, array_rand($array, $count));
	}

	/**
	 * Shuffles an array and keeps the keys
	 *
	 * ```php
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
	 * ```
	 *
	 * @param array $array The source array
	 * @return array The shuffled result array
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
	 * Returns a slice of an array
	 */
	public static function slice(
		array $array,
		int $offset,
		int|null $length = null,
		bool $preserveKeys = false
	): array {
		return array_slice($array, $offset, $length, $preserveKeys);
	}

	/**
	 * Checks if at least one element in the array passes the test
	 *
	 * ```php
	 * $array = [1, 30, 39, 29, 10, 'foo' => 12, 13];
	 *
	 * $isAboveThreshold = fn($value) => $value > 30;
	 * echo A::some($array, $isAboveThreshold) ? 'true' : 'false';
	 * // output: 'true'
	 *
	 * $isStringKey = fn($value, $key) => is_string($key);
	 * echo A::some($array, $isStringKey) ? 'true' : 'false';
	 * // output: 'true'
	 * ```
	 *
	 * @since 3.9.8
	 * @param callable(mixed $value, int|string $key, array $array):bool $test
	 */
	public static function some(array $array, callable $test): bool
	{
		foreach ($array as $key => $value) {
			if ($test($value, $key, $array)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Sorts a multi-dimensional array by a certain column
	 *
	 * ```php
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
	 * ```
	 *
	 * @param array $array The source array
	 * @param string $field The name of the column
	 * @param string $direction desc (descending) or asc (ascending)
	 * @param int $method A PHP sort method flag or 'natural' for
	 *                    natural sorting, which is not supported in
	 *                    PHP by sort flags
	 * @return array The sorted array
	 */
	public static function sort(
		array $array,
		string $field,
		string $direction = 'desc',
		int $method = SORT_REGULAR
	): array {
		$direction = strtolower($direction) === 'desc' ? SORT_DESC : SORT_ASC;
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
		foreach (array_keys($helper) as $key) {
			$result[$key] = $array[$key];
		}

		return $result;
	}

	/**
	 * Sums an array
	 */
	public static function sum(array $array): int|float
	{
		return array_sum($array);
	}

	/**
	 * Update an array with a second array
	 * The second array can contain callbacks as values,
	 * which will get the original values as argument
	 *
	 * ```php
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
	 *   'username' => fn ($username) => $username . ' j. simpson'
	 * ]);
	 * ```
	 */
	public static function update(array $array, array $update): array
	{
		foreach ($update as $key => $value) {
			if ($value instanceof Closure) {
				$value = $value(static::get($array, $key));
			}

			$array[$key] = $value;
		}

		return $array;
	}

	/**
	 * Remove key(s) from an array
	 * @since 3.6.5
	 */
	public static function without(array $array, int|string|array $keys): array
	{
		if (is_int($keys) === true || is_string($keys) === true) {
			$keys = static::wrap($keys);
		}

		return static::filter(
			$array,
			fn ($value, $key) => in_array($key, $keys, true) === false
		);
	}

	/**
	 * Wraps the given value in an array
	 * if it's not an array yet.
	 */
	public static function wrap($array = null): array
	{
		if ($array === null) {
			return [];
		}

		if (is_array($array) === false) {
			return [$array];
		}

		return $array;
	}
}
