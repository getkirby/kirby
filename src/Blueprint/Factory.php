<?php

namespace Kirby\Blueprint;

use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * Factory
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * // TODO: include in test coverage once blueprint refactoring is done
 * @codeCoverageIgnore
 */
class Factory
{
	/**
	 * Resolves the properties by
	 * applying a map of factories (propName => class)
	 */
	public static function apply(array $properties, array $factories): array
	{
		foreach ($factories as $property => $class) {
			// skip non-existing properties, empty properties
			// or properties that are matching objects
			if (
				isset($properties[$property]) === false ||
				$properties[$property] === null ||
				is_a($properties[$property], $class) === true
			) {
				continue;
			}

			$properties[$property] = $class::factory($properties[$property]);
		}

		return $properties;
	}

	public static function forNamedType(ReflectionNamedType|null $type, $value)
	{
		// get the class name for the single type
		$className = $type->getName();

		// check if there's a factory for the value
		if (method_exists($className, 'factory') === true) {
			return $className::factory($value);
		}

		// try to assign the value directly and trust
		// in PHP's type system.
		return $value;
	}

	public static function forProperties(string $class, array $properties): array
	{
		foreach ($properties as $property => $value) {
			try {
				$properties[$property] = static::forProperty($class, $property, $value);
			} catch (ReflectionException) {
				// the property does not exist
				unset($properties[$property]);
			}
		}

		return $properties;
	}

	public static function forProperty(string $class, string $property, $value)
	{
		if (is_null($value) === true) {
			return $value;
		}

		// instantly assign objects
		// PHP's type system will find issues automatically
		if (is_object($value) === true) {
			return $value;
		}

		// get the type for the property
		$reflection = new ReflectionProperty($class, $property);
		$propType   = $reflection->getType();

		// no type given
		if ($propType === null) {
			return $value;
		}

		// union types
		if ($propType instanceof ReflectionUnionType) {
			return static::forUnionType($propType, $value);
		}

		return static::forNamedType($propType, $value);
	}

	/**
	 * For properties with union types,
	 * the first named type is used to create
	 * the factory or pass a built-in value
	 */
	public static function forUnionType(ReflectionUnionType $type, $value)
	{
		return static::forNamedType($type->getTypes()[0], $value);
	}

	public static function make(string $class, array $properties): object
	{
		return new $class(...static::forProperties($class, $properties));
	}
}
