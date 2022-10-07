<?php

namespace Kirby\Kql;

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\PermissionException;
use Kirby\Kql\Interceptors\Interceptor as BaseInterceptor;

/**
 * Handles the interception of invalid calls
 * after each query segment
 *
 * @package   Kirby Kql
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Interceptor
{
	public static function replace($object)
	{
		if (is_object($object) === false) {
			throw new Exception('Unsupported value: ' . gettype($object));
		}

		$kirby     = App::instance();
		$className = get_class($object);
		$fullName  = strtolower($className);
		$blocked   = array_map('strtolower', $kirby->option('kql.classes.blocked', []));

		// check in the block list from the config
		if (in_array($fullName, $blocked) === true) {
			throw new PermissionException('Access to the class "' . $className . '" is blocked');
		}

		// directly return interceptor objects
		if ($object instanceof BaseInterceptor) {
			return $object;
		}

		// check for an interceptor class
		$interceptors = array_change_key_case($kirby->option('kql.interceptors', []), CASE_LOWER);
		// load an interceptor from config if it exists and otherwise fall back to a built-in interceptor
		$interceptor = $interceptors[$fullName] ?? str_replace('Kirby\\', 'Kirby\\Kql\\Interceptors\\', $className);

		// check for a valid interceptor class
		if (
			$className !== $interceptor &&
			class_exists($interceptor) === true
		) {
			return new $interceptor($object);
		}

		// go through parents of the current object to use their interceptors as fallback
		foreach (class_parents($object) as $parent) {
			$interceptor = str_replace('Kirby\\', 'Kirby\\Kql\\Interceptors\\', $parent);

			if (class_exists($interceptor) === true) {
				return new $interceptor($object);
			}
		}

		// check for a class in the allow list
		$allowed = array_map('strtolower', $kirby->option('kql.classes.allowed', []));

		// return the plain object if it is allowed
		if (in_array($fullName, $allowed) === true) {
			return $object;
		}

		throw new PermissionException('Access to the class "' . $className . '" is not supported');
	}
}
