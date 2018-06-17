<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Obj;

/**
 * The Nest class converts any array type
 * into a Kirby style collection/object. This
 * can be used make any type of array compatible
 * with Kirby queries.
 *
 * TODO: move this to the toolkit
 */
class Nest
{
    public static function create($data, $parent = null)
    {
        if (is_scalar($data) === true) {
            return new Field($parent, $data, $data);
        }

        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value) === true) {
                $result[$key] = static::create($value, $parent);
            } elseif (is_string($value) === true) {
                $result[$key] = new Field($parent, $key, $value);
            }
        }

        if (is_int(key($data))) {
            return new NestCollection($result);
        } else {
            return new NestObject($result);
        }
    }
}
