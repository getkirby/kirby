<?php

namespace Kirby\Toolkit\Query;

use Exception;

class Runtime
{
	public static function access(array|object|null $object, string|int $key, bool $nullSafe = false, ...$arguments): mixed
	{
		if($nullSafe && $object === null) {
			return null;
		}

		if(is_array($object)) {
			$item = ($object[$key] ?? $object[(string)$key] ?? null);

			if($item) {
				if($arguments) {
					return $item(...$arguments);
				}
				if($item instanceof \Closure) {
					return $item();
				}
			}

			return $item;
		}
		if(is_object($object)) {
			if(is_int($key)) {
				$key = (string)$key;
			}
			if(method_exists($object, $key) || method_exists($object, '__call')) {
				return $object->$key(...$arguments);
			}
			return $object->$key ?? null;
		}
		throw new Exception("Cannot access \"$key\" on " . gettype($object));

	}
}
