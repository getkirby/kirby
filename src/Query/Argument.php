<?php

namespace Kirby\Query;

use Closure;
use Kirby\Toolkit\Str;

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
class Argument
{
	public function __construct(
		public mixed $value
	) {
	}

	/**
	 * Sanitizes argument string into actual
	 * PHP type/object as new Argument instance
	 */
	public static function factory(string $argument): static
	{
		$argument = trim($argument);

		// remove grouping parantheses
		if (
			Str::startsWith($argument, '(') &&
			Str::endsWith($argument, ')')
		) {
			$argument = trim(substr($argument, 1, -1));
		}

		// string with single quotes
		if (
			Str::startsWith($argument, "'") &&
			Str::endsWith($argument, "'")
		) {
			$string = substr($argument, 1, -1);
			$string = str_replace("\'", "'", $string);
			return new static($string);
		}

		// string with double quotes
		if (
			Str::startsWith($argument, '"') &&
			Str::endsWith($argument, '"')
		) {
			$string = substr($argument, 1, -1);
			$string = str_replace('\"', '"', $string);
			return new static($string);
		}

		// array: split and recursive sanitizing
		if (
			Str::startsWith($argument, '[') &&
			Str::endsWith($argument, ']')
		) {
			$array = substr($argument, 1, -1);
			$array = Arguments::factory($array);
			return new static($array);
		}

		// numeric
		if (is_numeric($argument) === true) {
			if (str_contains($argument, '.') === false) {
				return new static((int)$argument);
			}

			return new static((float)$argument);
		}

		// Closure
		if (Str::startsWith($argument, '() =>')) {
			$query = Str::after($argument, '() =>');
			$query = trim($query);
			return new static(fn () => $query);
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
		// don't resolve the Closure immediately, instead
		// resolve it to the sub-query and create a new Closure
		// that resolves the sub-query with the same data set once called
		if ($this->value instanceof Closure) {
			$query = ($this->value)();
			return fn () => static::factory($query)->resolve($data);
		}

		if (is_object($this->value) === true) {
			return $this->value->resolve($data);
		}

		return $this->value;
	}
}
