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
	 * @internal
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

		$nonExisting = in_array($type, ['array', 'object']) ? 'non-existing ' : '';

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
	 */
	public function resolve(mixed $base = null, array|object $data = []): mixed
	{
		// resolve arguments to array
		$args = $this->arguments?->resolve($data) ?? [];

		// 1st segment, start from $data array
		if ($this->position === 0) {
			if (is_array($data) == true) {
				return $this->resolveArray($data, $args);
			}

			return $this->resolveObject($data, $args);
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
		if (array_key_exists($this->method, $array) === false) {
			static::error($array, $this->method, 'property');
		}

		$value = $array[$this->method];

		if ($value instanceof Closure) {
			return $value(...$args);
		}

		if ($args !== []) {
			throw new InvalidArgumentException('Cannot access array element "' . $this->method . '" with arguments');
		}

		return $value;
	}

	/**
	 * Resolves segment by calling the method/accessing the property
	 * on the base object
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
			$args === [] && (
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
