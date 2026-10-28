<?php

namespace Kirby\Cms;

use Kirby\Content\Field;

/**
 * The Nest class converts any array type
 * into a Kirby style collection/object. This
 * can be used to make any type of array compatible
 * with Kirby queries.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @todo REFACTOR: move this to the toolkit
 */
class Nest
{
	/**
	 * Converts scalars to fields and arrays to collections
	 * (numeric keys) or objects (string keys), recursively
	 *
	 * @param array|scalar $data
	 */
	public static function create(
		$data,
		object|null $parent = null
	): NestCollection|NestObject|Field {
		if (is_scalar($data) === true) {
			return new Field($parent, $data, $data);
		}

		$result = [];

		foreach ($data as $key => $value) {
			if (is_array($value) === true) {
				$result[$key] = static::create($value, $parent);
			} elseif (is_scalar($value) === true) {
				$result[$key] = new Field($parent, $key, $value);
			}
		}

		$key = key($data);

		if ($key === null || is_int($key) === true) {
			return new NestCollection($result);
		}

		return new NestObject($result);
	}
}
