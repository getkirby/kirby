<?php

namespace Kirby\Toolkit;

use Kirby\Exception\InvalidArgumentException;
use stdClass;

/**
 * Super simple stdClass extension with
 * magic getter methods for all properties
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Obj extends stdClass
{
	public function __construct(array $data = [])
	{
		foreach ($data as $key => $val) {
			$this->$key = $val;
		}
	}

	/**
	 * Magic getter
	 */
	public function __call(string $property, array $arguments): mixed
	{
		return $this->$property ?? null;
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	public function __get(string $property): mixed
	{
		return null;
	}

	public function __set(string $property, mixed $value): void
	{
		$this->{$property} = $value;
	}

	/**
	 * Gets one or multiple properties of the object
	 *
	 * @param $fallback If multiple properties are requested:
	 *                 Associative array of fallback values per key
	 */
	public function get(string|array $property, mixed $fallback = null): mixed
	{
		if (is_array($property) === true) {
			$fallback ??= [];

			if (is_array($fallback) === false) {
				throw new InvalidArgumentException(
					message: 'The fallback value must be an array when getting multiple properties'
				);
			}

			$result = [];

			foreach ($property as $key) {
				$result[$key] = $this->$key ?? $fallback[$key] ?? null;
			}

			return $result;
		}

		return $this->$property ?? $fallback;
	}

	/**
	 * Converts the object to an array
	 */
	public function toArray(): array
	{
		$result = [];

		foreach ((array)$this as $key => $value) {
			$result[$key] = is_object($value) === true && method_exists($value, 'toArray') === true
				? $value->toArray()
				: $value;
		}

		return $result;
	}

	/**
	 * Converts the object to a json string
	 *
	 * @return non-empty-string
	 * @throws \JsonException for invalid JSON
	 */
	public function toJson(int $flags = 0, int $depth = 512): string
	{
		/** @var non-empty-string $json */
		$json = json_encode(
			$this->toArray(),
			$flags | JSON_THROW_ON_ERROR,
			$depth
		);

		return $json;
	}

	/**
	 * Returns the property names
	 *
	 * @return list<int|string>
	 */
	public function toKeys(): array
	{
		return array_keys((array)$this);
	}
}
