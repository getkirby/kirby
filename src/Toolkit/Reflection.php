<?php

namespace Kirby\Toolkit;

use ReflectionMethod;
use ReflectionParameter;

class Reflection
{
	protected static array $params = [];

	/**
	 * Returns all values in the $props array that are not
	 * defined as constructor arguments.
	 */
	public static function extractAttrs(array $props, string $class): array
	{
		$propsNames = static::paramsNames($class);
		$attrs      = [];

		foreach ($props as $key => $value) {
			if (in_array($key, $propsNames) === false) {
				$attrs[$key] = $value;
			}
		}

		return $attrs;
	}

	/**
	 * Returns all values in the $props array that are
	 * defined as constructor arguments.
	 */
	public static function extractProps(array $props, string $class): array
	{
		$propsNames = static::paramsNames($class);

		foreach ($props as $key => $value) {
			if (in_array($key, $propsNames) === false) {
				unset($props[$key]);
			}
		}

		return $props;
	}

	/**
	 * Returns all constructor arguments of a class
	 */
	public static function params(string $class): array
	{
		if (isset(static::$params[$class]) === false) {
			$constructor = new ReflectionMethod($class, '__construct');
			$params      = $constructor->getParameters();

			static::$params[$class] = $params;
		}

		return static::$params[$class];
	}

	/**
	 * Returns all constructor argument names of a class.
	 */
	public static function paramsNames(string $class): array
	{
		return array_values(array_map(fn (ReflectionParameter $param) => $param->name, static::params($class)));
	}
}
