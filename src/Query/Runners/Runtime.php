<?php

namespace Kirby\Query\Runners;

use Closure;
use Exception;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Runtime
{
	public static function access(
		array|object|null $object,
		string|int $key,
		bool $nullSafe = false,
		...$arguments
	): mixed {
		if ($nullSafe === true && $object === null) {
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
			if (is_int($key) === true) {
				$key = (string)$key;
			}

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
}
