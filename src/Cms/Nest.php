<?php

namespace Kirby\Cms;

use Kirby\Util\Obj;

class Nest
{
    public static function create(array $array)
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value) === true) {
                $result[$key] = static::create($value);
            } elseif (is_string($value) === true) {
                $result[$key] = new ContentField($key, $value);
            }
        }

        if (is_int(key($array))) {
            return new NestCollection($result);
        } else {
            return new NestObject($result);
        }
    }
}
