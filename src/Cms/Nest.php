<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Obj;

class Nest
{
    public static function create($data, $parent = null)
    {
        if (is_scalar($data) === true) {
            return new ContentField($data, $data);
        }

        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value) === true) {
                $result[$key] = static::create($value, $parent);
            } elseif (is_string($value) === true) {
                $result[$key] = new ContentField($key, $value, $parent);
            }
        }

        if (is_int(key($data))) {
            return new NestCollection($result);
        } else {
            return new NestObject($result);
        }
    }
}
