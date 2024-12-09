<?php

namespace Kirby\Toolkit\Query;

use Closure;
use Exception;

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

		if (is_array($object)) {
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

		if (is_object($object)) {
			if (is_int($key)) {
				$key = (string)$key;
			}

			if (
				method_exists($object, $key) ||
				method_exists($object, '__call')
			) {
				return $object->$key(...$arguments);
			}

			return $object->$key ?? null;
		}

		throw new Exception("Cannot access \"$key\" on " . gettype($object));
	}
}
