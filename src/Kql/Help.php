<?php

namespace Kirby\Kql;

use ReflectionMethod;

/**
 * Help information for different KQL data types
 *
 * @package   Kirby Kql
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Help
{
	public static function for($object): array
	{
		if (is_array($object) === true) {
			return static::forArray($object);
		}

		if (is_object($object) === true) {
			return static::forObject($object);
		}

		return [
			'type'  => gettype($object),
			'value' => $object
		];
	}

	public static function forArray(array $array): array
	{
		return [
			'type'  => 'array',
			'keys'  => array_keys($array),
		];
	}

	public static function forMethod($object, $method): array
	{
		$reflection = new ReflectionMethod($object, $method);
		$returns    = null;
		$params     = [];

		if ($returnType = $reflection->getReturnType()) {
			$returns = $returnType->getName();
		}

		foreach ($reflection->getParameters() as $param) {
			$p = [
				'name'     => $param->getName(),
				'required' => $param->isOptional() === false,
				'type'     => $param->hasType() ? $param->getType()->getName() : null,
			];

			if ($param->isDefaultValueAvailable()) {
				$p['default'] = $param->getDefaultValue();
			}

			$call = null;

			if ($p['type'] !== null) {
				$call = $p['type'] . ' ';
			}

			$call .= '$' . $p['name'];

			if ($p['required'] === false && isset($p['default']) === true) {
				$call .= ' = ' . var_export($p['default'], true);
			}

			$p['call'] = $call;

			$params[$p['name']] = $p;
		}

		$call = '.' . $method;

		if (empty($params) === false) {
			$call .= '(' . implode(', ', array_column($params, 'call')) . ')';
		}

		return [
			'call'    => $call,
			'name'    => $method,
			'params'  => $params,
			'returns' => $returns
		];
	}

	public static function forMethods($object, $methods): array
	{
		$methods    = array_unique($methods);
		$reflection = [];

		sort($methods);

		foreach ($methods as $methodName) {
			if (method_exists($object, $methodName) === false) {
				continue;
			}

			$reflection[$methodName] = static::forMethod($object, $methodName);
		}

		return $reflection;
	}

	public static function forObject($object): array
	{
		$original = $object;
		$object   = Interceptor::replace($original);

		return $object->__debugInfo();
	}
}
