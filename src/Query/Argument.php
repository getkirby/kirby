<?php

namespace Kirby\Query;

/**
 * The Argument class represents a single
 * parameter passed to a method in a chained query
 *
 * @package   Kirby Query
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
final class Argument
{
	public function __construct(
		public mixed $value
	) {
	}

	/**
	 * Sanitizes argument string into
	 * PHP type/object as new Argument instance
	 */
	public static function factory(string $argument): static
	{
		$argument = trim($argument);

		// string with single or double quotes
		if (
			(
				substr($argument, 0, 1) === '"' &&
				substr($argument, -1) === '"'
			) || (
				substr($argument, 0, 1) === "'" &&
				substr($argument, -1) === "'"
			)
		) {
			$string = substr($argument, 1, -1);
			$string = str_replace(['\"', "\'"], ['"', "'"], $string);
			return new static($string);
		}

		// array: split and recursive sanitizing
		if (
			substr($argument, 0, 1) === '[' &&
			substr($argument, -1) === ']'
		) {
			$array = substr($argument, 1, -1);
			$array = Arguments::factory($array);
			return new static($array);
		}

		// numeric
		if (is_numeric($argument) === true) {
			return new static((float)$argument);
		}

		return new static(match ($argument) {
			'null'  => null,
			'true'  => true,
			'false' => false,

			// resolve parameter for objects and methods itself
			default => new Query($argument)
		});
	}

	/**
	 * Return the argument value and
	 * resolves nested objects to scaler types
	 */
	public function resolve(array|object $data = []): mixed
	{
		if (is_object($this->value) === true) {
			return $this->value->resolve($data);
		}

		return $this->value;
	}
}
