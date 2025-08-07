<?php

namespace Kirby\Query;

use Closure;
use Kirby\Exception\BadMethodCallException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

/**
 * The Segment class represents a single
 * part of a chained query
 *
 * @package   Kirby Query
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * @todo Deprecate in v6
 */
class Segment
{
	public function __construct(
		public string $method,
		public int $position,
		public Arguments|null $arguments = null,
	) {
	}

	/**
	 * Throws an exception for an access to an invalid method
	 * @unstable
	 *
	 * @param mixed $data Variable on which the access was tried
	 * @param string $name Name of the method/property that was accessed
	 * @param string $label Type of the name (`method`, `property` or `method/property`)
	 *
	 * @throws \Kirby\Exception\BadMethodCallException
	 */
	public static function error(mixed $data, string $name, string $label): void
	{
		$type = strtolower(gettype($data));

		if ($type === 'double') {
			$type = 'float';
		}

		$nonExisting = in_array($type, ['array', 'object'], true) ? 'non-existing ' : '';

		$error = 'Access to ' . $nonExisting . $label . ' "' . $name . '" on ' . $type;

		throw new BadMethodCallException($error);
	}

	/**
	 * Parses a segment into the property/method name and its arguments
	 *
	 * @param int $position String position of the segment inside the full query
	 */
	public static function factory(
		string $segment,
		int $position = 0
	): static {
		if (Str::endsWith($segment, ')') === false) {
			return new static(method: $segment, position: $position);
		}

		// the args are everything inside the *outer* parentheses
		$args = Str::substr($segment, Str::position($segment, '(') + 1, -1);

		return new static(
			method:    Str::before($segment, '('),
			position:  $position,
			arguments: Arguments::factory($args)
		);
	}

	/**
	 * Automatically resolves the segment depending on the
	 * segment position and the type of the base
	 *
	 * @param mixed $base Current value of the query chain
	 */
	public function resolve(mixed $base = null, array|object $data = []): mixed
	{
		// resolve arguments to array
		$args = $this->arguments?->resolve($data) ?? [];

		// 1st segment, use $data as base
		if ($this->position === 0) {
			$base = $data;
		}

		if (is_array($base) === true) {
			return $this->resolveArray($base, $args);
		}

		if (is_object($base) === true) {
			return $this->resolveObject($base, $args);
		}

		// trying to access further segments on a scalar/null value
		static::error($base, $this->method, 'method/property');
	}

	/**
	 * Resolves segment by calling the corresponding array key
	 */
	protected function resolveArray(array $array, array $args): mixed
	{
		// the directly provided array takes precedence
		// to look up a matching entry
		if (array_key_exists($this->method, $array) === true) {
			$value = $array[$this->method];

			// if this is a Closure we can directly use it, as
			// Closures from the $array should always have priority
			// over the Query::$entries Closures
			if ($value instanceof Closure) {
				return $value(...$args);
			}

			// if we have no arguments to pass, we also can directly
			// use the value from the $array as it must not be different
			// to the one from Query::$entries with the same name
			if ($args === []) {
				return $value;
			}
		}

		// fallback time: only if we are handling the first segment,
		// we can also try to resolve the segment with an entry from the
		// default Query::$entries
		if ($this->position === 0) {
			if (array_key_exists($this->method, Query::$entries) === true) {
				return Query::$entries[$this->method](...$args);
			}
		}

		// if we have not been able to return anything so far,
		// we just need to differntiate between two different error messages

		// this one is in case the original array contained the key,
		// but was not a Closure while the segment had arguments
		if (
			array_key_exists($this->method, $array) &&
			$args !== []
		) {
			throw new InvalidArgumentException(
				message: 'Cannot access array element "' . $this->method . '" with arguments'
			);
		}

		// last, the standard error for trying to access something
		// that does not exist
		static::error($array, $this->method, 'property');
	}

	/**
	 * Resolves segment by calling the method/
	 * accessing the property on the base object
	 */
	protected function resolveObject(object $object, array $args): mixed
	{
		if (
			method_exists($object, $this->method) === true ||
			method_exists($object, '__call') === true
		) {
			return $object->{$this->method}(...$args);
		}

		if (
			$args === [] &&
			(
				property_exists($object, $this->method) === true ||
				method_exists($object, '__get') === true
			)
		) {
			return $object->{$this->method};
		}

		$label = ($args === []) ? 'method/property' : 'method';
		static::error($object, $this->method, $label);
	}
}
