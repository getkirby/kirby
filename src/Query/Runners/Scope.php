<?php

namespace Kirby\Query\Runners;

use Closure;
use Exception;

/**
 * Helper class to execute logic during runtime
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 * @unstable
 */
class Scope
{
	/**
	 * Access the key on the object/array during runtime
	 */
	public static function access(
		array|object|null $object,
		string|int $key,
		bool $nullSafe = false,
		...$arguments
	): mixed {
		if ($object === null && $nullSafe === true) {
			return null;
		}

		if (is_array($object) === true) {
			if ($item = $object[$key] ?? $object[(string)$key] ?? null) {
				if ($arguments) {
					return $item(...$arguments);
				}

				if ($item instanceof Closure) {
					return $item();
				}
			}

			return $item;
		}

		if (is_object($object) === true) {
			$key = (string)$key;

			if (
				method_exists($object, $key) === true ||
				method_exists($object, '__call') === true
			) {
				return $object->$key(...$arguments);
			}

			return $object->$key ?? null;
		}

		throw new Exception("Cannot access \"$key\" on " . gettype($object));
	}

	/**
	 * Resolves a mapping from global context or functions during runtime
	 */
	public static function get(
		string $name,
		array $context = [],
		array $global = [],
		false|null $fallback = null
	): mixed {
		// What looks like a variable might actually be a global function
		// but if there is a variable with the same name,
		// the variable takes precedence
		if (isset($context[$name]) === true) {
			if ($context[$name] instanceof Closure) {
				return $context[$name]();
			}

			return $context[$name];
		}

		if (isset($global[$name]) === true) {
			return $global[$name]();
		}

		// Alias to access the global context
		if ($name === 'this') {
			return $context;
		}

		return $fallback;
	}
}
